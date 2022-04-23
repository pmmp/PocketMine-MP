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

namespace pocketmine\entity\projectile;

use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityCombustByEntityEvent;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ProjectileHitBlockEvent;
use pocketmine\event\entity\ProjectileHitEntityEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\level\Level;
use pocketmine\math\RayTraceResult;
use pocketmine\math\Vector3;
use pocketmine\math\VoxelRayTrace;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\timings\Timings;
use function assert;
use function atan2;
use function ceil;
use function sqrt;
use const M_PI;
use const PHP_INT_MAX;

abstract class Projectile extends Entity{

	/** @var float */
	protected $damage = 0.0;

	/** @var Vector3|null */
	protected $blockHit;
	/** @var int|null */
	protected $blockHitId;
	/** @var int|null */
	protected $blockHitData;

	public function __construct(Level $level, CompoundTag $nbt, ?Entity $shootingEntity = null){
		parent::__construct($level, $nbt);
		if($shootingEntity !== null){
			$this->setOwningEntity($shootingEntity);
		}
	}

	public function attack(EntityDamageEvent $source) : void{
		if($source->getCause() === EntityDamageEvent::CAUSE_VOID){
			parent::attack($source);
		}
	}

	protected function initEntity() : void{
		parent::initEntity();

		$this->setMaxHealth(1);
		$this->setHealth(1);
		$this->damage = $this->namedtag->getDouble("damage", $this->damage);

		(function() : void{
			if($this->namedtag->hasTag("tileX", IntTag::class) and $this->namedtag->hasTag("tileY", IntTag::class) and $this->namedtag->hasTag("tileZ", IntTag::class)){
				$blockHit = new Vector3($this->namedtag->getInt("tileX"), $this->namedtag->getInt("tileY"), $this->namedtag->getInt("tileZ"));
			}else{
				return;
			}

			if($this->namedtag->hasTag("blockId", IntTag::class)){
				$blockId = $this->namedtag->getInt("blockId");
			}else{
				return;
			}

			if($this->namedtag->hasTag("blockData", ByteTag::class)){
				$blockData = $this->namedtag->getByte("blockData");
			}else{
				return;
			}

			$this->blockHit = $blockHit;
			$this->blockHitId = $blockId;
			$this->blockHitData = $blockData;
		})();
	}

	public function canCollideWith(Entity $entity) : bool{
		return $entity instanceof Living and !$this->onGround;
	}

	public function canBeCollidedWith() : bool{
		return false;
	}

	/**
	 * Returns the base damage applied on collision. This is multiplied by the projectile's speed to give a result
	 * damage.
	 */
	public function getBaseDamage() : float{
		return $this->damage;
	}

	/**
	 * Sets the base amount of damage applied by the projectile.
	 */
	public function setBaseDamage(float $damage) : void{
		$this->damage = $damage;
	}

	/**
	 * Returns the amount of damage this projectile will deal to the entity it hits.
	 */
	public function getResultDamage() : int{
		return (int) ceil($this->motion->length() * $this->damage);
	}

	public function saveNBT() : void{
		parent::saveNBT();

		$this->namedtag->setDouble("damage", $this->damage);

		if($this->blockHit !== null){
			$this->namedtag->setInt("tileX", $this->blockHit->x);
			$this->namedtag->setInt("tileY", $this->blockHit->y);
			$this->namedtag->setInt("tileZ", $this->blockHit->z);

			//we intentionally use different ones to PC because we don't have stringy IDs
			$this->namedtag->setInt("blockId", $this->blockHitId);
			$this->namedtag->setByte("blockData", $this->blockHitData);
		}
	}

	protected function applyDragBeforeGravity() : bool{
		return true;
	}

	public function onNearbyBlockChange() : void{
		if($this->blockHit !== null){
			$blockIn = $this->level->getBlockAt($this->blockHit->x, $this->blockHit->y, $this->blockHit->z);
			if($blockIn->getId() !== $this->blockHitId or $blockIn->getDamage() !== $this->blockHitData){
				$this->blockHit = $this->blockHitId = $this->blockHitData = null;
			}
		}

		parent::onNearbyBlockChange();
	}

	public function hasMovementUpdate() : bool{
		return $this->blockHit === null and parent::hasMovementUpdate();
	}

