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

class WeatherCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"%pocketmine.command.weather.description",
			"%commands.weather.usage"
		);
		$this->setPermission("pocketmine.command.weather");
	}

	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) < 1){
			$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));

			return true;
		}

		$level = $sender instanceof Player ? $sender->getLevel() : $sender->getServer()->getDefaultLevel();

		$duration = !isset($args[1]) ? mt_rand((Level::TIME_FULL / 2), (7 * Level::TIME_FULL) + (Level::TIME_FULL / 2)) : (int)$args[1] * 20;

		if($duration < 0) {
			$level->lockWeather();
		}
		switch ($args[0]){
			case "clear":
				$level->setWeather(Level::WEATHER_CLEAR);
				$level->setRainTime($duration);
				$level->setThunderTime($duration * 3);
				Command::broadcastCommandMessage($sender, "Changing to clear weather", true);
				return true;
				break;
			case "rain":
				$level->setWeather(Level::WEATHER_RAIN);
				$level->setClearTime($duration);
				Command::broadcastCommandMessage($sender, "Changing to rainy weather", true);
				return true;
				break;
			case "thunder":
				$level->setWeather(Level::WEATHER_RAIN_THUNDER);
				$level->setClearTime($duration);
				Command::broadcastCommandMessage($sender, "Changing to rain and thunder", true);
				return true;
				break;
			default:
				$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));
				break;
		}
		$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));
		return true;
	}
}