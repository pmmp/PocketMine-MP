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
			switch(strtolower($args[0])){
				case "reload":
					if($this->testPermission($sender, DefaultPermissionNames::COMMAND_WHITELIST_RELOAD)){
						$sender->getServer()->getWhitelisted()->reload();
						Command::broadcastCommandMessage($sender, new TranslationContainer(KnownTranslationKeys::COMMANDS_WHITELIST_RELOADED));
					}

					return true;
				case "on":
					if($this->testPermission($sender, DefaultPermissionNames::COMMAND_WHITELIST_ENABLE)){
						$sender->getServer()->getConfigGroup()->setConfigBool("white-list", true);
						Command::broadcastCommandMessage($sender, new TranslationContainer(KnownTranslationKeys::COMMANDS_WHITELIST_ENABLED));
					}

					return true;
				case "off":
					if($this->testPermission($sender, DefaultPermissionNames::COMMAND_WHITELIST_DISABLE)){
						$sender->getServer()->getConfigGroup()->setConfigBool("white-list", false);
						Command::broadcastCommandMessage($sender, new TranslationContainer(KnownTranslationKeys::COMMANDS_WHITELIST_DISABLED));
					}

					return true;
				case "list":
					if($this->testPermission($sender, DefaultPermissionNames::COMMAND_WHITELIST_LIST)){
						$entries = $sender->getServer()->getWhitelisted()->getAll(true);
						sort($entries, SORT_STRING);
						$result = implode(", ", $entries);
						$count = count($entries);

						$sender->sendMessage(new TranslationContainer(KnownTranslationKeys::COMMANDS_WHITELIST_LIST, [$count, $count]));
						$sender->sendMessage($result);
					}

					return true;

				case "add":
					$sender->sendMessage(new TranslationContainer(KnownTranslationKeys::COMMANDS_GENERIC_USAGE, ["%" . KnownTranslationKeys::COMMANDS_WHITELIST_ADD_USAGE]));
					return true;

				case "remove":
					$sender->sendMessage(new TranslationContainer(KnownTranslationKeys::COMMANDS_GENERIC_USAGE, ["%" . KnownTranslationKeys::COMMANDS_WHITELIST_REMOVE_USAGE]));
					return true;
			}
		}elseif(count($args) === 2){
			if(!Player::isValidUserName($args[1])){
				throw new InvalidCommandSyntaxException();
			}
			switch(strtolower($args[0])){
				case "add":
					if($this->testPermission($sender, DefaultPermissionNames::COMMAND_WHITELIST_ADD)){
						$sender->getServer()->addWhitelist($args[1]);
						Command::broadcastCommandMessage($sender, new TranslationContainer(KnownTranslationKeys::COMMANDS_WHITELIST_ADD_SUCCESS, [$args[1]]));
					}

					return true;
				case "remove":
					if($this->testPermission($sender, DefaultPermissionNames::COMMAND_WHITELIST_REMOVE)){
						$sender->getServer()->removeWhitelist($args[1]);
						Command::broadcastCommandMessage($sender, new TranslationContainer(KnownTranslationKeys::COMMANDS_WHITELIST_REMOVE_SUCCESS, [$args[1]]));
					}

					return true;
			}
		}

		throw new InvalidCommandSyntaxException();
	}
}
