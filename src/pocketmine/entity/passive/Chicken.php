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

namespace pocketmine\entity\passive;

use pocketmine\entity\Animal;
use pocketmine\entity\behavior\FloatBehavior;
use pocketmine\entity\behavior\FollowParentBehavior;
use pocketmine\entity\behavior\LookAtPlayerBehavior;
use pocketmine\entity\behavior\MateBehavior;
use pocketmine\entity\behavior\PanicBehavior;
use pocketmine\entity\behavior\RandomLookAroundBehavior;
use pocketmine\entity\behavior\TemptedBehavior;
use pocketmine\entity\behavior\WanderBehavior;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;

class Chicken extends Animal{

	public const NETWORK_ID = self::CHICKEN;

	public $width = 0.4;
	public $height = 0.7;

	protected $chickenJockey = false;
	protected $timeUntilNextEgg = 0;

	/**
	 * @return bool
	 */
	public function isChickenJockey() : bool{
		return $this->chickenJockey;
	}

	/**
	 * @param bool $chickenJockey
	 */
	public function setChickenJockey(bool $chickenJockey) : void{
		$this->chickenJockey = $chickenJockey;
	}

	protected function addBehaviors() : void{
		$this->behaviorPool->setBehavior(0, new FloatBehavior($this));
		$this->behaviorPool->setBehavior(1, new PanicBehavior($this, 1.4));
		$this->behaviorPool->setBehavior(2, new MateBehavior($this, 1.0));
		$this->behaviorPool->setBehavior(3, new TemptedBehavior($this, [Item::WHEAT_SEEDS], 1.0));
		$this->behaviorPool->setBehavior(4, new FollowParentBehavior($this, 1.1));
		$this->behaviorPool->setBehavior(5, new WanderBehavior($this, 1.0));
		$this->behaviorPool->setBehavior(6, new LookAtPlayerBehavior($this, 6.0));
		$this->behaviorPool->setBehavior(7, new RandomLookAroundBehavior($this));
	}

	protected function initEntity(CompoundTag $nbt) : void{
		$this->setMaxHealth(4);
		$this->setMovementSpeed(0.25);
		$this->setFollowRange(10);

		$this->setChickenJockey(boolval($nbt->getByte("isChickenJockey", 0)));
		$this->timeUntilNextEgg = $this->level->random->nextBoundedInt(6000) + 6000;

		parent::initEntity($nbt);
	}

	public function getName() : string{
		return "Chicken";
	}

	public function getXpDropAmount() : int{
		return rand(1, 3);
	}

	public function getDrops() : array{
		return [
			($this->isOnFire() ? ItemFactory::get(Item::COOKED_CHICKEN, 0, 1) : ItemFactory::get(Item::RAW_CHICKEN, 0, 1)),
			ItemFactory::get(Item::FEATHER, 0, rand(0, 2))
		];
	}

	public function saveNBT() : CompoundTag{
		$nbt = parent::saveNBT();

		$nbt->setByte("isChickenJockey", intval($this->isChickenJockey()));

		return $nbt;
	}

	public function getRiderSeatPosition(int $seatNumber = 0) : Vector3{
		return new Vector3(0, 1, 0);
	}

	public function entityBaseTick(int $diff = 1) : bool{
		if(!$this->onGround and $this->motion->y < 0){
			$this->motion->y *= 0.6;
		}

		if($this->aiEnabled and !$this->isBaby() and !$this->isChickenJockey() and $this->timeUntilNextEgg-- <= 0){
			$this->level->dropItem($this, ItemFactory::get(Item::EGG));
			$this->level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_LAY_EGG);
			$this->timeUntilNextEgg = $this->level->random->nextBoundedInt(6000) + 6000;
		}
		return parent::entityBaseTick($diff);
	}

	public function fall(float $fallDistance) : void{
		// chickens do not get damage when fall
	}
}