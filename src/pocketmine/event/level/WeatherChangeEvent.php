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
 * @link   http://www.pocketmine.net/
 *
 *
 */

namespace pocketmine\event\level;

use pocketmine\level\Level;
use pocketmine\event\Cancellable;

/**
 * Called when weather is changed for any level.
 */
class WeatherChangeEvent extends LevelEvent implements Cancellable{
	public static $handlerList = null;

	private $oldWeather;
	private $newWeather;

	public function __construct(Level $level, int $oldWeather, int $newWeather){
		parent::__construct($level);
		$this->oldWeather = $oldWeather;
		$this->newWeather = $newWeather;
	}

	/**
	 * Sets the new weather type as an integer
	 *
	 * @param int $weather
	 */
	public function setNewWeather(int $weather){
		$this->newWeather = $weather;
	}

	/**
	 * Gives the new weather type as int
	 *
	 * @return int
	 */
	public function getNewWeather() : int{
		return $this->newWeather;
	}

	/**
	 * Gives the old weather type as int
	 *
	 * @return int
	 */
	public function getOldWeather() : int{
		return $this->oldWeather;
	}
}