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

declare(strict_types=1);

namespace pocketmine\world;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\TNT;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Entity;
use pocketmine\event\block\BlockUpdateEvent;
use pocketmine\event\entity\EntityDamageByBlockEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\item\ItemFactory;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\world\particle\HugeExplodeSeedParticle;
use pocketmine\world\sound\ExplodeSound;
use pocketmine\world\utils\SubChunkIteratorManager;
use function ceil;
use function floor;
use function mt_rand;
use function sqrt;

class Explosion{
	/** @var int */
	private $rays = 16;
	/** @var World */
	public $world;
	/** @var Position */
	public $source;
	/** @var float */
	public $size;

	/** @var Block[] */
	public $affectedBlocks = [];
	/** @var float */
	public $stepLen = 0.3;
	/** @var Entity|Block|null */
	private $what;

	/** @var SubChunkIteratorManager */
	private $subChunkHandler;

	/**
	 * @param Entity|Block|null $what
	 */
	public function __construct(Position $center, float $size, $what = null){
		if(!$center->isValid()){
			throw new \InvalidArgumentException("Position does not have a valid world");
		}
		$this->source = $center;
		$this->world = $center->getWorld();

		if($size <= 0){
			throw new \InvalidArgumentException("Explosion radius must be greater than 0, got $size");
		}
		$this->size = $size;

		$this->what = $what;
		$this->subChunkHandler = new SubChunkIteratorManager($this->world);
	}

	/**
	 * Calculates which blocks will be destroyed by this explosion. If explodeB() is called without calling this, no blocks
	 * will be destroyed.
	 */
	public function explodeA() : bool{
		if($this->size < 0.1){
			return false;
		}

		$blockFactory = BlockFactory::getInstance();

		$currentChunk = null;
		$currentSubChunk = null;

		$mRays = $this->rays - 1;
		for($i = 0; $i < $this->rays; ++$i){
			for($j = 0; $j < $this->rays; ++$j){
				for($k = 0; $k < $this->rays; ++$k){
					if($i === 0 or $i === $mRays or $j === 0 or $j === $mRays or $k === 0 or $k === $mRays){
						//this could be written as new Vector3(...)->normalize()->multiply(stepLen), but we're avoiding Vector3 for performance here
						[$shiftX, $shiftY, $shiftZ] = [$i / $mRays * 2 - 1, $j / $mRays * 2 - 1, $k / $mRays * 2 - 1];
						$len = sqrt($shiftX ** 2 + $shiftY ** 2 + $shiftZ ** 2);
						[$shiftX, $shiftY, $shiftZ] = [($shiftX / $len) * $this->stepLen, ($shiftY / $len) * $this->stepLen, ($shiftZ / $len) * $this->stepLen];
						$pointerX = $this->source->x;
						$pointerY = $this->source->y;
						$pointerZ = $this->source->z;

						for($blastForce = $this->size * (mt_rand(700, 1300) / 1000); $blastForce > 0; $blastForce -= $this->stepLen * 0.75){
							$x = (int) $pointerX;
							$y = (int) $pointerY;
							$z = (int) $pointerZ;
							$vBlockX = $pointerX >= $x ? $x : $x - 1;
							$vBlockY = $pointerY >= $y ? $y : $y - 1;
							$vBlockZ = $pointerZ >= $z ? $z : $z - 1;

							$pointerX += $shiftX;
							$pointerY += $shiftY;
							$pointerZ += $shiftZ;

							if(!$this->subChunkHandler->moveTo($vBlockX, $vBlockY, $vBlockZ, false)){
								continue;
							}

							$state = $this->subChunkHandler->currentSubChunk->getFullBlock($vBlockX & 0x0f, $vBlockY & 0x0f, $vBlockZ & 0x0f);

							if($state !== 0){
								$blastForce -= ($blockFactory->blastResistance[$state] / 5 + 0.3) * $this->stepLen;
								if($blastForce > 0){
									if(!isset($this->affectedBlocks[World::blockHash($vBlockX, $vBlockY, $vBlockZ)])){
										$_block = $blockFactory->fromFullBlock($state);
										$_block->position($this->world, $vBlockX, $vBlockY, $vBlockZ);
										foreach($_block->getAffectedBlocks() as $_affectedBlock){
											$_affectedBlockPos = $_affectedBlock->getPos();
											$this->affectedBlocks[World::blockHash($_affectedBlockPos->x, $_affectedBlockPos->y, $_affectedBlockPos->z)] = $_affectedBlock;
										}
									}
								}
							}
						}
					}
				}
			}
		}

		return true;
	}

