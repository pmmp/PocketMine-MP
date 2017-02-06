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

namespace pocketmine\level;

use pocketmine\math\Vector3;
use pocketmine\network\protocol\LevelEventPacket;
use pocketmine\level\Level;
use pocketmine\level\format\io\LevelProvider;

class WeatherManager{

	/** @var Level */
	public $level;
	/** @var LevelProvider */
	public $provider;

	public $weatherEnabled;
	public $tick;
	public $nextUpdate;

	const NORMAL = 0;
	const RAIN = 1;
	const THUNDER = 3;

	/**
	 * Starts to manage level weather.
	 *
	 * @param Level $level
	 * @param LevelProvider $provider
	 */
	public function __construct(Level $level, LevelProvider $provider){
		$this->level = $level;
		$this->provider = $provider;
	}

	public function tick(){
		if(!$this->$weatherEnabled){
			return;
		}
	}

	public function setWeatherEnabled(Bool $value){
		$this->weatherEnabled = $value;
	}

	public function toggleWeather(){

	}

	public function sendWeatherTo($players = null){
		$players = $players !== null && is_array($players) ? $players : $this->getSevrer()->getOnlinePlayers();
		foreach($players as $p){
			//TODO send weather to players
		}
	}

	public function strikeLighting(Vector3 $pos){

	}

	public function getServer(){
		return $this->server;
	}

	public function getLevel(){
		return $this->level;
	}
}