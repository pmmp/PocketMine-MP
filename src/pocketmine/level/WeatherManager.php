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

class WeatherManager{

	/** @var Level */
	public $level;
	/** @var LevelProvider */
	public $provider;
	/** @var Server */
	public $server;

	public $weatherEnabled;
	public $weather;
	public $weatherDuration;

	const NORMAL = 0;
	const RAIN = 1;
	//TODO: Check #2
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
		$this->server = $level->getServer();

		if(!$this->getWeatherFromDisk()){ //Currupt weather or very old world?
			$this->setWeather(self::NORMAL);
			$this->setDuration(mt_rand(300, 6000)); //30sec - 5min
			
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
	}

	public function setDuration(Int $value){
		$this->weatherDuration = $value;
	}
	
	public function getDuration(){
		return $this->weatherDuration;
	}

	public function setWeather(Int $weatherId){
		if($weatherId === $this->weather){
			return;
		}
		$this->weather = $weatherId;
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
		$this->setDuration(mt_rand(300, 6000));
		switch($this->weather){
			case self::NORMAL:
				$this->setWeather(self::RAIN);
				break;
			case self::RAIN:
				$this->setWeather(self::NORMAL);
				break;
		}
	}

	public function sendWeatherToPlayers($players = null){
		$players = is_array($players) ? $players : $this->getLevel()->getPlayers();

		$pk = new LevelEventPacket();
		$pk->evid = $this->weather === self::RAIN ? 3001 : 3003;
		$pk->data = 90000; //Not sure if this is default.

		foreach($players as $p){
			$p->dataPacket($pk);
		}
	}

	public function strikeLighting(Vector3 $pos, $yaw, $pitch, $metadata = null){
		$pk = new AddEntityPacket();
		$pk->type = 93;
		$pk->eid = Entity::$entityCount++;
		$pk->x = $pos->z;
		$pk->y = $pos->y;
		$pk->z = $pos->z;
		$pk->yaw = $yaw;
        $pk->pitch = $pitch;
		$pk->metadata = is_array($metadata) ? $metadata : []; //Not sure what defualt values should be.
		foreach($this->getLevel()->getPlayers() as $p){
			$p->dataPacket($pk);
		}
	}

	private function saveWeatherToDisk(bool $force = false){
		if(!$force && !$this->getServer()->getAutoSave()){
			return false;
		}
		//TODO save to level.dat
	}

	private function getWeatherFromDisk(){
		//TODO get weather from level.dat
		return false; //Return false if weather can't be read from disk.
	}

	public function getEvid($id){
		return $id === self::NORMAL ? 3003 : 3001;
	}

	public function getServer(){
		return $this->server;
	}

	public function getLevel(){
		return $this->level;
	}
}
