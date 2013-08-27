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
	public function __construct(LevelGenerator $generator, $name, $seed = false, $width = 16, $height = 8){
		$this->seed = $seed !== false ? (int) $seed:Utils::readInt(Utils::getRandomBytes(4, false));
		$this->random = new Random($this->seed);
		$this->width = (int) $width;
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
			"extra" => "",
			"width" => $this->width,
			"height" => $this->height
		));
		$entities = new Config($this->path."entities.yml", CONFIG_YAML);
		$tiles = new Config($this->path."tiles.yml", CONFIG_YAML);
		$blockUpdates = new Config($this->path."bupdates.yml", CONFIG_YAML);
		$this->level = new Level($level, $entities, $tiles, $blockUpdates, $name);
	}
	
	public function generate(){
		$this->generator->init($this->level, $this->random);
		for($Z = 0; $Z < $this->width; ++$Z){
			for($X = 0; $X < $this->width; ++$X){
				$this->generator->generateChunk($X, $Z);
			}
			console("[NOTICE] Generating level ".ceil((($Z + 1)/$this->width) * 100)."%");
		}
		console("[NOTICE] Populating level");
		$this->generator->populateLevel();
		for($Z = 0; $Z < $this->width; ++$Z){
			for($X = 0; $X < $this->width; ++$X){
				$this->generator->populateChunk($X, $Z);
			}
			console("[NOTICE] Populating level ".ceil((($Z + 1)/$this->width) * 100)."%");
		}
		
		$this->level->setSpawn($this->generator->getSpawn());
		$this->level->save(true, true);
	}
	
	public function close(){
		$this->level->close();
	}

}