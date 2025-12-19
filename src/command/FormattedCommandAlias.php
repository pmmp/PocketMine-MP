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

namespace pocketmine\command;

use pocketmine\command\overload\OverloadBuilder;
use pocketmine\command\overload\RawParameter;
use pocketmine\command\utils\CommandStringHelper;
use pocketmine\utils\TextFormat;
use function addcslashes;
use function count;
use function implode;
use function preg_match;
use function str_contains;
use function strlen;
use function strpos;
use function substr;

/**
 * @internal
 * Used to register commands defined in the `aliases` section of pocketmine.yml.
 * See the comments in resources/pocketmine.yml in the `aliases` section for configuration instructions and examples.
 */
final class FormattedCommandAlias{
	/**
	 * - matches a $
	 * - captures an optional second $ to indicate required/optional
	 * - captures a series of digits which don't start with a 0
	 * - captures an optional - to indicate variadic
	 */
	private const FORMAT_STRING_REGEX = '/\G\$(\$)?((?!0)+\d+)(-)?/';

	private function __construct(){
		//NOOP
	}

	/**
	 * @param string[] $formatStrings
	 * @phpstan-param list<string> $formatStrings
	 */
	public static function create(
		string $namespace,
		string $name,
		string $permission,
		array $formatStrings
	) : Command{
		return new Command(
			$namespace,
			$name,
			OverloadBuilder::single(
				[new RawParameter("args", "args")],
				$permission,
				fn(CommandSender $sender, string $args) => self::execute($sender, CommandStringHelper::parseQuoteAware($args), $formatStrings)
			)
		);
	}

	/**
	 * @param string[] $args
	 * @param string[] $formatStrings
	 * @phpstan-param list<string> $args
	 * @phpstan-param list<string> $formatStrings
	 */
	private static function execute(CommandSender $sender, array $args, array $formatStrings) : void{
		$commands = [];

		foreach($formatStrings as $formatString){
			try{
				$formatArgs = CommandStringHelper::parseQuoteAware($formatString);
				$unresolved = [];
				$processedArgs = [];
				foreach($formatArgs as $formatArg){
					$processedArg = self::buildCommand($formatArg, $args);
					if($processedArg === null){
						$unresolved[] = $formatArg;
					}elseif(count($unresolved) !== 0){
						//unresolved args are OK only if they are at the end of the string - we can't have holes in the args list
						throw new \InvalidArgumentException("Unable to resolve format arguments (" . implode(", ", $unresolved) . ") in command string \"$formatString\" due to missing arguments");
					}else{
						$processedArgs[] = $processedArg;
					}
				}
				$commands[] = implode(" ", $processedArgs);
			}catch(\InvalidArgumentException $e){
				$sender->sendMessage(TextFormat::RED . $e->getMessage());
				return;
			}
		}

		$commandMap = $sender->getServer()->getCommandMap();
		foreach($commands as $commandLine){
			$sender->getServer()->getLogger()->debug("Dispatching formatted command: $commandLine");
			$commandMap->dispatch($sender, $commandLine);
			//TODO: maybe we should abort command processing if there was an error???
		}
	}

	/**
	 * @param string[] $args
	 * @phpstan-param list<string> $args
	 */
	private static function buildCommand(string $formatString, array $args) : ?string{
		$index = 0;
		while(($index = strpos($formatString, '$', $index)) !== false){
			$start = $index;
			if($index > 0 && $formatString[$start - 1] === "\\"){
				$formatString = substr($formatString, 0, $start - 1) . substr($formatString, $start);
				//offset is now pointing at the next character because we just deleted the \
				continue;
			}

			$info = self::extractPlaceholderInfo($formatString, $index);
			if($info === null){
				throw new \InvalidArgumentException("Invalid replacement token");
			}
			[$fullPlaceholder, $required, $position, $rest] = $info;
			$position--; //array offsets start at 0, but placeholders start at 1

			if($required && $position >= count($args)){
				throw new \InvalidArgumentException("Missing required argument " . ($position + 1));
			}

			$replacement = self::buildReplacement($args, $position, $rest);
			if($replacement === null){
				return null;
			}

			$end = $index + strlen($fullPlaceholder);
			$formatString = substr($formatString, 0, $start) . $replacement . substr($formatString, $end);

			$index = $start + strlen($replacement);
		}

		//we need to assemble a command string to call the target commands, so this needs to be properly quoted
		if(str_contains($formatString, " ")){
			return '"' . addcslashes($formatString, '"') . '"';
		}

		return $formatString;
	}

	/**
	 * @param string[] $args
	 * @phpstan-param list<string> $args
	 */
	private static function buildReplacement(array $args, int $position, bool $rest) : ?string{
		if($rest && $position < count($args)){
			$replacement = "";
			for($i = $position, $c = count($args); $i < $c; ++$i){
				if($i !== $position){
					$replacement .= " ";
				}

				$replacement .= $args[$i];
			}

			return $replacement;
		}elseif($position < count($args)){
			return $args[$position];
		}

		return null;
	}

	/**
	 * @phpstan-return array{string, bool, int, bool}
	 */
	private static function extractPlaceholderInfo(string $commandString, int $offset) : ?array{
		if(preg_match(self::FORMAT_STRING_REGEX, $commandString, $matches, 0, $offset) !== 1){
			return null;
		}

		$fullPlaceholder = $matches[0];

		$required = ($matches[1] ?? "") !== "";
		$position = (int) $matches[2];
		$variadic = ($matches[3] ?? "") !== "";

		return [$fullPlaceholder, $required, $position, $variadic];
	}
}
