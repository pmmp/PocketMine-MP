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
	private $i = 12; //Rays
	public $level;
	public $source;
	public $size;
	public $affectedBlocks = array();
	
	public function __construct(Position $center, $size){
		$this->level = $center->level;
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
						
						$blastForce = $this->size * (mt_rand(700, 1300) / 1000);
						$X = $this->source->x;
						$Y = $this->source->y;
						$Z = $this->source->z;
						
						for($stepLen = 0.3; $blastForce > 0; $blastForce -= $stepLen * 0.75){
							$x = (int) $X;
							$y = (int) $Y;
							$z = (int) $Z;
							$block = $this->level->getBlock(new Vector3($x, $y, $z));
			
							if(!($block instanceof AirBlock) and $y < 128 and $y >= 0){
								$blastForce -= ($block->getHardness() / 5 + 0.3) * $stepLen;
								if($blastForce > 0){
									$this->affectedBlocks[$x.":".$y.":".$z] = $block;
								}
							}
							
							$X += $d3 * $stepLen;
							$Y += $d4 * $stepLen;
							$Z += $d5 * $stepLen;
						}
					}
				}
			}
		}
		
		$send = array();
		$airblock = new AirBlock();
		$source = $this->source->floor();
		
		foreach($server->api->entity->getRadius($this->source, 10) as $entity){
			$impact = (1 - $this->source->distance($entity) / 10);
			$damage = (int) (($impact * $impact + $impact) * 4 * $this->size + 1);
			$entity->harm($damage, "explosion");
		}

		foreach($this->affectedBlocks as $block){
			$this->level->setBlockRaw($block, $airblock, false, false); //Do not send record
			if($block instanceof TNTBlock){
				$data = array(
					"x" => $block->x + 0.5,
					"y" => $block->y + 0.5,
					"z" => $block->z + 0.5,
					"power" => 4,
					"fuse" => mt_rand(10, 30), //0.5 to 3 seconds
				);
				$e = $server->api->entity->add($this->level, ENTITY_OBJECT, OBJECT_PRIMEDTNT, $data);
				$server->api->entity->spawnToAll($e);
			}
			$send[] = new Vector3($block->x - $source->x, $block->y - $source->y, $block->z - $source->z);
			if(mt_rand(0, 100) < 30){
				$server->api->entity->drop(new Position($block->x + 0.5, $block->y, $block->z + 0.5, $this->level), BlockAPI::getItem($block->getID(), $block->getMetadata()));
			}
		}
		$server->api->player->broadcastPacket($server->api->player->getAll($this->level), MC_EXPLOSION, array(
			"x" => $this->source->x,
			"y" => $this->source->y,
			"z" => $this->source->z,
			"radius" => $this->size,
			"records" => $send,
		));

	}
}