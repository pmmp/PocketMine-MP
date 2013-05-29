<?php

/*

           -
         /   \
      /         \
   /   PocketMine  \
/          MP         \
|\     @shoghicp     /|
|.   \           /   .|
| ..     \   /     .. |
|    ..    |    ..    |
|       .. | ..       |
\          |          /
   \       |       /
      \    |    /
         \ | /

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Lesser General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.


*/

/***REM_START***/
require_once("noise/NoiseGeneratorOctaves.php");
/***REM_END***/

class NormalGenerator implements LevelGenerator{
	private $level, $options, $random, $chunks, $noise1, $chunkNoise;
	
	public function __construct(array $options = array()){
		$this->options = $options;
		if(isset($this->options["elevation"])){
			$this->options["elevation"] = intval($this->options["elevation"]);
		}else{
			$this->options["elevation"] = 62;
		}
		$this->chunks = array();
	}
	
	public function init(Level $level, Random $random){
		$this->level = $level;
		$this->random = $random;
		$this->noise1 = new NoiseGeneratorOctaves($this->random, 16);
		$this->chunkNoise = array();
	}
		
	public function generateChunk($chunkX, $chunkY, $chunkZ){
		$ix = $chunkX.".".$chunkZ;
		if(!isset($this->chunkNoise[$ix])){
			$this->chunkNoise[$ix] = $this->noise1->generateNoiseOctaves();
		}
		$this->random->setSeed((int) ($chunkX * 0xdead + $chunkZ * 0xbeef));
		$startY = $chunkY << 4;
		$endY = $startY + 16;
		for($z = 0; $z < 16; ++$z){
			for($x = 0; $x < 16; ++$x){
				$blocks = "";
				$metas = "";
				for($y = $startY; $y < $endY; ++$y){
					
				}
			}
		}
		$this->level->setMiniChunk($chunkX, $chunkZ, $chunkY, $this->chunks[$chunkY]);
	}
	
	public function populateChunk($chunkX, $chunkY, $chunkZ){

	}
	
	public function populateLevel(){

	}
	
	public function getSpawn(){
		return new Vector3(128, 128, 128);
	}
}