<?php

declare(strict_types=1);

namespace pocketmine\event\weather;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\Event;
use pocketmine\world\WeatherType;
use pocketmine\world\World;

class WeatherChangeEvent extends Event implements Cancellable{
	use CancellableTrait;

	public function __construct(
		private World $world,
		private WeatherType $oldWeather,
		private WeatherType $newWeather
	){}

	public function getWorld() : World{
		return $this->world;
	}

	public function getOldWeather() : WeatherType{
		return $this->oldWeather;
	}

	public function getNewWeather() : WeatherType{
		return $this->newWeather;
	}
}