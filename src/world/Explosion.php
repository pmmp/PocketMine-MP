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

class Explosion{
	private $i = 16; //Rays
	public $level;
	public $source;
	public $size;
	public $affectedBlocks = array();
	
	public function __construct($level, Vector3 $center, $size){
		$this->level = $level;
		$this->source = $center;
		$this->size = max($size, 0);
	}
	
	public function explode(){
		if($this->size < 0.1 or ServerAPI::request()->api->dhandle("entity.explosion", array(
			"level" => $level,
			"source" => $this->source,
			"size" => $this->size
		))){
			return false;
		}
		
		for($i = 0; $i < $this->i; ++$i){
			for($j = 0; $j < $this->i; ++$j){
				for($k = 0; $k < $this->i; ++$k){
					if($i === 0 or $i === ($this->i - 1) or $j === 0 or $j === ($this->i - 1) or $k === 0 or $k === ($this->i - 1)){
						$d3 = $i / ($this->i - 1) * 2 - 1;
						$d4 = $j / ($this->i - 1) * 2 - 1;
						$d5 = $k / ($this->i - 1) * 2 - 1;
						$d6 = sqrt($d3 * $d3 + $d4 * $d4 + $d5 * $d5);
						$d3 /= $d6;
						$d4 /= $d6;
						$d5 /= $d6;
						
						$f1 = $this->size * (0.7 + lcg_value() * 0.6);
						$d0 = $this->source->x;
						$d1 = $this->source->y;
						$d2 = $this->source->z;
						
						for($f2 = 0.3; $f1 > 0; $f1 -= $f2 * 0.75){
							$l = floor($d0);
							$i1 = floor($d1);
							$j1 = floor($d2);
							$k1 = $this->level->getBlock(new Vector3($l, $i1, $j1))->getID();
							if($k1 > AIR){
								$f3 = 0.5; //Placeholder
								$f1 -= ($f3 + 0.4) * $f2;
							}
							
							if($f1 > 0 and $i1 < 128 and $i1 >= 0){
								$this->level->setBlock(new Vector3($l, $i1, $j1), AIR, 0, false);
								$this->affectedBlocks[$l.$i1.$j1] = new Vector3($l, $i1, $j1);
							}
							
							$d0 += $d3 * $f2;
							$d1 += $d4 * $f2;
							$d2 += $d5 * $f2;
						}
					}
				}
			}
		}

		foreach(ServerAPI::request()->api->player->getAll() as $player){
			$player->dataPacket(MC_EXPLOSION, array(
				"x" => $this->source->x,
				"y" => $this->source->y,
				"z" => $this->source->z,
				"radius" => $this->size,
				"records" => array(), //Blocks are already sent
			));
		}
	}
}