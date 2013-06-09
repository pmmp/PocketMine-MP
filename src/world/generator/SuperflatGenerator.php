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
require_once("LevelGenerator.php");
/***REM_END***/

class SuperflatGenerator implements LevelGenerator{
	private $level, $random, $structure, $chunks, $options, $floorLevel;
	
	public function __construct(array $options = array()){
		$this->preset = "2;7,59x1,3x3,2;1;spawn(radius=10 block=89),decoration(treecount=80 grasscount=120)";
		$this->options = $options;
		if(isset($options["preset"])){
			$this->parsePreset($options["preset"]);
		}else{
			$this->parsePreset($this->preset);
		}
	}
	
	public function parsePreset($preset){
		$this->preset = $preset;
		$preset = explode(";", $preset);
		$version = (int) $preset[0];
		$blocks = @$preset[1];
		$biome = isset($preset[2]) ? $preset[2]:1;
		$options = isset($preset[3]) ? $preset[3]:"";
		preg_match_all('#(([0-9]{0,})x?([0-9]{1,3}:?[0-9]{0,2})),?#', $blocks, $matches);
		$y = 0;
		$this->structure = array();
		$this->chunks = array();
		foreach($matches[3] as $i => $b){
			$b = BlockAPI::fromString($b);
			$cnt = $matches[2][$i] === "" ? 1:intval($matches[2][$i]);
			for($cY = $y, $y += $cnt; $cY < $y; ++$cY){
				$this->structure[$cY] = $b;
			}
		}
		
		$this->floorLevel = $y;
		
		for(;$y < 0xFF; ++$y){
			$this->structure[$y] = new AirBlock();
		}
		
		
		for($Y = 0; $Y < 8; ++$Y){
			$this->chunks[$Y] = "";
			$startY = $Y << 4;
			$endY = $startY + 16;
			for($Z = 0; $Z < 16; ++$Z){
				for($X = 0; $X < 16; ++$X){
					$blocks = "";
					$metas = "";
					for($y = $startY; $y < $endY; ++$y){
						$blocks .= chr($this->structure[$y]->getID());
						$metas .= substr(dechex($this->structure[$y]->getMetadata()), -1);
					}
					$this->chunks[$Y] .= $blocks.Utils::hexToStr($metas)."\x00\x00\x00\x00\x00\x00\x00\x00";
				}
			}
		}
		
		preg_match_all('#(([0-9a-z_]{1,})\(?([0-9a-z_ =:]{0,})\)?),?#', $options, $matches);
		foreach($matches[2] as $i => $option){
			$params = true;
			if($matches[3][$i] !== ""){
				$params = array();
				$p = explode(" ", $matches[3][$i]);
				foreach($p as $k){
					$k = explode("=", $k);
					if(isset($k[1])){
						$params[$k[0]] = $k[1];
					}
				}
			}
			$this->options[$option] = $params;
		}
	}
	
	public function init(Level $level, Random $random){
		$this->level = $level;
		$this->random = $random;
	}
		
	public function generateChunk($chunkX, $chunkY, $chunkZ){
		$this->level->setMiniChunk($chunkX, $chunkZ, $chunkY, $this->chunks[$chunkY]);
	}
	
	public function populateChunk($chunkX, $chunkY, $chunkZ){
		$this->random->setSeed((int) ($chunkX * 0xdead + $chunkZ * 0xbeef));
		if(isset($this->options["decoration"])){
			//Ore spawning algorithm
			$ores = array(
				array(new CoalOreBlock(),  1, 70, 143),
				array(new IronOreBlock(),  1, 64, 77),
				array(new LapisOreBlock(),  1, 31, 3),
				array(new GoldOreBlock(),  1, 32, 8),
				array(new DiamondOreBlock(),  1, 14, 3),
				array(new RedstoneOreBlock(),  1, 14, 25),
			);
			$minX = $chunkX << 4;
			$maxX = $minX + 16;
			$minZ = $chunkZ << 4;
			$maxZ = $minZ + 16;
			$y = $chunkY << 4;
			
			foreach($ores as $data){
				$minY = max($y, $data[1]);
				$maxY = min($y + 16, min($this->floorLevel - 1, $data[2]));
				if($minY > ($y + 16) or $maxY < $y){
					continue;
				}
				$nRange = $data[2] - $data[1] + 1;
				$factor = ($maxY - $minY + 1) / $nRange;
				$count = (int) ($this->random->nextRange($data[3] - 1, $data[3] + 1) * $factor);
				for($c = 0; $c < $count; ++$c){
					$block = $this->level->getBlock(new Vector3(
						$this->random->nextRange($minX, $maxX),
						$this->random->nextRange($minY, $maxY),
						$this->random->nextRange($minZ, $maxZ)
					));
					if($block->getID() === STONE){
						$this->level->setBlockRaw($block, $data[0]);
					}
				}
			}
		}
	}
	
	public function populateLevel(){
		$this->random->setSeed($this->level->getSeed());
		if(isset($this->options["spawn"])){
			$spawn = array(10, new SandstoneBlock());
			if(isset($this->options["spawn"]["radius"])){
				$spawn[0] = intval($this->options["spawn"]["radius"]);
			}
			if(isset($this->options["spawn"]["block"])){
				$spawn[1] = BlockAPI::fromString($this->options["spawn"]["block"])->getBlock();
				if(!($spawn[1] instanceof Block)){
					$spawn[1] = new SandstoneBlock();
				}
			}

			$start = 128 - $spawn[0];
			$end = 128 + $spawn[0];
			for($x = $start; $x <= $end; ++$x){
				for($z = $start; $z <= $end; ++$z){
					if(floor(sqrt(pow($x - 128, 2) + pow($z - 128, 2))) <= $spawn[0]){
						$this->level->setBlockRaw(new Vector3($x, $this->floorLevel - 1, $z), $spawn[1], null);
					}
				}
			}
		}
		
		if(isset($this->options["decoration"])){
			$treecount = 80;
			$grasscount = 120;
			if(isset($this->options["spawn"]["treecount"])){
				$treecount = intval($this->options["spawn"]["treecount"]);
			}
			if(isset($this->options["spawn"]["grasscount"])){
				$grasscount = intval($this->options["spawn"]["grasscount"]);
			}
			for($t = 0; $t < $treecount; ++$t){
				$centerX = $this->random->nextRange(0, 256);
				$centerZ = $this->random->nextRange(0, 256);
				$down = $this->level->getBlock(new Vector3($centerX, $this->floorLevel - 1, $centerZ))->getID();
				if($down === DIRT or $down === GRASS or $down === FARMLAND){
					TreeObject::growTree($this->level, new Vector3($centerX, $this->floorLevel, $centerZ), $this->random, $this->random->nextRange(0,3));
				}
			}
			for($t = 0; $t < $grasscount; ++$t){
				$centerX = $this->random->nextRange(0, 256);
				$centerZ = $this->random->nextRange(0, 256);
				$down = $this->level->getBlock(new Vector3($centerX, $this->floorLevel - 1, $centerZ))->getID();
				if($down === GRASS){
					TallGrassObject::growGrass($this->level, new Vector3($centerX, $this->floorLevel - 1, $centerZ), $this->random, $this->random->nextRange(8, 40));
				}
			}
		}
	}
	
	public function getSpawn(){
		return new Vector3(128, $this->floorLevel, 128);
	}
}