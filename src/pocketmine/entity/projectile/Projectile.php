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

use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityCombustByEntityEvent;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;

abstract class Projectile extends Entity{

	public const DATA_SHOOTER_ID = 17;

	protected $damage = 0;

	public $hadCollision = false;

	public function __construct(Level $level, CompoundTag $nbt, Entity $shootingEntity = null){
		parent::__construct($level, $nbt);
		if($shootingEntity !== null){
			$this->setOwningEntity($shootingEntity);
		}
	}

	public function attack(EntityDamageEvent $source){
		if($source->getCause() === EntityDamageEvent::CAUSE_VOID){
			parent::attack($source);
		}
	}

	protected function initEntity(){
		parent::initEntity();

		$this->setMaxHealth(1);
		$this->setHealth(1);
		$this->age = $this->namedtag->getShort("Age", $this->age);
	}

	public function canCollideWith(Entity $entity) : bool{
		return $entity instanceof Living and !$this->onGround;
	}

	/**
	 * Returns the amount of damage this projectile will deal to the entity it hits.
	 * @return int
	 */
	public function getResultDamage() : int{
		return (int) ceil(sqrt($this->motionX ** 2 + $this->motionY ** 2 + $this->motionZ ** 2) * $this->damage);
	}

	public function onCollideWithEntity(Entity $entity){
		$this->server->getPluginManager()->callEvent(new ProjectileHitEvent($this));

		$damage = $this->getResultDamage();

		if($this->getOwningEntity() === null){
			$ev = new EntityDamageByEntityEvent($this, $entity, EntityDamageEvent::CAUSE_PROJECTILE, $damage);
		}else{
			$ev = new EntityDamageByChildEntityEvent($this->getOwningEntity(), $this, $entity, EntityDamageEvent::CAUSE_PROJECTILE, $damage);
		}

		$entity->attack($ev);

		$this->hadCollision = true;

		if($this->fireTicks > 0){
			$ev = new EntityCombustByEntityEvent($this, $entity, 5);
			$this->server->getPluginManager()->callEvent($ev);
			if(!$ev->isCancelled()){
				$entity->setOnFire($ev->getDuration());
			}
		}

		$this->flagForDespawn();
	}

	public function saveNBT(){
		parent::saveNBT();
		$this->namedtag->setShort("Age", $this->age);
	}

	protected function applyDragBeforeGravity() : bool{
		return true;
	}

	public function entityBaseTick(int $tickDiff = 1) : bool{
		if($this->closed){
			return false;
		}

		$hasUpdate = parent::entityBaseTick($tickDiff);

		if(!$this->isFlaggedForDespawn()){
			$movingObjectPosition = null;

			$moveVector = new Vector3($this->x + $this->motionX, $this->y + $this->motionY, $this->z + $this->motionZ);

			$list = $this->getLevel()->getCollidingEntities($this->boundingBox->addCoord($this->motionX, $this->motionY, $this->motionZ)->expand(1, 1, 1), $this);

			$nearDistance = PHP_INT_MAX;
			$nearEntity = null;

			foreach($list as $entity){
				if(/*!$entity->canCollideWith($this) or */
				($entity->getId() === $this->getOwningEntityId() and $this->ticksLived < 5)
				){
					continue;
				}

				$axisalignedbb = $entity->boundingBox->grow(0.3, 0.3, 0.3);
				$rayTraceResult = $axisalignedbb->calculateIntercept($this, $moveVector);

				if($rayTraceResult === null){
					continue;
				}

				$distance = $this->distanceSquared($rayTraceResult->hitVector);

				if($distance < $nearDistance){
					$nearDistance = $distance;
					$nearEntity = $entity;
				}
			}

			if($nearEntity !== null){
				$this->onCollideWithEntity($nearEntity);
				return false;
			}

			if($this->isCollided and !$this->hadCollision){ //Collided with a block
				$this->hadCollision = true;

				$this->motionX = 0;
				$this->motionY = 0;
				$this->motionZ = 0;

				$this->server->getPluginManager()->callEvent(new ProjectileHitEvent($this));
				return false;
			}elseif(!$this->isCollided and $this->hadCollision){ //Previously collided with block, but block later removed
				$this->hadCollision = false;
			}

			if(!$this->hadCollision or abs($this->motionX) > self::MOTION_THRESHOLD or abs($this->motionY) > self::MOTION_THRESHOLD or abs($this->motionZ) > self::MOTION_THRESHOLD){
				$f = sqrt(($this->motionX ** 2) + ($this->motionZ ** 2));
				$this->yaw = (atan2($this->motionX, $this->motionZ) * 180 / M_PI);
				$this->pitch = (atan2($this->motionY, $f) * 180 / M_PI);
				$hasUpdate = true;
			}
		}

		return $hasUpdate;
	}

}
