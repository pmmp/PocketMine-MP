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

use function hash;
use function strlen;
use function strrpos;
use function substr;

final class ConsoleReaderChildProcessUtils{
	public const TOKEN_DELIMITER = ":";
	public const TOKEN_HASH_ALGO = "xxh3";

	private function __construct(){

	}

	/**
	 * Creates an IPC message to transmit a user's input command to the parent process.
	 *
	 * Unfortunately we can't currently provide IPC pipes other than stdout/stderr to subprocesses on Windows, so this
	 * adds a hash of the user input (with a counter as salt) to prevent unintended process output (like error messages)
	 * from being treated as user input.
	 */
	public static function createMessage(string $line, int &$counter) : string{
		$token = hash(self::TOKEN_HASH_ALGO, $line, options: ['seed' => $counter]);
		$counter++;
		return $line . self::TOKEN_DELIMITER . $token;
	}

	/**
	 * Extracts a command from an IPC message from the console reader subprocess.
	 * Returns the user's input command, or null if this isn't a user input.
	 */
	public static function parseMessage(string $message, int &$counter) : ?string{
		$delimiterPos = strrpos($message, self::TOKEN_DELIMITER);
		if($delimiterPos !== false){
			$left = substr($message, 0, $delimiterPos);
			$right = substr($message, $delimiterPos + strlen(self::TOKEN_DELIMITER));
			$expectedToken = hash(self::TOKEN_HASH_ALGO, $left, options: ['seed' => $counter]);

			if($expectedToken === $right){
				$counter++;
				return $left;
			}
		}

		return null;
	}
}