	public function move(float $dx, float $dy, float $dz) : void{
		$this->blocksAround = null;

		Timings::$entityMoveTimer->startTiming();

		$start = $this->asVector3();
		$end = $start->add($dx, $dy, $dz);

		$blockHit = null;
		$entityHit = null;
		$hitResult = null;

		foreach(VoxelRayTrace::betweenPoints($start, $end) as $vector3){
			$block = $this->level->getBlockAt($vector3->x, $vector3->y, $vector3->z);

			$blockHitResult = $this->calculateInterceptWithBlock($block, $start, $end);
			if($blockHitResult !== null){
				$end = $blockHitResult->hitVector;
				$blockHit = $block;
				$hitResult = $blockHitResult;
				break;
			}
		}

		$entityDistance = PHP_INT_MAX;

		$newDiff = $end->subtract($start);
		foreach($this->level->getCollidingEntities($this->boundingBox->addCoord($newDiff->x, $newDiff->y, $newDiff->z)->expand(1, 1, 1), $this) as $entity){
			if($entity->getId() === $this->getOwningEntityId() and $this->ticksLived < 5){
				continue;
			}

			$entityBB = $entity->boundingBox->expandedCopy(0.3, 0.3, 0.3);
			$entityHitResult = $entityBB->calculateIntercept($start, $end);

			if($entityHitResult === null){
				continue;
			}

			$distance = $this->distanceSquared($entityHitResult->hitVector);

			if($distance < $entityDistance){
				$entityDistance = $distance;
				$entityHit = $entity;
				$hitResult = $entityHitResult;
				$end = $entityHitResult->hitVector;
			}
		}

		$this->x = $end->x;
		$this->y = $end->y;
		$this->z = $end->z;
		$this->recalculateBoundingBox();

		if($hitResult !== null){
			/** @var ProjectileHitEvent|null $ev */
			$ev = null;
			if($entityHit !== null){
				$ev = new ProjectileHitEntityEvent($this, $hitResult, $entityHit);
			}elseif($blockHit !== null){
				$ev = new ProjectileHitBlockEvent($this, $hitResult, $blockHit);
			}else{
				assert(false, "unknown hit type");
			}

			if($ev !== null){
				$ev->call();
				$this->onHit($ev);

				if($ev instanceof ProjectileHitEntityEvent){
					$this->onHitEntity($ev->getEntityHit(), $ev->getRayTraceResult());
				}elseif($ev instanceof ProjectileHitBlockEvent){
					$this->onHitBlock($ev->getBlockHit(), $ev->getRayTraceResult());
				}
			}

			$this->isCollided = $this->onGround = true;
			$this->motion->x = $this->motion->y = $this->motion->z = 0;
		}else{
			$this->isCollided = $this->onGround = false;
			$this->blockHit = $this->blockHitId = $this->blockHitData = null;

			//recompute angles...
			$f = sqrt(($this->motion->x ** 2) + ($this->motion->z ** 2));
			$this->yaw = (atan2($this->motion->x, $this->motion->z) * 180 / M_PI);
			$this->pitch = (atan2($this->motion->y, $f) * 180 / M_PI);
		}

		$this->checkChunks();
		$this->checkBlockCollision();

		Timings::$entityMoveTimer->stopTiming();
	}

	/**
	 * Called by move() when raytracing blocks to discover whether the block should be considered as a point of impact.
	 * This can be overridden by other projectiles to allow altering the blocks which are collided with (for example
	 * some projectiles collide with any non-air block).
	 *
	 * @return RayTraceResult|null the result of the ray trace if successful, or null if no interception is found.
	 */
	protected function calculateInterceptWithBlock(Block $block, Vector3 $start, Vector3 $end) : ?RayTraceResult{
		return $block->calculateIntercept($start, $end);
	}

	/**
	 * Called when the projectile hits something. Override this to perform non-target-specific effects when the
	 * projectile hits something.
	 */
	protected function onHit(ProjectileHitEvent $event) : void{

	}

	/**
	 * Called when the projectile collides with an Entity.
	 */
	protected function onHitEntity(Entity $entityHit, RayTraceResult $hitResult) : void{
		$damage = $this->getResultDamage();

		if($damage >= 0){
			if($this->getOwningEntity() === null){
				$ev = new EntityDamageByEntityEvent($this, $entityHit, EntityDamageEvent::CAUSE_PROJECTILE, $damage);
			}else{
				$ev = new EntityDamageByChildEntityEvent($this->getOwningEntity(), $this, $entityHit, EntityDamageEvent::CAUSE_PROJECTILE, $damage);
			}

			$entityHit->attack($ev);

			if($this->isOnFire()){
				$ev = new EntityCombustByEntityEvent($this, $entityHit, 5);
				$ev->call();
				if(!$ev->isCancelled()){
					$entityHit->setOnFire($ev->getDuration());
				}
			}
		}

		$this->flagForDespawn();
	}

	/**
	 * Called when the projectile collides with a Block.
	 */
	protected function onHitBlock(Block $blockHit, RayTraceResult $hitResult) : void{
		$this->blockHit = $blockHit->asVector3();
		$this->blockHitId = $blockHit->getId();
		$this->blockHitData = $blockHit->getDamage();
	}
}
