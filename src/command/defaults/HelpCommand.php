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

namespace pocketmine\command\defaults;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\SimpleCommandMap;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\lang\Translatable;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Utils;
use function array_chunk;
use function array_key_first;
use function array_pop;
use function count;
use function explode;
use function implode;
use function is_array;
use function is_numeric;
use function ksort;
use function min;
use function sort;
use function strtolower;
use const PHP_INT_MAX;
use const SORT_FLAG_CASE;
use const SORT_NATURAL;

class HelpCommand extends VanillaCommand{

	public function __construct(string $namespace, string $name){
		parent::__construct(
			$namespace,
			$name,
			KnownTranslationFactory::pocketmine_command_help_description(),
			KnownTranslationFactory::commands_help_usage()
		);
		$this->setPermission(DefaultPermissionNames::COMMAND_HELP);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(count($args) === 0){
			$commandName = "";
			$pageNumber = 1;
		}elseif(is_numeric($args[count($args) - 1])){
			$pageNumber = (int) array_pop($args);
			if($pageNumber <= 0){
				$pageNumber = 1;
			}
			$commandName = implode(" ", $args);
		}else{
			$commandName = implode(" ", $args);
			$pageNumber = 1;
		}

		$pageHeight = $sender->getScreenLineHeight();

		//TODO: maybe inject this in the constructor instead of assuming the server's command map?
		$commandMap = $sender->getServer()->getCommandMap();
		$userAliasMap = $sender->getCommandAliasMap();
		if($commandName === ""){
			$commands = [];
			foreach($commandMap->getUniqueCommands() as $command){
				if($command->testPermissionSilent($sender)){
					$userAliases = $userAliasMap->getMergedAliases($command->getId(), $commandMap->getAliasMap());
					$preferredAlias = $userAliases[array_key_first($userAliases)];
					if(isset($commands[$preferredAlias])){
						throw new AssumptionFailedError("Something weird happened during user/global alias resolving");
					}
					$commands[$preferredAlias] = $command;
				}
			}
			ksort($commands, SORT_NATURAL | SORT_FLAG_CASE);
			$commands = array_chunk($commands, $pageHeight, preserve_keys: true);
			$pageNumber = min(count($commands), $pageNumber);
			if($pageNumber < 1){
				$pageNumber = 1;
			}
			$sender->sendMessage(KnownTranslationFactory::commands_help_header((string) $pageNumber, (string) count($commands)));
			$lang = $sender->getLanguage();
			if(isset($commands[$pageNumber - 1])){
				foreach(Utils::promoteKeys($commands[$pageNumber - 1]) as $preferredAlias => $command){
					$description = $command->getDescription();
					$descriptionString = $description instanceof Translatable ? $lang->translate($description) : $description;
					$sender->sendMessage(TextFormat::DARK_GREEN . "/$preferredAlias: " . TextFormat::RESET . $descriptionString);
				}
			}

			return true;
		}else{
			if(($command = $commandMap->getCommand(strtolower($commandName), $userAliasMap)) !== null){
				if(is_array($command)){
					SimpleCommandMap::handleConflicted($sender, $commandName, $command, $commandMap->getAliasMap());
					return true;
				}
				if($command->testPermissionSilent($sender)){
					$lang = $sender->getLanguage();
					$description = $command->getDescription();
					$descriptionString = $description instanceof Translatable ? $lang->translate($description) : $description;
					$sender->sendMessage(KnownTranslationFactory::pocketmine_command_help_specificCommand_header($commandName)
						->format(TextFormat::YELLOW . "--------- " . TextFormat::RESET, TextFormat::YELLOW . " ---------"));
					$sender->sendMessage(KnownTranslationFactory::pocketmine_command_help_specificCommand_description(TextFormat::RESET . $descriptionString)
						->prefix(TextFormat::GOLD));

					$usage = $command->getUsage() ?? "/$commandName";
					$usageString = $usage instanceof Translatable ? $lang->translate($usage) : $usage;
					$sender->sendMessage(KnownTranslationFactory::pocketmine_command_help_specificCommand_usage(TextFormat::RESET . implode("\n" . TextFormat::RESET, explode("\n", $usageString, limit: PHP_INT_MAX)))
						->prefix(TextFormat::GOLD));

					$aliases = $userAliasMap->getMergedAliases($command->getId(), $commandMap->getAliasMap());
					sort($aliases, SORT_NATURAL);
					$sender->sendMessage(KnownTranslationFactory::pocketmine_command_help_specificCommand_aliases(TextFormat::RESET . implode(", ", $aliases))
						->prefix(TextFormat::GOLD));

					return true;
				}
			}
			$sender->sendMessage(KnownTranslationFactory::pocketmine_command_notFound($commandName, "/" . $userAliasMap->getPreferredAlias("pocketmine:help", $commandMap->getAliasMap()))->prefix(TextFormat::RED));

			return true;
		}
	}
}
