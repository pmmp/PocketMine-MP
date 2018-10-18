<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\entity;


use pocketmine\entity\behavior\BehaviorPool;
use pocketmine\entity\pathfinder\EntityNavigator;
use pocketmine\item\Lead;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\math\VoxelRayTrace;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\EntityEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\Player;
use pocketmine\timings\Timings;

abstract class Mob extends Living{

	/** @var BehaviorPool */
	protected $behaviorPool;
	/** @var BehaviorPool */
	protected $targetBehaviorPool;
	/** @var EntityNavigator */
	protected $navigator;
	/** @var Vector3 */
	protected $lookPosition;
	/** @var Entity[] */
	protected $seenEntities = [];
	/** @var Entity[] */
	protected $unseenEntities = [];
	protected $jumpCooldown = 0;
	/** @var Vector3 */
	protected $homePosition;
	/** @var int */
	protected $livingSoundTime = 0;

	/**
	 * @return Vector3
	 */
	public function getHomePosition() : Vector3{
		return $this->homePosition;
	}

	/**
	 * Get number of ticks, at least during which the living entity will be silent.
	 *
	 * @return int
	 */
	public function getTalkInterval() : int{
		return 80;
	}

	/**
	 * @param Vector3 $homePosition
	 */
	public function setHomePosition(Vector3 $homePosition) : void{
		$this->homePosition = $homePosition;
	}

	/**
	 * @param CompoundTag $nbt
	 */
	protected function initEntity(CompoundTag $nbt) : void{
		parent::initEntity($nbt);

		$this->targetBehaviorPool = new BehaviorPool();
		$this->behaviorPool = new BehaviorPool();
		$this->navigator = new EntityNavigator($this);

		$this->addBehaviors();

		$this->setDefaultMovementSpeed($this->getMovementSpeed());
		$this->setImmobile(boolval($nbt->getByte("NoAI", 1)));
	}

	/**
	 * @return CompoundTag
	 */
	public function saveNBT() : CompoundTag{
		$nbt = parent::saveNBT();

		$nbt->setByte("NoAI", intval($this->isImmobile()));

		return $nbt;
	}

	/**
	 * @param int $diff
	 *
	 * @return bool
	 */
	public function entityBaseTick(int $diff = 1) : bool{
		$hasUpdate = parent::entityBaseTick($diff);

		if(!$this->isImmobile()){
			$this->onBehaviorUpdate();

			if($this->isAlive() and $this->random->nextBoundedInt(1000) < $this->livingSoundTime++){
				$this->livingSoundTime -= $this->getTalkInterval();
				$this->playLivingSound();
			}

			return $this->hasMovementUpdate() or $hasUpdate;
		}

		return $hasUpdate;
	}

	/**
	 * @return null|string
	 */
	public function getLivingSound() : ?string{
		return null;
	}

	public function playLivingSound() : void{
		$sound = $this->getLivingSound();

		if($sound !== null and $this->chunk !== null){
			$pk = new PlaySoundPacket();
			$pk->x = $this->x;
			$pk->y = $this->y;
			$pk->z = $this->z;
			$pk->pitch = $this->isBaby() ? 2 : 1;
			$pk->volume = 1.0;
			$pk->soundName = $sound;

			$this->level->addChunkPacket($this->chunk->getX(), $this->chunk->getZ(), $pk);
		}
	}

	protected function onBehaviorUpdate() : void{
		Timings::$mobBehaviorUpdateTimer->startTiming();
		$this->targetBehaviorPool->onUpdate();
		$this->behaviorPool->onUpdate();
		Timings::$mobBehaviorUpdateTimer->stopTiming();

		Timings::$mobNavigationUpdateTimer->startTiming();
		$this->navigator->onNavigateUpdate();
		Timings::$mobNavigationUpdateTimer->stopTiming();

		if($this->getLookPosition() !== null){
			$this->lookAt($this->getLookPosition(), true);
			$this->lookPosition = null;
		}

		$this->handleWaterMovement();
		$this->clearSightCache();
	}

	/**
	 * @param Entity $target
	 *
	 * @return bool
	 */
	public function canSeeEntity(Entity $target) : bool{
		if(in_array($target->getId(), $this->unseenEntities)){
			return false;
		}elseif(in_array($target->getId(), $this->seenEntities)){
			return true;
		}else{
			// TODO: Fix seen from corners
			$canSee = $this->getNavigator()->isClearBetweenPoints($this, $target);

			if($canSee){
				$this->seenEntities[] = $target->getId();
			}else{
				$this->unseenEntities[] = $target->getId();
			}

			return $canSee;
		}
	}

	public function clearSightCache() : void{
		$this->seenEntities = [];
		$this->unseenEntities = [];
	}

