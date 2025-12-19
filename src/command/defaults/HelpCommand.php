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
use pocketmine\command\overload\IntRangeParameter;
use pocketmine\command\overload\OverloadBuilder;
use pocketmine\command\overload\StringParameter;
use pocketmine\command\SimpleCommandMap;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\lang\Translatable;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Utils;
use function array_chunk;
use function array_key_first;
use function count;
use function explode;
use function implode;
use function is_array;
use function ksort;
use function min;
use function sort;
use function strtolower;
use const PHP_INT_MAX;
use const SORT_FLAG_CASE;
use const SORT_NATURAL;

final class HelpCommand{
	private function __construct(){
		//NOOP
	}

	public static function create(string $namespace, string $name) : Command{
		return new Command(
			$namespace,
			$name,
			OverloadBuilder::make()
				->executor([
					new IntRangeParameter("pageNumber", "page", 1, PHP_INT_MAX)
				], DefaultPermissionNames::COMMAND_HELP, self::commandListPage(...))
				->executor([
					new StringParameter("commandName", "command name")
				], DefaultPermissionNames::COMMAND_HELP, self::commandSpecificInfo(...))
				->build(),
			KnownTranslationFactory::pocketmine_command_help_description(),
		);
	}

	private static function commandListPage(CommandSender $sender, int $pageNumber = 1) : void{
		$pageHeight = $sender->getScreenLineHeight();

		//TODO: maybe inject this in the constructor instead of assuming the server's command map?
		$commandMap = $sender->getServer()->getCommandMap();
		$userAliasMap = $sender->getCommandAliasMap();
		$commands = [];
		foreach($commandMap->getUniqueCommands() as $command){
			if(count($command->getPermittedOverloads($sender)) > 0){
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
	}

	private static function commandSpecificInfo(CommandSender $sender, string $commandName) : void{
		//TODO: maybe inject this in the constructor instead of assuming the server's command map?
		$commandMap = $sender->getServer()->getCommandMap();
		$userAliasMap = $sender->getCommandAliasMap();
		if(($command = $commandMap->getCommand(strtolower($commandName), $userAliasMap)) !== null){
			if(is_array($command)){
				SimpleCommandMap::handleConflicted($sender, $commandName, $command, $commandMap->getAliasMap());
				return;
			}

			$lang = $sender->getLanguage();
			$usages = [];
			foreach($command->getPermittedOverloads($sender) as $overload){
				$usages[] = $overload->getUsage($commandName);
			}

			if(count($usages) > 0){ //only permitted usages are shown
				$description = $command->getDescription();
				$descriptionString = $description instanceof Translatable ? $lang->translate($description) : $description;
				$sender->sendMessage(KnownTranslationFactory::pocketmine_command_help_specificCommand_header($commandName)
					->format(TextFormat::YELLOW . "--------- " . TextFormat::RESET, TextFormat::YELLOW . " ---------"));
				$sender->sendMessage(KnownTranslationFactory::pocketmine_command_help_specificCommand_description(TextFormat::RESET . $descriptionString)
					->prefix(TextFormat::GOLD));

				$aliases = $userAliasMap->getMergedAliases($command->getId(), $commandMap->getAliasMap());
				sort($aliases, SORT_NATURAL);
				$sender->sendMessage(KnownTranslationFactory::pocketmine_command_help_specificCommand_aliases(TextFormat::RESET . implode(", ", $aliases))
					->prefix(TextFormat::GOLD));

				foreach($usages as $usage){
					$usageString = $lang->translate($usage);
					$sender->sendMessage(KnownTranslationFactory::pocketmine_command_help_specificCommand_usage(TextFormat::RESET . implode("\n" . TextFormat::RESET, explode("\n", $usageString, limit: PHP_INT_MAX)))
						->prefix(TextFormat::GOLD));
				}

				return;
			}
		}
		$sender->sendMessage(KnownTranslationFactory::pocketmine_command_notFound($commandName, "/" . $userAliasMap->getPreferredAlias("pocketmine:help", $commandMap->getAliasMap()))->prefix(TextFormat::RED));
	}
}
