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
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\math\VoxelRayTrace;
use pocketmine\nbt\tag\CompoundTag;

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

	/** @var bool */
	protected $aiEnabled = false;

	public function __construct(Level $level, CompoundTag $nbt){
		parent::__construct($level, $nbt);
		$this->setImmobile(true);
	}

	/**
	 * @return bool
	 */
	public function isAiEnabled() : bool{
		return $this->aiEnabled;
	}

	/**
	 * @param bool $aiEnabled
	 */
	public function setAiEnabled(bool $aiEnabled) : void{
		$this->aiEnabled = $aiEnabled;
	}

	/**
	 * @return Vector3
	 */
	public function getHomePosition() : Vector3{
		return $this->homePosition;
	}

	/**
	 * @param Vector3 $homePosition
	 */
	public function setHomePosition(Vector3 $homePosition) : void{
		$this->homePosition = $homePosition;
	}

	protected function initEntity(CompoundTag $nbt) : void{
		parent::initEntity($nbt);

		$this->targetBehaviorPool = new BehaviorPool();
		$this->behaviorPool = new BehaviorPool();
		$this->navigator = new EntityNavigator($this);

		$this->addBehaviors();

		$this->aiEnabled = boolval($nbt->getByte("aiEnabled", 0));
	}

	public function saveNBT() : CompoundTag{
		$nbt = parent::saveNBT();

		$nbt->setByte("aiEnabled", intval($this->aiEnabled));
	}

	public function entityBaseTick(int $diff = 1) : bool{
		$hasUpdate = parent::entityBaseTick($diff);

		if($this->aiEnabled){
			$this->onBehaviorUpdate();
		}

		return $hasUpdate;
	}

	protected function onBehaviorUpdate() : void{
		$this->targetBehaviorPool->onUpdate();
		$this->behaviorPool->onUpdate();

		$this->navigator->onNavigateUpdate();

		if($this->getLookPosition() !== null){
			$this->lookAt($this->getLookPosition(), true);
			$this->lookPosition = null;
		}

		$this->handleWaterMovement();

		$this->clearSightCache();
	}

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

	public function getLookPosition() : ?Vector3{
		return $this->lookPosition;
	}

	public function setLookPosition(?Vector3 $pos) : void{
		$this->lookPosition = $pos;
	}

	protected function addBehaviors() : void{

	}

	public function getBehaviorPool() : BehaviorPool{
		return $this->behaviorPool;
	}

	public function getTargetBehaviorPool() : BehaviorPool{
		return $this->targetBehaviorPool;
	}

	public function moveForward(float $spm) : bool{
		if($this->jumpCooldown > 0) $this->jumpCooldown--;

		$sf = $this->getMovementSpeed() * $spm * 0.7;
		$dir = $this->getDirectionVector();
		$dir->y = 0;

		$coord = $this->add($dir->multiply($sf)->add($dir->multiply($this->width * 0.5)));

		$block = $this->level->getBlock($coord);
		$blockUp = $block->getSide(Vector3::SIDE_UP);
		$blockUpUp = $block->getSide(Vector3::SIDE_UP, 2);

		$collide = $block->isSolid() || ($this->height >= 1 and $blockUp->isSolid());

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
			}elseif((!$blockUp->isSolid() and !($this->height > 1 and $blockUpUp->isSolid())) or $this->isSwimmer()){
				$this->motion->y = $this->getJumpVelocity();
				$this->jumpCooldown = 20;
				return true;
			}else{
				$this->motion->x = $this->motion->z = 0;
			}
		}
		return false;
	}

	public function getNavigator() : EntityNavigator{
		return $this->navigator;
	}

	public function canBePushed() : bool{
		return $this->aiEnabled;
	}

	public function setDefaultMovementSpeed(float $value) : void{
		$this->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED)->setDefaultValue($value);
	}
}