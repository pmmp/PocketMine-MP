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

use pocketmine\command\overload\Parameter;
use pocketmine\command\overload\ParameterParseException;
use pocketmine\utils\AssumptionFailedError;
use function get_class;
use function is_string;
use function preg_last_error_msg;
use function preg_match;
use function preg_match_all;
use function preg_replace;
use function strlen;
use function substr_compare;

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
					$match = $matches[$i][$k];
					$args[] = preg_replace('/\\\\([\\\\"])/u', '$1', $match) ?? throw new AssumptionFailedError(preg_last_error_msg());
					break;
				}
			}
		}

		return $args;
	}

	public static function parseQuoteAwareSingle(string $commandLine, int &$offset = 0) : ?string{
		//quoted or bare string, like the old CommandStringHelper
		if(preg_match('/\G(?:"((?:\\\\.|[^\\\\"])*)"|(\S+))/u', $commandLine, $matches, offset: $offset) > 0){
			$offset += strlen($matches[0]);
			for($i = 1; $i <= 2; ++$i){
				if($matches[$i] !== ""){
					$match = $matches[$i];
					return preg_replace('/\\\\([\\\\"])/u', '$1', $match) ?? throw new AssumptionFailedError(preg_last_error_msg());
				}
			}
		}

		return null;
	}

	public static function skipWhitespace(string $commandLine, int &$offset) : int{
		if(preg_match('/\G\s+/', $commandLine, $matches, offset: $offset) > 0){
			$offset += strlen($matches[0]);
			return strlen($matches[0]);
		}
		return 0;
	}

	/**
	 * @phpstan-param list<Parameter<*>|string> $parameters
	 * @phpstan-return list<mixed>
	 */
	public static function parseArguments(array $parameters, string $commandLine, int &$offset) : array{
		$args = [];

		//skip preceding whitespace
		CommandStringHelper::skipWhitespace($commandLine, $offset);

		if($offset < strlen($commandLine)){
			foreach($parameters as $parameter){
				if(is_string($parameter)){
					if(substr_compare($commandLine, $parameter, $offset, strlen($parameter)) === 0){
						$offset += strlen($parameter);
					}else{
						throw new ParameterParseException("Literal \"$parameter\" expected");
					}
				}else{
					try{
						$args[] = $parameter->parse($commandLine, $offset);
					}catch(ParameterParseException $e){
						throw new ParameterParseException(
							"Failed parsing argument \$" . $parameter->getCodeName() . ": " . $e->getMessage(),
							previous: $e
						);
					}
				}
				if(CommandStringHelper::skipWhitespace($commandLine, $offset) === 0){
					if($offset === strlen($commandLine)){
						//no more tokens, rest of the parameters must be optional
						break;
					}elseif(is_string($parameter)){
						throw new ParameterParseException("Incorrect literal provided (should have been \"$parameter\" followed by whitespace)");
					}else{
						throw new ParameterParseException("Parameter " . get_class($parameter) . " for \$" . $parameter->getCodeName() . " didn't stop on a whitespace character");
					}
				}
			}
		}

		return $args;
	}
}