	/**
	 * Executes the explosion's effects on the world. This includes destroying blocks (if any), harming and knocking back entities,
	 * and creating sounds and particles.
	 */
	public function explodeB() : bool{
		$send = [];
		$updateBlocks = [];

		$source = (new Vector3($this->source->x, $this->source->y, $this->source->z))->floor();
		$yield = (1 / $this->size) * 100;

		if($this->what instanceof Entity){
			$ev = new EntityExplodeEvent($this->what, $this->source, $this->affectedBlocks, $yield);
			$ev->call();
			if($ev->isCancelled()){
				return false;
			}else{
				$yield = $ev->getYield();
				$this->affectedBlocks = $ev->getBlockList();
			}
		}

		$explosionSize = $this->size * 2;
		$minX = (int) floor($this->source->x - $explosionSize - 1);
		$maxX = (int) ceil($this->source->x + $explosionSize + 1);
		$minY = (int) floor($this->source->y - $explosionSize - 1);
		$maxY = (int) ceil($this->source->y + $explosionSize + 1);
		$minZ = (int) floor($this->source->z - $explosionSize - 1);
		$maxZ = (int) ceil($this->source->z + $explosionSize + 1);

		$explosionBB = new AxisAlignedBB($minX, $minY, $minZ, $maxX, $maxY, $maxZ);

		/** @var Entity[] $list */
		$list = $this->world->getNearbyEntities($explosionBB, $this->what instanceof Entity ? $this->what : null);
		foreach($list as $entity){
			$entityPos = $entity->getPosition();
			$distance = $entityPos->distance($this->source) / $explosionSize;

			if($distance <= 1){
				$motion = $entityPos->subtractVector($this->source)->normalize();

				$impact = (1 - $distance) * ($exposure = 1);

				$damage = (int) ((($impact * $impact + $impact) / 2) * 8 * $explosionSize + 1);

				if($this->what instanceof Entity){
					$ev = new EntityDamageByEntityEvent($this->what, $entity, EntityDamageEvent::CAUSE_ENTITY_EXPLOSION, $damage);
				}elseif($this->what instanceof Block){
					$ev = new EntityDamageByBlockEvent($this->what, $entity, EntityDamageEvent::CAUSE_BLOCK_EXPLOSION, $damage);
				}else{
					$ev = new EntityDamageEvent($entity, EntityDamageEvent::CAUSE_BLOCK_EXPLOSION, $damage);
				}

				$entity->attack($ev);
				$entity->setMotion($motion->multiply($impact));
			}
		}

		$air = ItemFactory::air();
		$airBlock = VanillaBlocks::AIR();

		foreach($this->affectedBlocks as $block){
			$pos = $block->getPos();
			if($block instanceof TNT){
				$block->ignite(mt_rand(10, 30));
			}else{
				if(mt_rand(0, 100) < $yield){
					foreach($block->getDrops($air) as $drop){
						$this->world->dropItem($pos->add(0.5, 0.5, 0.5), $drop);
					}
				}
				if(($t = $this->world->getTileAt($pos->x, $pos->y, $pos->z)) !== null){
					$t->onBlockDestroyed(); //needed to create drops for inventories
				}
				$this->world->setBlockAt($pos->x, $pos->y, $pos->z, $airBlock, false); //TODO: should updating really be disabled here?
				$this->world->updateAllLight($pos->x, $pos->y, $pos->z);
			}
		}

		foreach($this->affectedBlocks as $block){
			$pos = $block->getPos();
			foreach(Facing::ALL as $side){
				$sideBlock = $pos->getSide($side);
				if(!$this->world->isInWorld($sideBlock->x, $sideBlock->y, $sideBlock->z)){
					continue;
				}
				if(!isset($this->affectedBlocks[$index = World::blockHash($sideBlock->x, $sideBlock->y, $sideBlock->z)]) and !isset($updateBlocks[$index])){
					$ev = new BlockUpdateEvent($this->world->getBlockAt($sideBlock->x, $sideBlock->y, $sideBlock->z));
					$ev->call();
					if(!$ev->isCancelled()){
						foreach($this->world->getNearbyEntities(AxisAlignedBB::one()->offset($sideBlock->x, $sideBlock->y, $sideBlock->z)->expand(1, 1, 1)) as $entity){
							$entity->onNearbyBlockChange();
						}
						$ev->getBlock()->onNearbyBlockChange();
					}
					$updateBlocks[$index] = true;
				}
			}
			$send[] = $pos->subtractVector($source);
		}

		$this->world->addParticle($source, new HugeExplodeSeedParticle());
		$this->world->addSound($source, new ExplodeSound());

		return true;
	}
}
