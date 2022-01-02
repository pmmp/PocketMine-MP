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
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\player\Player;
use pocketmine\world\World;
use function count;
use function implode;

class TimeCommand extends VanillaCommand{

	public function __construct(string $name){
		parent::__construct(
			$name,
			KnownTranslationFactory::pocketmine_command_time_description(),
			KnownTranslationFactory::pocketmine_command_time_usage()
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

		$world = $sender instanceof Player ? $sender->getWorld() : $sender->getServer()->getWorldManager()->getDefaultWorld();

		if($args[0] === "start"){
			if(!$this->testPermission($sender, DefaultPermissionNames::COMMAND_TIME_START)){
				return true;
			}
			$world->startTime();
			Command::broadcastCommandMessage($sender, "Restarted the time");
			return true;
		}elseif($args[0] === "stop"){
			if(!$this->testPermission($sender, DefaultPermissionNames::COMMAND_TIME_STOP)){
				return true;
			}
			$world->stopTime();
			Command::broadcastCommandMessage($sender, "Stopped the time");
			return true;
		}elseif($args[0] === "query"){
			if(!$this->testPermission($sender, DefaultPermissionNames::COMMAND_TIME_QUERY)){
				return true;
			}
			$sender->sendMessage($sender->getLanguage()->translate(KnownTranslationFactory::commands_time_query((string) $world->getTime())));
			return true;
		}

		if(count($args) < 2){
			throw new InvalidCommandSyntaxException();
		}

		if($args[0] === "set"){
			if(!$this->testPermission($sender, DefaultPermissionNames::COMMAND_TIME_SET)){
				return true;
			}

			$value = match($args[1]){
				"day" => World::TIME_DAY,
				"noon" => World::TIME_NOON,
				"sunset" => World::TIME_SUNSET,
				"night" => World::TIME_NIGHT,
				"midnight" => World::TIME_MIDNIGHT,
				"sunrise" => World::TIME_SUNRISE,
				default => $this->getInteger($sender, $args[1], 0),
			};

			$world->setTime($value);
			Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_time_set((string) $value));
		}elseif($args[0] === "add"){
			if(!$this->testPermission($sender, DefaultPermissionNames::COMMAND_TIME_ADD)){
				return true;
			}

			$value = $this->getInteger($sender, $args[1], 0);
			$world->setTime($world->getTime() + $value);
			Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_time_added((string) $value));
		}else{
			throw new InvalidCommandSyntaxException();
		}

		return true;
	}
}