	/**
	 * @return null|Vector3
	 */
	public function getLookPosition() : ?Vector3{
		return $this->lookPosition;
	}

	/**
	 * @param null|Vector3 $pos
	 */
	public function setLookPosition(?Vector3 $pos) : void{
		$this->lookPosition = $pos;
	}

	protected function addBehaviors() : void{

	}

	/**
	 * @return BehaviorPool
	 */
	public function getBehaviorPool() : BehaviorPool{
		return $this->behaviorPool;
	}

	/**
	 * @return BehaviorPool
	 */
	public function getTargetBehaviorPool() : BehaviorPool{
		return $this->targetBehaviorPool;
	}

	/**
	 * @param float $spm
	 *
	 * @return bool
	 */
	public function moveForward(float $spm) : bool{
		if($this->jumpCooldown > 0) $this->jumpCooldown--;

		$sf = $this->getMovementSpeed() * $spm * 0.7;
		$dir = $this->getDirectionVector();
		$dir->y = 0;

		$coord = $this->add($dir->multiply($sf)->add($dir->multiply($this->width * 0.5)));

		$block = $this->level->getBlock($coord);

		if($this->isInsideOfSolid()){
			$block = $this->level->getBlock($this);
		}

		$blockUp = $block->getSide(Facing::UP);
		$blockUpUp = $block->getSide(Facing::UP, 2);

		$bb = $block->getBoundingBox();

		$collide = $block->isSolid() or ($this->height >= 1 and $blockUp->isSolid());

		if($collide){
			if($bb->maxY <= $this->y){
				$collide = false;
			}
		}

		if(!$collide){
			if(!$this->onGround and $this->jumpCooldown === 0 and !$this->isSwimmer()) return true;

			$velocity = $dir->multiply($sf);
			$entityVelocity = $this->getMotion();
			$entityVelocity->y = 0;

			$this->motion = $this->getMotion()->add($velocity->subtract($entityVelocity));
			return true;
		}else{
			if($this->canClimb()){
				$this->motion->y = 0.2;
				$this->jumpCooldown = 20;
				return true;
			}elseif((!$blockUp->isSolid() and !($this->height > 1 and $blockUpUp->isSolid()) or $block->isPassable($this)) or $this->isSwimmer()){
				$f = $this->getJumpVelocity() + 0.02;
				if($bb instanceof AxisAlignedBB){
					$yDiff = $bb->maxY - $bb->minY;
					if($yDiff < 1){
						$f *= $yDiff;

						if($f < 0.1){
							$f = 0.1 + $this->gravity; // :/
						}
					}
				}

				$this->motion->y = $f;
				$this->jumpCooldown = 20;
				return true;
			}else{
				$this->motion->x = $this->motion->z = 0;
			}
		}
		return false;
	}

	/**
	 * @return EntityNavigator
	 */
	public function getNavigator() : EntityNavigator{
		return $this->navigator;
	}

	/**
	 * @return bool
	 */
	public function canBePushed() : bool{
		return !$this->isImmobile();
	}

	/**
	 * @param float $value
	 */
	public function setDefaultMovementSpeed(float $value) : void{
		$this->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED)->setDefaultValue($value);
	}

	/**
	 * @return float
	 */
	public function getDefaultMovementSpeed() : float{
		return $this->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED)->getDefaultValue();
	}

	public function updateLeashedState() : void{
		parent::updateLeashedState();

		if($this->isLeashed() and $this->leashedToEntity !== null){
			$entity = $this->leashedToEntity;
			$f = $this->distance($entity);

			if($this instanceof Tamable and $this->isSitting()){
				if($f > 10){
					$this->clearLeashed(true, true);
				}
				return;
			}

			if($f > 4){
				$this->navigator->tryMoveTo($entity, 1.0);
			}

			if($f > 6){
				$d0 = ($entity->x - $this->x) / $f;
				$d1 = ($entity->y - $this->y) / $f;
				$d2 = ($entity->z - $this->z) / $f;

				$this->motion->x += $d0 * abs($d0) * 0.4;
				$this->motion->y += $d1 * abs($d1) * 0.4;
				$this->motion->z += $d2 * abs($d2) * 0.4;
			}

			if($f > 10){
				$this->clearLeashed(true, true);
			}
		}
	}

	/**
	 * @return bool
	 */
	public function canDespawn() : bool{
		return !$this->isImmobile() and !$this->isLeashed() and $this->getOwningEntityId() === null;
	}

	/**
	 * @param Vector3 $pos
	 *
	 * @return float
	 */
	public function getBlockPathWeight(Vector3 $pos) : float{
		return 0.0;
	}

	/**
	 * @return bool
	 */
	public function canSpawnHere() : bool{
		return parent::canSpawnHere() and $this->getBlockPathWeight($this) > 0;
	}
}