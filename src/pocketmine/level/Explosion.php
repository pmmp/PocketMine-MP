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

use pocketmine\block\Block;
use pocketmine\block\TNT;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\item\Item;
use pocketmine\math\Vector3 as Vector3;
use pocketmine\network\protocol\ExplodePacket;
use pocketmine\Server;

class Explosion{
	public static $specialDrops = array(
		Item::GRASS => Item::DIRT,
		Item::STONE => Item::COBBLESTONE,
		Item::COAL_ORE => Item::COAL,
		Item::DIAMOND_ORE => Item::DIAMOND,
		Item::REDSTONE_ORE => Item::REDSTONE,
	);
	private $rays = 16; //Rays
	public $level;
	public $source;
	public $size;
	/**
	 * @var Block[]
	 */
	public $affectedBlocks = [];
	public $stepLen = 0.3;
	private $what;

	public function __construct(Position $center, $size, $what = null){
		$this->level = $center->getLevel();
		$this->source = $center;
		$this->size = max($size, 0);
		$this->what = $what;
	}

	public function explode(){
		if($this->size < 0.1){
			return false;
		}

		$mRays = $this->rays - 1;
		for($i = 0; $i < $this->rays; ++$i){
			for($j = 0; $j < $this->rays; ++$j){
				for($k = 0; $k < $this->rays; ++$k){
					if($i == 0 or $i == $mRays or $j == 0 or $j == $mRays or $k == 0 or $k == $mRays){
						$vector = new Vector3($i / $mRays * 2 - 1, $j / $mRays * 2 - 1, $k / $mRays * 2 - 1); //($i / $mRays) * 2 - 1
						$vector = $vector->normalize()->multiply($this->stepLen);
						$pointer = clone $this->source;

						for($blastForce = $this->size * (mt_rand(700, 1300) / 1000); $blastForce > 0; $blastForce -= $this->stepLen * 0.75){
							$vBlock = $pointer->floor();
							$blockID = $this->level->getBlockIdAt($vBlock->x, $vBlock->y, $vBlock->z);

							if($blockID > 0){
								$block = Block::get($blockID, 0);
								$block->x = $vBlock->x;
								$block->y = $vBlock->y;
								$block->z = $vBlock->z;
								$blastForce -= ($block->getHardness() / 5 + 0.3) * $this->stepLen;
								if($blastForce > 0){
									$index = ($block->x << 15) + ($block->z << 7) + $block->y;
									if(!isset($this->affectedBlocks[$index])){
										$this->affectedBlocks[$index] = $block;
									}
								}
							}
							$pointer = $pointer->add($vector);
						}
					}
				}
			}
		}

		$send = [];
		$source = $this->source->floor();
		$radius = 2 * $this->size;
		$yield = (1 / $this->size) * 100;

		if($this->what instanceof Entity){
			Server::getInstance()->getPluginManager()->callEvent($ev = new EntityExplodeEvent($this->what, $this->source, $this->affectedBlocks, $yield));
			if($ev->isCancelled()){
				return false;
			}else{
				$yield = $ev->getYield();
				$this->affectedBlocks = $ev->getBlockList();
			}
		}

		//TODO
		/*foreach($server->api->entity->getRadius($this->source, $radius) as $entity){
			$impact = (1 - $this->source->distance($entity) / $radius) * 0.5; //placeholder, 0.7 should be exposure
			$damage = (int) (($impact * $impact + $impact) * 8 * $this->size + 1);
			$entity->harm($damage, "explosion");
		}*/


		foreach($this->affectedBlocks as $block){
			if($block instanceof TNT){
				$data = array(
					"x" => $block->x + 0.5,
					"y" => $block->y + 0.5,
					"z" => $block->z + 0.5,
					"power" => 4,
					"fuse" => mt_rand(10, 30), //0.5 to 1.5 seconds
				);
				//TODO
				//$e = $server->api->entity->add($this->level, ENTITY_OBJECT, OBJECT_PRIMEDTNT, $data);
				//$e->spawnToAll();
			}elseif(mt_rand(0, 100) < $yield){
				if(isset(self::$specialDrops[$block->getID()])){
					//TODO
					//$server->api->entity->drop(new Position($block->x + 0.5, $block->y, $block->z + 0.5, $this->level), Item::get(self::$specialDrops[$block->getID()], 0));
				}else{
					//TODO
					//$server->api->entity->drop(new Position($block->x + 0.5, $block->y, $block->z + 0.5, $this->level), Item::get($block->getID(), $this->level->level->getBlockDamage($block->x, $block->y, $block->z)));
				}
			}
			$this->level->setBlockIdAt($block->x, $block->y, $block->z, 0);
			$send[] = new Vector3($block->x - $source->x, $block->y - $source->y, $block->z - $source->z);
		}
		$pk = new ExplodePacket;
		$pk->x = $this->source->x;
		$pk->y = $this->source->y;
		$pk->z = $this->source->z;
		$pk->radius = $this->size;
		$pk->records = $send;
		Server::broadcastPacket($this->level->getPlayers(), $pk);

	}
}
