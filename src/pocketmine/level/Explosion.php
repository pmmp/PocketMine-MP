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

use pocketmine\block\Air;
use pocketmine\block\Block;
use pocketmine\block\TNT;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByBlockEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Math;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\Byte;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Double;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Float;
use pocketmine\network\protocol\ExplodePacket;
use pocketmine\Server;
use pocketmine\utils\Random;

class Explosion{

	private $rays = 16; //Rays
	public $level;
	public $source;
	public $size;
	/**
	 * @var Block[]
	 */
	public $affectedBlocks = [];
	public $stepLen = 0.3;
	/** @var Entity|Block */
	private $what;

	public function __construct(Position $center, $size, $what = null){
		$this->level = $center->getLevel();
		$this->source = $center;
		$this->size = max($size, 0);
		$this->what = $what;
	}

	/**
	 * @deprecated
	 * @return bool
	 */
	public function explode(){
		if($this->explodeA()){
			return $this->explodeB();
		}

		return false;
	}

	/**
	 * @return bool
	 */
	public function explodeA(){
		if($this->size < 0.1){
			return false;
		}

		$pointer = Vector3::createVector(0, 0, 0);
		$vector = Vector3::createVector(0, 0, 0);
		$vBlock = Vector3::createVector(0, 0, 0);

		$mRays = $this->rays - 1;
		for($i = 0; $i < $this->rays; ++$i){
			for($j = 0; $j < $this->rays; ++$j){
				//break 2 gets here
				for($k = 0; $k < $this->rays; ++$k){
					if($i == 0 or $i == $mRays or $j == 0 or $j == $mRays or $k == 0 or $k == $mRays){
						$vector->setComponents($i / $mRays * 2 - 1, $j / $mRays * 2 - 1, $k / $mRays * 2 - 1);
						$vector->setComponents(($vector->x / ($len = $vector->length())) * $this->stepLen, ($vector->y / $len) * $this->stepLen, ($vector->z / $len) * $this->stepLen);
						$pointer->setComponents($this->source->x, $this->source->y, $this->source->z);

						for($blastForce = $this->size * (mt_rand(700, 1300) / 1000); $blastForce > 0; $blastForce -= $this->stepLen * 0.75){
							$x = (int) $pointer->x;
							$y = (int) $pointer->y;
							$z = (int) $pointer->z;
							$vBlock->setComponents($pointer->x >= $x ? $x : $x - 1, $pointer->y >= $y ? $y : $y - 1, $pointer->z >= $z ? $z : $z - 1);
							if($vBlock->y < 0 or $vBlock->y > 127){
								break;
							}
							$block = $this->level->getBlock($vBlock);

							if(!($block instanceof Air)){
								$blastForce -= ($block->getHardness() / 5 + 0.3) * $this->stepLen;
								if($blastForce > 0){
									$index = ($block->x << 15) + ($block->z << 7) + $block->y;
									if(!isset($this->affectedBlocks[$index])){
										$this->affectedBlocks[$index] = $block;
									}
								}
							}
							$pointer->x += $vector->x;
							$pointer->y += $vector->y;
							$pointer->z += $vector->z;
						}
					}
				}
			}
		}

		return true;
	}

	public function explodeB(){
		$send = [];
		$source = Vector3::cloneVector($this->source)->floor();
		$yield = (1 / $this->size) * 100;

		if($this->what instanceof Entity){
			$this->level->getServer()->getPluginManager()->callEvent($ev = EntityExplodeEvent::createEvent($this->what, $this->source, $this->affectedBlocks, $yield));
			if($ev->isCancelled()){
				return false;
			}else{
				$yield = $ev->getYield();
				$this->affectedBlocks = $ev->getBlockList();
			}
		}

		$explosionSize = $this->size * 2;
		$minX = Math::floorFloat($this->source->x - $explosionSize - 1);
		$maxX = Math::floorFloat($this->source->x + $explosionSize + 1);
		$minY = Math::floorFloat($this->source->y - $explosionSize - 1);
		$maxY = Math::floorFloat($this->source->y + $explosionSize + 1);
		$minZ = Math::floorFloat($this->source->z - $explosionSize - 1);
		$maxZ = Math::floorFloat($this->source->z + $explosionSize + 1);

		$explosionBB = AxisAlignedBB::getBoundingBoxFromPool($minX, $minY, $minZ, $maxX, $maxY, $maxZ);

		$list = $this->level->getNearbyEntities($explosionBB, $this->what instanceof Entity ? $this->what : null);
		foreach($list as $entity){
			$distance = $entity->distance($this->source) / $explosionSize;

			if($distance <= 1){
				$motion = $entity->subtract($this->source)->normalize();

				$impact = (1 - $distance) * ($exposure = 1);

				$damage = (int) ((($impact * $impact + $impact) / 2) * 8 * $explosionSize + 1);

				if($this->what instanceof Entity){
					$ev = EntityDamageByEntityEvent::createEvent($this->what, $entity, EntityDamageEvent::CAUSE_ENTITY_EXPLOSION, $damage);
				}elseif($this->what instanceof Block){
					$ev = EntityDamageByBlockEvent::createEvent($this->what, $entity, EntityDamageEvent::CAUSE_BLOCK_EXPLOSION, $damage);
				}else{
					$ev = EntityDamageEvent::createEvent($entity, EntityDamageEvent::CAUSE_BLOCK_EXPLOSION, $damage);
				}

				$entity->attack($ev->getFinalDamage(), $ev);
				$entity->setMotion($motion->multiply($impact));
			}
		}


		$air = Item::get(Item::AIR);

		foreach($this->affectedBlocks as $block){
			$block->setDamage($this->level->getBlockDataAt($block->x, $block->y, $block->z));

			if($block instanceof TNT){
				$mot = (new Random())->nextSignedFloat() * M_PI * 2;
				$tnt = Entity::createEntity("PrimedTNT", $this->level->getChunk($block->x >> 4, $block->z >> 4), new Compound("", [
					"Pos" => new Enum("Pos", [
						new Double("", $block->x + 0.5),
						new Double("", $block->y),
						new Double("", $block->z + 0.5)
					]),
					"Motion" => new Enum("Motion", [
						new Double("", -sin($mot) * 0.02),
						new Double("", 0.2),
						new Double("", -cos($mot) * 0.02)
					]),
					"Rotation" => new Enum("Rotation", [
						new Float("", 0),
						new Float("", 0)
					]),
					"Fuse" => new Byte("Fuse", mt_rand(10, 30))
				]));
				$tnt->spawnToAll();
			}elseif(mt_rand(0, 100) < $yield){
				foreach($block->getDrops($air) as $drop){
					$this->level->dropItem($block->add(0.5, 0.5, 0.5), Item::get(...$drop));
				}
			}
			$this->level->setBlockIdAt($block->x, $block->y, $block->z, 0);
			$send[] = new Vector3($block->x - $source->x, $block->y - $source->y, $block->z - $source->z);
		}
		$pk = ExplodePacket::getFromPool();
		$pk->x = $this->source->x;
		$pk->y = $this->source->y;
		$pk->z = $this->source->z;
		$pk->radius = $this->size;
		$pk->records = $send;
		Server::broadcastPacket($this->level->getUsingChunk($source->x >> 4, $source->z >> 4), $pk);

		return true;
	}
}
