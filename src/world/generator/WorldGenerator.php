<?php

/**
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

class WorldGenerator{
	private $seed, $level, $path, $random, $generator, $width;
	public function __construct(LevelGenerator $generator, $name, $seed = false, $height = 8){
		$this->seed = $seed !== false ? (int) $seed:Utils::readInt(Utils::getRandomBytes(4, false));
		$this->random = new Random($this->seed);
		$this->height = (int) $height;
		$this->path = DATA_PATH."worlds/".$name."/";
		$this->generator = $generator;
		$level = new PMFLevel($this->path."level.pmf", array(
			"name" => $name,
			"seed" => $this->seed,
			"time" => 0,
			"spawnX" => 128,
			"spawnY" => 128,
			"spawnZ" => 128,
			"height" => $this->height,
			"generator" => get_class($this->generator),
			"generatorSettings" => $this->generator->getSettings(),
			"extra" => ""
		));
		$entities = new Config($this->path."entities.yml", CONFIG_YAML);
		$tiles = new Config($this->path."tiles.yml", CONFIG_YAML);
		$blockUpdates = new Config($this->path."bupdates.yml", CONFIG_YAML);
		$this->level = new Level($level, $entities, $tiles, $blockUpdates, $name);
	}
	
	public function generate(){
		$this->generator->init($this->level, $this->random);
		
		//Generate 4 chunks for spawning players
		for($Z = 7; $Z <= 8; ++$Z){
			for($X = 7; $X <= 8; ++$X){
				$this->level->level->loadChunk($X, $Z);
			}
		}
		
		$this->level->setSpawn($this->generator->getSpawn());
	}
	
	public function close(){
		$this->level->close();
	}

}