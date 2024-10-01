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
use pocketmine\data\SavedDataLoadingException;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\entity\Location;
use pocketmine\entity\object\EndCrystal;
use pocketmine\event\entity\EntityCombustByEntityEvent;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ProjectileHitBlockEvent;
use pocketmine\event\entity\ProjectileHitEntityEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\math\RayTraceResult;
use pocketmine\math\Vector3;
use pocketmine\math\VoxelRayTrace;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\timings\Timings;
use function assert;
use function atan2;
use function ceil;
use function count;
use function sqrt;
use const M_PI;
use const PHP_INT_MAX;

abstract class Projectile extends Entity{
	private const TAG_STUCK_ON_BLOCK_POS = "StuckToBlockPos";
	private const TAG_DAMAGE = "damage"; //TAG_Double
	private const TAG_TILE_X = "tileX"; //TAG_Int
	private const TAG_TILE_Y = "tileY"; //TAG_Int
	private const TAG_TILE_Z = "tileZ"; //TAG_Int

	protected float $damage = 0.0;
	protected ?Vector3 $blockHit = null;

	public function __construct(Location $location, ?Entity $shootingEntity, ?CompoundTag $nbt = null){
		parent::__construct($location, $nbt);
		if($shootingEntity !== null){
			$this->setOwningEntity($shootingEntity);
		}
	}

	public function attack(EntityDamageEvent $source) : void{
		if($source->getCause() === EntityDamageEvent::CAUSE_VOID){
			parent::attack($source);
		}
	}

	protected function initEntity(CompoundTag $nbt) : void{
		parent::initEntity($nbt);

		$this->setMaxHealth(1);
		$this->setHealth(1);
		$this->damage = $nbt->getDouble(self::TAG_DAMAGE, $this->damage);

		if(($stuckOnBlockPosTag = $nbt->getListTag(self::TAG_STUCK_ON_BLOCK_POS)) !== null){
			if($stuckOnBlockPosTag->getTagType() !== NBT::TAG_Int || count($stuckOnBlockPosTag) !== 3){
				throw new SavedDataLoadingException(self::TAG_STUCK_ON_BLOCK_POS . " tag should be a list of 3 TAG_Int");
			}

			/** @var IntTag[] $values */
			$values = $stuckOnBlockPosTag->getValue();

			$this->blockHit = new Vector3($values[0]->getValue(), $values[1]->getValue(), $values[2]->getValue());
		}elseif(($tileXTag = $nbt->getTag(self::TAG_TILE_X)) instanceof IntTag && ($tileYTag = $nbt->getTag(self::TAG_TILE_Y)) instanceof IntTag && ($tileZTag = $nbt->getTag(self::TAG_TILE_Z)) instanceof IntTag){
			$this->blockHit = new Vector3($tileXTag->getValue(), $tileYTag->getValue(), $tileZTag->getValue());
		}
	}

	public function canCollideWith(Entity $entity) : bool{
		return ($entity instanceof Living || $entity instanceof EndCrystal) && !$this->onGround;
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
		return (int) ceil($this->damage);
	}

	public function saveNBT() : CompoundTag{
		$nbt = parent::saveNBT();

		$nbt->setDouble(self::TAG_DAMAGE, $this->damage);

		if($this->blockHit !== null){
			$nbt->setTag(self::TAG_STUCK_ON_BLOCK_POS, new ListTag([
				new IntTag($this->blockHit->getFloorX()),
				new IntTag($this->blockHit->getFloorY()),
				new IntTag($this->blockHit->getFloorZ())
			]));
		}

		return $nbt;
	}

	protected function applyDragBeforeGravity() : bool{
		return true;
	}

	public function onNearbyBlockChange() : void{
		if($this->blockHit !== null && $this->getWorld()->isInLoadedTerrain($this->blockHit)){
			$blockHit = $this->getWorld()->getBlock($this->blockHit);
			if(!$blockHit->collidesWithBB($this->getBoundingBox()->expandedCopy(0.001, 0.001, 0.001))){
				$this->blockHit = null;
			}
		}

		parent::onNearbyBlockChange();
	}

	public function hasMovementUpdate() : bool{
		return $this->blockHit === null && parent::hasMovementUpdate();
	}

	protected function move(float $dx, float $dy, float $dz) : void{
		$this->blocksAround = null;

		Timings::$projectileMove->startTiming();
		Timings::$projectileMoveRayTrace->startTiming();

		$start = $this->location->asVector3();
		$end = $start->add($dx, $dy, $dz);

		$blockHit = null;
		$entityHit = null;
		$hitResult = null;

		$world = $this->getWorld();
		foreach(VoxelRayTrace::betweenPoints($start, $end) as $vector3){
			$block = $world->getBlockAt($vector3->x, $vector3->y, $vector3->z);

			$blockHitResult = $this->calculateInterceptWithBlock($block, $start, $end);
			if($blockHitResult !== null){
				$end = $blockHitResult->hitVector;
				$blockHit = $block;
				$hitResult = $blockHitResult;
				break;
			}
		}

		$entityDistance = PHP_INT_MAX;

		$newDiff = $end->subtractVector($start);
		foreach($world->getCollidingEntities($this->boundingBox->addCoord($newDiff->x, $newDiff->y, $newDiff->z)->expand(1, 1, 1), $this) as $entity){
			if($entity->getId() === $this->getOwningEntityId() && $this->ticksLived < 5){
				continue;
			}

			$entityBB = $entity->boundingBox->expandedCopy(0.3, 0.3, 0.3);
			$entityHitResult = $entityBB->calculateIntercept($start, $end);

			if($entityHitResult === null){
				continue;
			}

			$distance = $this->location->distanceSquared($entityHitResult->hitVector);

			if($distance < $entityDistance){
				$entityDistance = $distance;
				$entityHit = $entity;
				$hitResult = $entityHitResult;
				$end = $entityHitResult->hitVector;
			}
		}

		Timings::$projectileMoveRayTrace->stopTiming();

		$this->location = Location::fromObject(
			$end,
			$this->location->world,
			$this->location->yaw,
			$this->location->pitch
		);
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
			$this->motion = Vector3::zero();
		}else{
			$this->isCollided = $this->onGround = false;
			$this->blockHit = null;

			//recompute angles...
			$f = sqrt(($this->motion->x ** 2) + ($this->motion->z ** 2));
			$this->setRotation(
				atan2($this->motion->x, $this->motion->z) * 180 / M_PI,
				atan2($this->motion->y, $f) * 180 / M_PI
			);
		}

		$world->onEntityMoved($this);
		$this->checkBlockIntersections();

		Timings::$projectileMove->stopTiming();
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
		$this->blockHit = $blockHit->getPosition()->asVector3();
		$blockHit->onProjectileHit($this, $hitResult);
	}
}
