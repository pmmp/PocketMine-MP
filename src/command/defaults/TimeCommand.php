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
use pocketmine\utils\TextFormat;
use pocketmine\world\World;
use function count;
use function implode;

class TimeCommand extends VanillaCommand{

	public function __construct(string $name){
		parent::__construct(
			$name,
			"%" . KnownTranslationKeys::POCKETMINE_COMMAND_TIME_DESCRIPTION,
			"%" . KnownTranslationKeys::POCKETMINE_COMMAND_TIME_USAGE
		);
		$this->setPermission(implode(";", [
			DefaultPermissionNames::COMMAND_TIME_ADD,
			DefaultPermissionNames::COMMAND_TIME_SET,
			DefaultPermissionNames::COMMAND_TIME_START,
			DefaultPermissionNames::COMMAND_TIME_STOP,
			DefaultPermissionNames::COMMAND_TIME_QUERY
		]));
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}
		if(count($args) < 1){
			throw new InvalidCommandSyntaxException();
		}

		if($args[0] === "start"){
			if(!$sender->hasPermission(DefaultPermissionNames::COMMAND_TIME_START)){
				$sender->sendMessage($sender->getLanguage()->translateString(TextFormat::RED . "%" . KnownTranslationKeys::COMMANDS_GENERIC_PERMISSION));

				return true;
			}
			foreach($sender->getServer()->getWorldManager()->getWorlds() as $world){
				$world->startTime();
			}
			Command::broadcastCommandMessage($sender, "Restarted the time");
			return true;
		}elseif($args[0] === "stop"){
			if(!$sender->hasPermission(DefaultPermissionNames::COMMAND_TIME_STOP)){
				$sender->sendMessage($sender->getLanguage()->translateString(TextFormat::RED . "%" . KnownTranslationKeys::COMMANDS_GENERIC_PERMISSION));

				return true;
			}
			foreach($sender->getServer()->getWorldManager()->getWorlds() as $world){
				$world->stopTime();
			}
			Command::broadcastCommandMessage($sender, "Stopped the time");
			return true;
		}elseif($args[0] === "query"){
			if(!$sender->hasPermission(DefaultPermissionNames::COMMAND_TIME_QUERY)){
				$sender->sendMessage($sender->getLanguage()->translateString(TextFormat::RED . "%" . KnownTranslationKeys::COMMANDS_GENERIC_PERMISSION));

				return true;
			}
			if($sender instanceof Player){
				$world = $sender->getWorld();
			}else{
				$world = $sender->getServer()->getWorldManager()->getDefaultWorld();
			}
			$sender->sendMessage($sender->getLanguage()->translateString(KnownTranslationKeys::COMMANDS_TIME_QUERY, [$world->getTime()]));
			return true;
		}

		if(count($args) < 2){
			throw new InvalidCommandSyntaxException();
		}

		if($args[0] === "set"){
			if(!$sender->hasPermission(DefaultPermissionNames::COMMAND_TIME_SET)){
				$sender->sendMessage($sender->getLanguage()->translateString(TextFormat::RED . "%" . KnownTranslationKeys::COMMANDS_GENERIC_PERMISSION));

				return true;
			}

			switch($args[1]){
				case "day":
					$value = World::TIME_DAY;
					break;
				case "noon":
					$value = World::TIME_NOON;
					break;
				case "sunset":
					$value = World::TIME_SUNSET;
					break;
				case "night":
					$value = World::TIME_NIGHT;
					break;
				case "midnight":
					$value = World::TIME_MIDNIGHT;
					break;
				case "sunrise":
					$value = World::TIME_SUNRISE;
					break;
				default:
					$value = $this->getInteger($sender, $args[1], 0);
					break;
			}

			foreach($sender->getServer()->getWorldManager()->getWorlds() as $world){
				$world->setTime($value);
			}
			Command::broadcastCommandMessage($sender, new TranslationContainer(KnownTranslationKeys::COMMANDS_TIME_SET, [$value]));
		}elseif($args[0] === "add"){
			if(!$sender->hasPermission(DefaultPermissionNames::COMMAND_TIME_ADD)){
				$sender->sendMessage($sender->getLanguage()->translateString(TextFormat::RED . "%" . KnownTranslationKeys::COMMANDS_GENERIC_PERMISSION));

				return true;
			}

			$value = $this->getInteger($sender, $args[1], 0);
			foreach($sender->getServer()->getWorldManager()->getWorlds() as $world){
				$world->setTime($world->getTime() + $value);
			}
			Command::broadcastCommandMessage($sender, new TranslationContainer(KnownTranslationKeys::COMMANDS_TIME_ADDED, [$value]));
		}else{
			throw new InvalidCommandSyntaxException();
		}

		return true;
	}
}
