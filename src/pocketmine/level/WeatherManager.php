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
use pocketmine\level\Level;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\network\protocol\LevelEventPacket;
use pocketmine\level\format\io\LevelProvider;
use pocketmine\entity\Entity;
use pocketmine\Server;
use pocketmine\event\level\WeatherChangeEvent;

class WeatherManager{

	/** @var Level */
	public $level;
	/** @var LevelProvider */
	public $provider;
	/** @var Server */
	public $server;

	public $weatherEnabled;
	public $weather = 0;
	public $weatherDuration;

	const NORMAL = 0;
	const RAIN = 1;
	const THUNDER_STORM = 2;

	/**
	 * Starts to manage level weather.
	 *
	 * @param Level $level
	 * @param LevelProvider $provider
	 */
	public function __construct(Level $level, LevelProvider $provider){
		$this->level = $level;
		$this->provider = $provider;
		$this->server = $level->getServer();

		if(!$this->getWeatherFromDisk()){ //Currupt level.dat or very old world?
			$this->setWeather(self::NORMAL);
			
			$this->saveWeatherToDisk();
		}
	}

	public function tick(){

		if(!$this->weatherEnabled){
			return;
		}

		if($this->weatherDuration-- <= 0){
			$this->toggleWeather();
		}

		if($this->getWeather === self::RAIN and mt_rand(0, 3000) === 0){ //No exact wiki chance value.
			$this->setWeather(self::THUNDER_STORM); //Small chance rain storm can worsen into thunder storm.
		}
		
		 //1⁄100,000 chance of an attempted lightning strike during a thunderstorm.
	}

	public function setDuration(Int $value){
		$this->weatherDuration = $value;
	}
	
	public function getDuration(){
		return $this->weatherDuration;
	}

	public function setWeather(Int $weatherId, Int $duration = null){
		if($weatherId === $this->weather){
			return;
		}

		$duration = $duration === null ? mt_rand(300, 6000) : $duration;

		$this->getServer()->getPluginManager()->callEvent($ev = new WeatherChangeEvent($this->getLevel(), $this->weather, $duration));

		if($ev->isCancelled()){
			return;
		}

		$this->weather = $ev->getWeather();
		$this->duration = $ev->getDuration();
		$this->sendWeatherToPlayers();
	}
	
	public function getWeather(){
		return $this->weather;
	}

	public function setWeatherEnabled(bool $value){
		$this->weatherEnabled = $value;
	}

	public function isWeatherEnabled(){
		return $this->weatherEnabled;
	}

	public function toggleWeather(){
		switch($this->weather){
			case self::NORMAL:
				if(mt_rand(0, 100) > 95){ //Can't find exact chance. :/
					$this->setWeather(self::THUNDER_STORM);
					break;
				}
				$this->setWeather(self::RAIN);
				break;
			case self::RAIN:
				$this->setWeather(self::NORMAL);
				break;
		}
	}

	public function sendWeatherToPlayers(array $players = []){
		$players = is_array($players) ? count($players) > 0 : $this->getLevel()->getPlayers();

		$pk = new LevelEventPacket();
		$pk->evid = $this->weather === self::RAIN ? 3001 : 3003;
		$pk->data = 90000; //Not sure if this is default.

		foreach($players as $p){
			$p->dataPacket($pk);
		}
	}

	public function strikeLighting(Vector3 $pos, $yaw, $pitch, array $metadata = []){
		$pk = new AddEntityPacket();
		$pk->type = 93;
		$pk->eid = Entity::$entityCount++;
		$pk->x = $pos->z;
		$pk->y = $pos->y;
		$pk->z = $pos->z;
		$pk->yaw = $yaw;
        $pk->pitch = $pitch;
		$pk->metadata = $metadata;
		foreach($this->getLevel()->getPlayers() as $p){
			$p->dataPacket($pk);
		}
	}

	private function saveWeatherToDisk(bool $force = false){
		if(!$force and !$this->getServer()->getAutoSave()){
			return false;
		}
		//TODO save to level.dat
	}

	private function getWeatherFromDisk(){
		//TODO get weather from level.dat
		return false; //Return false if weather can't be read from disk.
	}

	public function getEvid(Int $id){
		return $id === self::NORMAL ? 3003 : 3001;
	}

	public function getServer(){
		return $this->server;
	}

	public function getLevel(){
		return $this->level;
	}
}
