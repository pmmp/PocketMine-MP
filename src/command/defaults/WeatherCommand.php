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
use pocketmine\utils\AssumptionFailedError;
use function count;
use function max;
use function strtolower;

class WeatherCommand extends VanillaCommand{

	public function __construct(){
		parent::__construct(
			"weather",
			KnownTranslationFactory::pocketmine_command_weather_description(),
			KnownTranslationFactory::pocketmine_command_weather_usage(),
		);
		$this->setPermission(DefaultPermissionNames::COMMAND_WEATHER);
	}

	public function execute(CommandSender $sender, string $label, array $args) : void{
		if($sender instanceof Player){
			$world = $sender->getWorld();
		}else{
			$world = $sender->getServer()->getWorldManager()->getDefaultWorld();
		}

		if($world === null){
			throw new AssumptionFailedError("Failed to retrieve world instance. Default world is not loaded.");
		}

		if(!$world->isWeatherEnabled()){
			$sender->sendMessage(KnownTranslationFactory::pocketmine_command_weather_disable());
			return;
		}

		if(count($args) < 1){
			$rainLevel = $world->getRainLevel();
			$thunderLevel = $world->getThunderLevel();

			if($rainLevel > 0.0 && $thunderLevel > 0.0){
				$current = "thunder";
			}elseif($rainLevel > 0.0){
				$current = "rain";
			}else{
				$current = "clear";
			}

			$stateName = match($current){
				"clear" => KnownTranslationFactory::commands_weather_query_clear(),
				"rain" => KnownTranslationFactory::commands_weather_query_rain(),
				"thunder" => KnownTranslationFactory::commands_weather_query_thunder(),
			};

			$sender->sendMessage(KnownTranslationFactory::commands_weather_query($stateName));
			return;
		}

		$type = match(strtolower($args[0])){
			"clear" => "clear",
			"rain" => "rain",
			"thunder" => "thunder",
			default => throw new InvalidCommandSyntaxException(),
		};

		$rainLevel = 0.0;
		$thunderLevel = 0.0;
		$duration = max(100, (int) ($args[1] ?? 6000));

		switch($type){
			case "rain":
				$rainLevel = 1.0;
				$thunderLevel = 0.0;
				break;

			case "thunder":
				$rainLevel = 1.0;
				$thunderLevel = 1.0;
				break;
		}
		$world->setWeather($rainLevel, $thunderLevel, $duration);

		Command::broadcastCommandMessage($sender,
			match($type){
				"clear" => KnownTranslationFactory::commands_weather_clear(),
				"rain" => KnownTranslationFactory::commands_weather_rain(),
				"thunder" => KnownTranslationFactory::commands_weather_thunder(),
			}
		);
	}
}
