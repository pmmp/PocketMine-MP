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
	private $i = 8; //Rays
	public $level;
	public $source;
	public $size;
	public $affectedBlocks = array();
	
	public function __construct(Level $level, Vector3 $center, $size){
		$this->level = $level;
		$this->source = $center;
		$this->size = max($size, 0);
	}
	
	public function explode(){
		$server = ServerAPI::request();
		if($this->size < 0.1 or $server->api->dhandle("entity.explosion", array(
			"level" => $this->level,
			"source" => $this->source,
			"size" => $this->size
		))){
			return false;
		}
		$airblock = new AirBlock();
		$drops = array();
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
						
						$f1 = $this->size * (0.7 + (mt_rand(0, 1000000) / 1000000) * 0.6);
						$d0 = $this->source->x;
						$d1 = $this->source->y;
						$d2 = $this->source->z;
						
						for($f2 = 0.3; $f1 > 0; $f1 -= $f2 * 0.75){
							$l = (int) $d0;
							$i1 = (int) $d1;
							$j1 = (int) $d2;
							$k1 = $this->level->getBlock(new Vector3($l, $i1, $j1));
							
							$f1 -= ($k1->getHardness() / 5 + 0.3) * $f2;
							
							if(!($k1 instanceof AirBlock) and $f1 > 0 and $i1 < 128 and $i1 >= 0){
								$this->level->setBlockRaw(new Vector3($l, $i1, $j1), $airblock, false, false); //Do not send record
								$this->affectedBlocks[$l.$i1.$j1] = new Vector3($l - $this->source->x, $i1 - $this->source->y, $j1 - $this->source->z);
								if(mt_rand(0, 100) < 30){
									$drops[] = array(new Position($l, $i1, $j1, $this->level), BlockAPI::getItem($k1->getID(), $k1->getMetadata()));
								}
							}
							
							$d0 += $d3 * $f2;
							$d1 += $d4 * $f2;
							$d2 += $d5 * $f2;
						}
					}
				}
			}
		}
		
		$server->api->player->broadcastPacket($server->api->player->getAll($this->level), MC_EXPLOSION, array(
			"x" => $this->source->x,
			"y" => $this->source->y,
			"z" => $this->source->z,
			"radius" => $this->size,
			"records" => $this->affectedBlocks,
		));
		foreach($server->api->entity->getRadius($this->source, 10) as $entity){
			$impact = (1 - $this->source->distance($entity) / 10);
			$damage = (int) (($impact * $impact + $impact) * 4 * $this->size + 1);
			$entity->harm($damage, "explosion");
		}
		foreach($drops as $drop){
			$server->api->entity->drop($drop[0], $drop[1]);
		}
	}
}