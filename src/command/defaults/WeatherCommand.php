<?php

declare(strict_types=1);

namespace pocketmine\command\defaults;

use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\player\Player;
use pocketmine\world\WeatherType;
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
			throw new \RuntimeException("Failed to retrieve world instance. Default world is not loaded.");
		}

		if(count($args) < 1){
			$current = $world->getWeather();

		$stateName = match($current){
			WeatherType::CLEAR => KnownTranslationFactory::commands_weather_query_clear(),
			WeatherType::RAIN => KnownTranslationFactory::commands_weather_query_rain(),
			WeatherType::THUNDER => KnownTranslationFactory::commands_weather_query_thunder(),
		};

			$sender->sendMessage(KnownTranslationFactory::commands_weather_query($stateName));
			return;
		}

		$type = match(strtolower($args[0])){
			"clear" => WeatherType::CLEAR,
			"rain" => WeatherType::RAIN,
			"thunder" => WeatherType::THUNDER,
			default => throw new InvalidCommandSyntaxException(),
		};

		$duration = isset($args[1]) ? max(100, (int) $args[1]) : 6000;
		$world->setWeather($type, $duration);
		$sender->sendMessage(match($type){
			WeatherType::CLEAR => KnownTranslationFactory::commands_weather_clear(),
			WeatherType::RAIN => KnownTranslationFactory::commands_weather_rain(),
			WeatherType::THUNDER => KnownTranslationFactory::commands_weather_thunder(),
		});
	}
}