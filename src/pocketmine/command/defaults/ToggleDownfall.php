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

namespace pocketmine\command\defaults;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\TranslationContainer;
use pocketmine\level\Level;
use pocketmine\Player;

class ToggleDownfall extends VanillaCommand{

	public function __construct($name, $description = "", $usageMessage = null, array $aliases = []){
		parent::__construct(
			$name,
			"%pocketmine.command.toggledownfall.description",
			"%pocketmine.command.toggledownfall.usage"
		);
		$this->setPermission("pocketmine.command.toggledownfall");
	}

	public function execute(CommandSender $sender, $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) > 0){
			$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));

			return true;
		}

		$level = $sender instanceof Player ? $sender->getLevel() : $sender->getServer()->getDefaultLevel();

		$duration = mt_rand((Level::TIME_FULL / 2), (7 * Level::TIME_FULL) + (Level::TIME_FULL / 2));

		switch($level->getWeather()){
			case Level::WEATHER_RAIN_THUNDER:
			case Level::WEATHER_RAIN:
				$level->setWeather(Level::WEATHER_NORM);
				$level->setRainTime($duration);
				$level->setThunderTime($duration * 3);
				Command::broadcastCommandMessage($sender, "Toggled downfall", true);
				return true;
				break;
			case Level::WEATHER_NORM:
			default:
				if(mt_rand(0, 100) > 94){
					$level->setWeather(Level::WEATHER_RAIN_THUNDER);
				} else {
					$level->setWeather(Level::WEATHER_RAIN);
				}
				$level->setClearTime($duration);
				Command::broadcastCommandMessage($sender, "Toggled downfall", true);
				return true;
				break;
		}
	}
}