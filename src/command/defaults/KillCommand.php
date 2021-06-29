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
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\lang\KnownTranslationKeys;
use pocketmine\lang\TranslationContainer;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use function count;
use function implode;

class KillCommand extends VanillaCommand{

	public function __construct(string $name){
		parent::__construct(
			$name,
			"%" . KnownTranslationKeys::POCKETMINE_COMMAND_KILL_DESCRIPTION,
			"%" . KnownTranslationKeys::POCKETMINE_COMMAND_KILL_USAGE,
			["suicide"]
		);
		$this->setPermission(implode(";", [DefaultPermissionNames::COMMAND_KILL_SELF, DefaultPermissionNames::COMMAND_KILL_OTHER]));
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) >= 2){
			throw new InvalidCommandSyntaxException();
		}

		if(count($args) === 1){
			if(!$sender->hasPermission(DefaultPermissionNames::COMMAND_KILL_OTHER)){
				$sender->sendMessage($sender->getLanguage()->translateString(TextFormat::RED . "%" . KnownTranslationKeys::COMMANDS_GENERIC_PERMISSION));

				return true;
			}

			$player = $sender->getServer()->getPlayerByPrefix($args[0]);

			if($player instanceof Player){
				$player->attack(new EntityDamageEvent($player, EntityDamageEvent::CAUSE_SUICIDE, 1000));
				Command::broadcastCommandMessage($sender, new TranslationContainer(KnownTranslationKeys::COMMANDS_KILL_SUCCESSFUL, [$player->getName()]));
			}else{
				$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%" . KnownTranslationKeys::COMMANDS_GENERIC_PLAYER_NOTFOUND));
			}

			return true;
		}

		if($sender instanceof Player){
			if(!$sender->hasPermission(DefaultPermissionNames::COMMAND_KILL_SELF)){
				$sender->sendMessage($sender->getLanguage()->translateString(TextFormat::RED . "%" . KnownTranslationKeys::COMMANDS_GENERIC_PERMISSION));

				return true;
			}

			$sender->attack(new EntityDamageEvent($sender, EntityDamageEvent::CAUSE_SUICIDE, 1000));
			$sender->sendMessage(new TranslationContainer(KnownTranslationKeys::COMMANDS_KILL_SUCCESSFUL, [$sender->getName()]));
		}else{
			throw new InvalidCommandSyntaxException();
		}

		return true;
	}
}
