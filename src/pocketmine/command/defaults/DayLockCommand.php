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
use pocketmine\lang\TranslationContainer;
use pocketmine\level\Level;
use pocketmine\Player;

class DayLockCommand extends VanillaCommand{

	public function __construct(string $name){
		parent::__construct(
			$name,
			"%pocketmine.command.daylock.description",
			"%commands.daylock.usage",
      ["alwaysday"]
		);
		$this->setPermission("pocketmine.command.daylock");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

    if(count($args) > 1){
      throw new InvalidCommandSyntaxException();
    }

    if(count($args) < 1 or $args[0] === "true"){
      foreach($sender->getServer()->getLevels() as $level){
        $level->checkTime();
        $level->stopTime();
        $level->checkTime();
      }
      $sender->sendMessage(new TranslationContainer("commands.always.day.locked"));
      Command::broadcastCommandMessage($sender, "Stopped the time.");
    }elseif($args[0] === "false"){
      foreach($sender->getServer()->getLevels() as $level){
        $level->checkTime();
        $level->startTime();
        $level->checkTime();
      }
      $sender->sendMessage(new TranslationContainer("commands.always.day.unlocked"));
      Command::broadcastCommandMessage($sender, "Started the time.");
    }else{
      throw new InvalidCommandSyntaxException();
    }

		return true;
	}
}
