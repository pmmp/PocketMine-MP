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

use pocketmine\level\format\pmf\LevelFormat;
use pocketmine\level\generator\Generator;
use pocketmine\Server;
use pocketmine\utils\Binary;
use pocketmine\utils\Random;
use pocketmine\utils\Utils;

class WorldGenerator{
	private $seed, $level, $path, $random, $generator, $server;

	/**
	 * @param Server    $server
	 * @param Generator $generator
	 * @param string    $name
	 * @param int       $seed
	 */
	public function __construct(Server $server, Generator $generator, $name, $seed = null){
		$this->seed = $seed !== null ? (int) $seed : Binary::readInt(Utils::getRandomBytes(4, false));
		$this->random = new Random($this->seed);
		$this->server = $server;
		$this->path = $this->server->getDataPath() . "worlds/" . $name . "/";
		$this->generator = $generator;
		$level = new LevelFormat($this->path . "level.pmf", array(
			"name" => $name,
			"seed" => $this->seed,
			"time" => 0,
			"spawnX" => 128,
			"spawnY" => 128,
			"spawnZ" => 128,
			"height" => 8,
			"generator" => $this->generator->getName(),
			"generatorSettings" => $this->generator->getSettings(),
			"extra" => ""
		));
		$this->level = new Level($this->server, $level, $name);
	}

	public function generate(){
		$this->generator->init($this->level, $this->random);

		for($Z = 7; $Z <= 9; ++$Z){
			for($X = 7; $X <= 9; ++$X){
				$this->level->level->loadChunk($X, $Z);
			}
		}

		$this->level->setSpawn($this->generator->getSpawn());
	}

	public function close(){
		$this->level->close();
	}

}