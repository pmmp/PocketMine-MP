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

use pocketmine\command\utils\CommandStringHelper;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\TextFormat;
use function array_shift;
use function count;
use function implode;
use function preg_match;
use function strlen;
use function strpos;
use function substr;

class FormattedCommandAlias extends Command{
	/**
	 * - matches a $
	 * - captures an optional second $ to indicate required/optional
	 * - captures a series of digits which don't start with a 0
	 * - captures an optional - to indicate variadic
	 */
	private const FORMAT_STRING_REGEX = '/\G\$(\$)?((?!0)+\d+)(-)?/';

	/**
	 * @param string[] $formatStrings
	 */
	public function __construct(
		string $alias,
		private array $formatStrings
	){
		parent::__construct($alias, KnownTranslationFactory::pocketmine_command_userDefined_description());
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		$commands = [];
		$result = true;

		foreach($this->formatStrings as $formatString){
			try{
				$formatArgs = CommandStringHelper::parseQuoteAware($formatString);
				$unresolved = [];
				$processedArgs = [];
				foreach($formatArgs as $formatArg){
					$processedArg = $this->buildCommand($formatArg, $args);
					if($processedArg === null){
						$unresolved[] = $formatArg;
					}elseif(count($unresolved) !== 0){
						//unresolved args are OK only if they are at the end of the string - we can't have holes in the args list
						throw new \InvalidArgumentException("Unable to resolve format arguments (" . implode(", ", $unresolved) . ") in command string \"$formatString\" due to missing arguments");
					}else{
						$processedArgs[] = $processedArg;
					}
				}
				$commands[] = $processedArgs;
			}catch(\InvalidArgumentException $e){
				$sender->sendMessage(TextFormat::RED . $e->getMessage());
				return false;
			}
		}

		$commandMap = $sender->getServer()->getCommandMap();
		foreach($commands as $commandArgs){
			//this approximately duplicates the logic found in SimpleCommandMap::dispatch()
			//this is to allow directly invoking the commands without having to rebuild a command string and parse it
			//again for no reason
			//TODO: a method on CommandMap to invoke a command with pre-parsed arguments would probably be a good idea
			//for a future major version
			$commandLabel = array_shift($commandArgs);
			if($commandLabel === null){
				throw new AssumptionFailedError("This should have been checked before construction");
			}

			if(($target = $commandMap->getCommand($commandLabel)) !== null){
				$target->timings->startTiming();

				try{
					$target->execute($sender, $commandLabel, $commandArgs);
				}catch(InvalidCommandSyntaxException $e){
					$sender->sendMessage($sender->getLanguage()->translate(KnownTranslationFactory::commands_generic_usage($target->getUsage())));
				}finally{
					$target->timings->stopTiming();
				}
			}else{
				$sender->sendMessage($sender->getLanguage()->translate(KnownTranslationFactory::pocketmine_command_notFound($commandLabel, "/help")->prefix(TextFormat::RED)));

				//to match the behaviour of SimpleCommandMap::dispatch()
				//this shouldn't normally happen, but might happen if the command was unregistered or modified after
				//the alias was installed
				$result = false;
			}
		}

		return $result;
	}

	/**
	 * @param string[] $args
	 */
	private function buildCommand(string $formatString, array $args) : ?string{
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
