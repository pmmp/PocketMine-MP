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

use pocketmine\utils\Process;
use function cli_set_process_title;
use function count;
use function dirname;
use function feof;
use function fwrite;
use function stream_socket_client;
use const PTHREADS_INHERIT_NONE;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

if(count($argv) !== 2){
	die("Please provide a server to connect to");
}

@cli_set_process_title('PocketMine-MP Console Reader');
$errCode = null;
$errMessage = null;
$socket = stream_socket_client($argv[1], $errCode, $errMessage, 15.0);
if($socket === false){
	throw new \RuntimeException("Failed to connect to server process ($errCode): $errMessage");
}

$channel = new \Threaded();
$thread = new class($channel) extends \Thread{
	public function __construct(
		private \Threaded $channel,
	){}

	public function run(){
		require dirname(__DIR__, 2) . '/vendor/autoload.php';

		$channel = $this->channel;
		$reader = new ConsoleReader();
		while(true){ // @phpstan-ignore-line
			$line = $reader->readLine();
			if($line !== null){
				$channel->synchronized(function() use ($channel, $line) : void{
					$channel[] = $line;
					$channel->notify();
				});
			}
		}
	}
};

$thread->start(PTHREADS_INHERIT_NONE);
while(!feof($socket)){
	$line = $channel->synchronized(function() use ($channel) : ?string{
		if(count($channel) === 0){
			$channel->wait(1_000_000);
		}
		/** @var string|null $line */
		$line = $channel->shift();
		return $line;
	});
	if(@fwrite($socket, ($line ?? "") . "\n") === false){
		//Always send even if there's no line, to check if the parent is alive
		//If the parent process was terminated forcibly, it won't close the connection properly, so feof() will return
		//false even though the connection is actually broken. However, fwrite() will fail.
		break;
	}
}

//For simplicity's sake, we don't bother with a graceful shutdown here.
//The parent process would normally forcibly terminate the child process anyway, so we only reach this point if the
//parent process was terminated forcibly and didn't clean up after itself.
Process::kill(Process::pid(), false);
