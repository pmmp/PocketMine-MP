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

/***REM_START***/
require_once("LevelGenerator.php");
/***REM_END***/

class NormalGenerator implements LevelGenerator{

	private $populators = array();
	private $level;
	private $random;
	private $worldHeight = 65;
	private $waterHeight = 62;
	private $noiseGenBase;
	private $noiseGen1;
	private $noiseGen2;
	private $noiseGen3;
	private $noiseGen4;
	private $noiseGen5;
	private $noiseGen6;
	
	public function __construct(array $options = array()){
		
	}
	
	public function getSettings(){
		return array();
	}
	
	public function init(Level $level, Random $random){
		$this->level = $level;
		$this->random = $random;
		$this->random->setSeed($this->level->getSeed());	
		$this->noiseGenBase = new NoiseGeneratorSimplex($this->random, 4);
		$this->noiseGen1 = new NoiseGeneratorSimplex($this->random, 8);
		//$this->noiseGen2 = new NoiseGeneratorSimplex($this->random, 8);

		$ores = new OrePopulator();
		$ores->setOreTypes(array(
			new OreType(new CoalOreBlock(), 20, 16, 0, 128),
			new OreType(New IronOreBlock(), 20, 8, 0, 64),
			new OreType(new RedstoneOreBlock(), 8, 7, 0, 16),
			new OreType(new LapisOreBlock(), 1, 6, 0, 32),
			new OreType(new GoldOreBlock(), 2, 8, 0, 32),
			new OreType(new DiamondOreBlock(), 1, 7, 0, 16),
			new OreType(new DirtBlock(), 20, 32, 0, 128),
			new OreType(new GravelBlock(), 10, 16, 0, 128),
		));
		$this->populators[] = $ores;
	}
	
	public function generateChunk($chunkX, $chunkZ){
		$this->random->setSeed(0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->level->getSeed());

		for($chunkY = 0; $chunkY < 8; ++$chunkY){
			$chunk = "";
			$startY = $chunkY << 4;
			$endY = $startY + 16;			
			for($z = 0; $z < 16; ++$z){
				for($x = 0; $x < 16; ++$x){
					$noiseBase = $this->noiseGenBase->noise2D($x + ($chunkX << 4), $z + ($chunkZ << 4), 1/5, 16, true);
					$noise1 = $this->noiseGen1->noise2D($x + ($chunkX << 4), $z + ($chunkZ << 4), 0.7, 25, true);
					//$noise2 = $this->noiseGen2->noise2D($x + ($chunkX << 4), $z + ($chunkZ << 4), 0.8, 1);
					//$height = $this->worldHeight + $noiseBase + $noise1 /*+ $noise2*/;
					$height = $this->worldHeight + $noiseBase;//$height = (int) ($height + ($height * 0.15 * $noiseBase));
					$height = (int) $height;
					for($y = $startY; $y < $endY; ++$y){
						$diff = $height - $y;	
						if($y <= 4 and ($y === 0 or $this->random->nextFloat() < 0.75)){
							$chunk .= "\x07"; //bedrock
						}elseif($diff > 3){
							$chunk .= "\x01"; //stone
						}elseif($diff > 0){
							$chunk .= "\x03"; //dirt
						}elseif($y <= $this->waterHeight){
							if($y === $this->waterHeight and $diff === 0){
								$chunk .= "\x0c"; //sand
							}else{
								$chunk .= "\x09"; //still_water
							}
						}elseif($diff === 0){
							$chunk .= $noise1 > 0 ? "\x02":"\x01"; //grass
						}else{
							$chunk .= "\x00";
						}
					}
					$chunk .= "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00";
				}
			}
			$this->level->setMiniChunk($chunkX, $chunkZ, $chunkY, $chunk);
		}
		
	}
	
	public function populateChunk($chunkX, $chunkZ){		
		foreach($this->populators as $populator){
			$this->random->setSeed(0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->level->getSeed());
			$populator->populate($this->level, $chunkX, $chunkZ, $this->random);
		}
	}
	
	public function populateLevel(){
	
	}
	
	public function getSpawn(){
		return $this->level->getSafeSpawn(new Vector3(127.5, 128, 127.5));
	}

}