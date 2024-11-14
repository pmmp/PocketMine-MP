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

namespace pocketmine\command\utils;

use pocketmine\utils\AssumptionFailedError;
use function preg_last_error_msg;
use function preg_match_all;
use function preg_replace;

final class CommandStringHelper{

	private function __construct(){
		//NOOP
	}

	/**
	 * Parses a command string into its component parts. Parts of the string which are inside unescaped quotes are
	 * considered as one argument.
	 *
	 * Examples:
	 * - `give "steve jobs" apple` -> ['give', 'steve jobs', 'apple']
	 * - `say "This is a \"string containing quotes\""` -> ['say', 'This is a "string containing quotes"']
	 *
	 * @return string[]
	 * @phpstan-return list<string>
	 */
	public static function parseQuoteAware(string $commandLine) : array{
		$args = [];
		preg_match_all('/"((?:\\\\.|[^\\\\"])*)"|(\S+)/u', $commandLine, $matches);
		foreach($matches[0] as $k => $_){
			for($i = 1; $i <= 2; ++$i){
				if($matches[$i][$k] !== ""){
					/** @var string $match */ //phpstan can't understand preg_match and friends by itself :(
					$match = $matches[$i][$k];
					$args[(int) $k] = preg_replace('/\\\\([\\\\"])/u', '$1', $match) ?? throw new AssumptionFailedError(preg_last_error_msg());
					break;
				}
			}
		}

		return $args;
	}
}
