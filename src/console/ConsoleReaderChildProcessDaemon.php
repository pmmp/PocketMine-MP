<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
 */

declare(strict_types=1);

namespace pocketmine\console;

use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Utils;
use Symfony\Component\Filesystem\Path;
use function base64_encode;
use function count;
use function explode;
use function fgets;
use function fopen;
use function preg_replace;
use function proc_close;
use function proc_open;
use function proc_terminate;
use function random_bytes;
use function rtrim;
use function sprintf;
use function stream_select;
use function trim;
use const PHP_BINARY;

/**
 * This pile of shit exists because PHP on Windows is broken, and can't handle stream_select() on stdin or pipes
 * properly - stdin native triggers stream_select() when a key is pressed, causing it to get stuck in fgets()
 * waiting for a line that might never come (and Windows doesn't support character-based reading either), and
 * pipes just constantly trigger stream_select() instead of only when data is returned, rendering it useless.
 *
 * This results in whichever process reads stdin getting stuck on shutdown, which previously forced us to kill
 * the entire server process to make it go away.
 *
 * To get around this problem, we delegate the responsibility of reading stdin to a subprocess, which we can
 * then brutally murder when the server shuts down, without killing the entire server process.
 * Thankfully, stream_select() actually works properly on sockets, so we can use them for inter-process
 * communication.
 */
final class ConsoleReaderChildProcessDaemon{
	private \PrefixedLogger $logger;
	/** @var resource */
	private $subprocess;
	/** @var resource */
	private $socket;
	private string $commandPrefix;

	public function __construct(
		\Logger $logger
	){
		$this->logger = new \PrefixedLogger($logger, "Console Reader Daemon");
		$this->prepareSubprocess();
	}

	private function prepareSubprocess() : void{
		//Windows sucks, and likes to corrupt UTF-8 file paths when they travel to the subprocess, so we base64 encode
		//the path to avoid the problem. This is an abysmally shitty hack, but here we are :(
		$this->commandPrefix = rtrim(base64_encode(random_bytes(8)), '=');
		$sub = Utils::assumeNotFalse(proc_open(
			[
				PHP_BINARY,
				'-dopcache.enable_cli=0',
				'-r',
				sprintf('require base64_decode("%s", true);', base64_encode(Path::join(__DIR__, 'ConsoleReaderChildProcess.php'))),
				$this->commandPrefix
			],
			[
				1 => ['socket'],
				2 => fopen("php://stderr", "w"),
			],
			$pipes
		), "Something has gone horribly wrong");

		$this->subprocess = $sub;
		$this->socket = $pipes[1];
	}

	private function shutdownSubprocess() : void{
		//we have no way to signal to the subprocess to shut down gracefully; besides, Windows sucks, and the subprocess
		//gets stuck in a blocking fgets() read because stream_select() is a hunk of junk (hence the separate process in
		//the first place).
		proc_terminate($this->subprocess);
		proc_close($this->subprocess);
	}

	public function readLine() : ?string{
		$r = [$this->socket];
		$w = null;
		$e = null;
		if(stream_select($r, $w, $e, 0, 0) === 1){
			$line = fgets($this->socket);
			if($line === false){
				$this->logger->debug("Lost connection to subprocess, restarting (maybe the child process was killed from outside?)");
				$this->shutdownSubprocess();
				$this->prepareSubprocess();
				return null;
			}

			$parts = explode(":", $line, 2);
			if(count($parts) !== 2 || $parts[0] !== $this->commandPrefix){
				//this is not a command - it may be some kind of error output from the subprocess
				//write it directly to the console
				echo $line;
				return null;
			}
			$command = $parts[1];

			$command = preg_replace("#\\x1b\\x5b([^\\x1b]*\\x7e|[\\x40-\\x50])#", "", trim($command)) ?? throw new AssumptionFailedError("This regex is assumed to be valid");
			$command = preg_replace('/[[:cntrl:]]/', '', $command) ?? throw new AssumptionFailedError("This regex is assumed to be valid");

			return $command !== "" ? $command : null;
		}

		return null;
	}

	public function quit() : void{
		$this->shutdownSubprocess();
	}
}
