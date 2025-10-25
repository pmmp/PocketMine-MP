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
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\block\TNT;
use pocketmine\block\utils\SupportType;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Entity;
use pocketmine\event\block\BlockExplodeEvent;
use pocketmine\event\entity\EntityDamageByBlockEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\item\VanillaItems;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Utils;
use pocketmine\world\format\SubChunk;
use pocketmine\world\particle\HugeExplodeSeedParticle;
use pocketmine\world\sound\ExplodeSound;
use pocketmine\world\utils\SubChunkExplorer;
use pocketmine\world\utils\SubChunkExplorerStatus;
use function ceil;
use function floor;
use function min;
use function mt_rand;
use function sqrt;

class Explosion{
	public const DEFAULT_FIRE_CHANCE = 1.0 / 3.0;

	private int $rays = 16;
	public World $world;

	/**
	 * @var Block[]
	 * @phpstan-var array<int, Block>
	 */
	public array $affectedBlocks = [];
	public float $stepLen = 0.3;
	/** @var Block[] */
	private array $fireIgnitions = [];

	private SubChunkExplorer $subChunkExplorer;

	public function __construct(
		public Position $source,
		public float $radius,
		private Entity|Block|null $what = null,
		private float $fireChance = 0.0
	){
		if(!$this->source->isValid()){
			throw new \InvalidArgumentException("Position does not have a valid world");
		}
		$this->world = $this->source->getWorld();
		Utils::checkFloatNotInfOrNaN("fireChance", $fireChance);
		if($fireChance < 0.0 || $fireChance > 1.0){
			throw new \InvalidArgumentException("Fire chance must be a number between 0 and 1.");
		}
		if($radius <= 0){
			throw new \InvalidArgumentException("Explosion radius must be greater than 0, got $radius");
		}
		$this->subChunkExplorer = new SubChunkExplorer($this->world);
	}

