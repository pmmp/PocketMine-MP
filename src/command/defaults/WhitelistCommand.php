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
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\lang\KnownTranslationKeys;
use pocketmine\lang\TranslationContainer;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\TextFormat;
use function count;
use function implode;
use function sort;
use function strtolower;
use const SORT_STRING;

class WhitelistCommand extends VanillaCommand{

	public function __construct(string $name){
		parent::__construct(
			$name,
			"%" . KnownTranslationKeys::POCKETMINE_COMMAND_WHITELIST_DESCRIPTION,
			"%" . KnownTranslationKeys::COMMANDS_WHITELIST_USAGE
		);
		$this->setPermission(implode(";", [
			DefaultPermissionNames::COMMAND_WHITELIST_RELOAD,
			DefaultPermissionNames::COMMAND_WHITELIST_ENABLE,
			DefaultPermissionNames::COMMAND_WHITELIST_DISABLE,
			DefaultPermissionNames::COMMAND_WHITELIST_LIST,
			DefaultPermissionNames::COMMAND_WHITELIST_ADD,
			DefaultPermissionNames::COMMAND_WHITELIST_REMOVE
		]));
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) === 1){
			if($this->badPerm($sender, strtolower($args[0]))){
				return false;
			}
			switch(strtolower($args[0])){
				case "reload":
					$sender->getServer()->getWhitelisted()->reload();
					Command::broadcastCommandMessage($sender, new TranslationContainer(KnownTranslationKeys::COMMANDS_WHITELIST_RELOADED));

					return true;
				case "on":
					$sender->getServer()->getConfigGroup()->setConfigBool("white-list", true);
					Command::broadcastCommandMessage($sender, new TranslationContainer(KnownTranslationKeys::COMMANDS_WHITELIST_ENABLED));

					return true;
				case "off":
					$sender->getServer()->getConfigGroup()->setConfigBool("white-list", false);
					Command::broadcastCommandMessage($sender, new TranslationContainer(KnownTranslationKeys::COMMANDS_WHITELIST_DISABLED));

					return true;
				case "list":
					$entries = $sender->getServer()->getWhitelisted()->getAll(true);
					sort($entries, SORT_STRING);
					$result = implode(", ", $entries);
					$count = count($entries);

					$sender->sendMessage(new TranslationContainer(KnownTranslationKeys::COMMANDS_WHITELIST_LIST, [$count, $count]));
					$sender->sendMessage($result);

					return true;

				case "add":
					$sender->sendMessage(new TranslationContainer(KnownTranslationKeys::COMMANDS_GENERIC_USAGE, ["%" . KnownTranslationKeys::COMMANDS_WHITELIST_ADD_USAGE]));
					return true;

				case "remove":
					$sender->sendMessage(new TranslationContainer(KnownTranslationKeys::COMMANDS_GENERIC_USAGE, ["%" . KnownTranslationKeys::COMMANDS_WHITELIST_REMOVE_USAGE]));
					return true;
			}
		}elseif(count($args) === 2){
			if($this->badPerm($sender, strtolower($args[0]))){
				return false;
			}
			if(!Player::isValidUserName($args[1])){
				throw new InvalidCommandSyntaxException();
			}
			switch(strtolower($args[0])){
				case "add":
					$sender->getServer()->addWhitelist($args[1]);
					Command::broadcastCommandMessage($sender, new TranslationContainer(KnownTranslationKeys::COMMANDS_WHITELIST_ADD_SUCCESS, [$args[1]]));

					return true;
				case "remove":
					$sender->getServer()->removeWhitelist($args[1]);
					Command::broadcastCommandMessage($sender, new TranslationContainer(KnownTranslationKeys::COMMANDS_WHITELIST_REMOVE_SUCCESS, [$args[1]]));

					return true;
			}
		}

		throw new InvalidCommandSyntaxException();
	}

	private function badPerm(CommandSender $sender, string $subcommand) : bool{
		$permission = [
			"reload" => DefaultPermissionNames::COMMAND_WHITELIST_RELOAD,
			"on" => DefaultPermissionNames::COMMAND_WHITELIST_ENABLE,
			"off" => DefaultPermissionNames::COMMAND_WHITELIST_DISABLE,
			"list" => DefaultPermissionNames::COMMAND_WHITELIST_LIST,
			"add" => DefaultPermissionNames::COMMAND_WHITELIST_ADD,
			"remove" => DefaultPermissionNames::COMMAND_WHITELIST_REMOVE
		][$subcommand] ?? null;
		if($permission === null){
			throw new AssumptionFailedError("Unknown subcommand $subcommand");
		}
		if(!$sender->hasPermission($permission)){
			$sender->sendMessage($sender->getLanguage()->translateString(TextFormat::RED . "%" . KnownTranslationKeys::COMMANDS_GENERIC_PERMISSION));

			return true;
		}

		return false;
	}
}