	/**
	 * Calculates which blocks will be destroyed by this explosion. If explodeB() is called without calling this, no blocks
	 * will be destroyed.
	 */
	public function explodeA() : bool{
		if($this->radius < 0.1){
			return false;
		}

		$blockFactory = RuntimeBlockStateRegistry::getInstance();

		$mRays = $this->rays - 1;
		$incendiary = $this->fireChance > 0;
		for($i = 0; $i < $this->rays; ++$i){
			for($j = 0; $j < $this->rays; ++$j){
				for($k = 0; $k < $this->rays; ++$k){
					if($i === 0 || $i === $mRays || $j === 0 || $j === $mRays || $k === 0 || $k === $mRays){
						//this could be written as new Vector3(...)->normalize()->multiply(stepLen), but we're avoiding Vector3 for performance here
						[$shiftX, $shiftY, $shiftZ] = [$i / $mRays * 2 - 1, $j / $mRays * 2 - 1, $k / $mRays * 2 - 1];
						$len = sqrt($shiftX ** 2 + $shiftY ** 2 + $shiftZ ** 2);
						[$shiftX, $shiftY, $shiftZ] = [($shiftX / $len) * $this->stepLen, ($shiftY / $len) * $this->stepLen, ($shiftZ / $len) * $this->stepLen];
						$pointerX = $this->source->x;
						$pointerY = $this->source->y;
						$pointerZ = $this->source->z;

						for($blastForce = $this->radius * (mt_rand(700, 1300) / 1000); $blastForce > 0; $blastForce -= $this->stepLen * 0.75){
							$x = (int) $pointerX;
							$y = (int) $pointerY;
							$z = (int) $pointerZ;
							$vBlockX = $pointerX >= $x ? $x : $x - 1;
							$vBlockY = $pointerY >= $y ? $y : $y - 1;
							$vBlockZ = $pointerZ >= $z ? $z : $z - 1;

							$pointerX += $shiftX;
							$pointerY += $shiftY;
							$pointerZ += $shiftZ;

							if($this->subChunkExplorer->moveTo($vBlockX, $vBlockY, $vBlockZ) === SubChunkExplorerStatus::INVALID){
								continue;
							}
							$subChunk = $this->subChunkExplorer->currentSubChunk;
							if($subChunk === null){
								throw new AssumptionFailedError("SubChunkExplorer subchunk should not be null here");
							}

							$state = $subChunk->getBlockStateId($vBlockX & SubChunk::COORD_MASK, $vBlockY & SubChunk::COORD_MASK, $vBlockZ & SubChunk::COORD_MASK);

							$blastResistance = $blockFactory->blastResistance[$state] ?? 0;
							if($blastResistance >= 0){
								$blastForce -= ($blastResistance / 5 + 0.3) * $this->stepLen;
								if($blastForce > 0){
									if(!isset($this->affectedBlocks[World::blockHash($vBlockX, $vBlockY, $vBlockZ)])){
										$_block = $this->world->getBlockAt($vBlockX, $vBlockY, $vBlockZ, true, false);
										foreach($_block->getAffectedBlocks() as $_affectedBlock){
											$_affectedBlockPos = $_affectedBlock->getPosition();
											$posHash = World::blockHash($_affectedBlockPos->x, $_affectedBlockPos->y, $_affectedBlockPos->z);
											$this->affectedBlocks[$posHash] = $_affectedBlock;

											if($incendiary && Utils::getRandomFloat() <= $this->fireChance){
												$this->fireIgnitions[$posHash] = $_affectedBlock;
											}
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
		$source = (new Vector3($this->source->x, $this->source->y, $this->source->z))->floor();
		$yield = min(100, (1 / $this->radius) * 100);

		if($this->what instanceof Entity){
			$ev = new EntityExplodeEvent($this->what, $this->source, $this->affectedBlocks, $yield, $this->fireIgnitions);

			$ev->call();
			if($ev->isCancelled()){
				return false;
			}

			$yield = $ev->getYield();
			$this->affectedBlocks = $ev->getBlockList();
			$this->fireIgnitions = $ev->getIgnitions();
		}elseif($this->what instanceof Block){
			$ev = new BlockExplodeEvent(
				$this->what,
				$this->source,
				$this->affectedBlocks,
				$yield,
				$this->fireIgnitions,
			);

			$ev->call();
			if($ev->isCancelled()){
				return false;
			}else{
				$yield = $ev->getYield();
				$this->affectedBlocks = $ev->getAffectedBlocks();
				$this->fireIgnitions = $ev->getIgnitions();
			}
		}

		$explosionSize = $this->radius * 2;
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
				$entity->setMotion($entity->getMotion()->addVector($motion->multiply($impact)));
			}
		}

		$air = VanillaItems::AIR();
		$airBlock = VanillaBlocks::AIR();
		$fireBlock = VanillaBlocks::FIRE();

		foreach($this->affectedBlocks as $hash => $block){
			$pos = $block->getPosition();
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
				$targetBlock =
					isset($this->fireIgnitions[$hash]) &&
					$block->getSide(Facing::DOWN)->getSupportType(Facing::UP) === SupportType::FULL ?
						$fireBlock :
						$airBlock;

				$this->world->setBlockAt($pos->x, $pos->y, $pos->z, $targetBlock);
			}
		}

		$this->world->addParticle($source, new HugeExplodeSeedParticle());
		$this->world->addSound($source, new ExplodeSound());

		return true;
	}

	/**
	 * Sets a chance between 0 and 1 of creating a fire.
	 * For example, if the chance is 1/3, then that amount of affected blocks will be ignited.
	 *
	 * @param float $fireChance 0 ... 1
	 */
	public function setFireChance(float $fireChance) : void{
		Utils::checkFloatNotInfOrNaN("fireChance", $fireChance);
		if($fireChance < 0.0 || $fireChance > 1.0){
			throw new \InvalidArgumentException("Fire chance must be a number between 0 and 1.");
		}
		$this->fireChance = $fireChance;
	}
}
