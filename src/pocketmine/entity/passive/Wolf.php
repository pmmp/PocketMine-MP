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

use pocketmine\entity\behavior\FloatBehavior;
use pocketmine\entity\behavior\FollowOwnerBehavior;
use pocketmine\entity\behavior\HurtByTargetBehavior;
use pocketmine\entity\behavior\LeapAtTargetBehavior;
use pocketmine\entity\behavior\LookAtPlayerBehavior;
use pocketmine\entity\behavior\MeleeAttackBehavior;
use pocketmine\entity\behavior\NearestAttackableTargetBehavior;
use pocketmine\entity\behavior\OwnerHurtByTargetBehavior;
use pocketmine\entity\behavior\OwnerHurtTargetBehavior;
use pocketmine\entity\behavior\RandomLookAroundBehavior;
use pocketmine\entity\behavior\StayWhileSittingBehavior;
use pocketmine\entity\behavior\RandomStrollBehavior;
use pocketmine\entity\Entity;
use pocketmine\entity\hostile\Skeleton;
use pocketmine\entity\Tamable;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\ActorEventPacket;
use pocketmine\Player;
use function mt_rand;

class Wolf extends Tamable{
	public const NETWORK_ID = self::WOLF;

	public $width = 0.6;
	public $height = 0.8;
	/** @var StayWhileSittingBehavior */
	protected $behaviorSitting;

	protected function addBehaviors() : void{
		$this->behaviorPool->setBehavior(0, new FloatBehavior($this));
		$this->behaviorPool->setBehavior(1, $this->behaviorSitting = new StayWhileSittingBehavior($this));
		$this->behaviorPool->setBehavior(2, new LeapAtTargetBehavior($this, 0.4));
		$this->behaviorPool->setBehavior(3, new MeleeAttackBehavior($this, 1.0));
		$this->behaviorPool->setBehavior(4, new FollowOwnerBehavior($this, 1, 10 ,2));
		$this->behaviorPool->setBehavior(5, new RandomStrollBehavior($this, 1.0));
		$this->behaviorPool->setBehavior(6, new LookAtPlayerBehavior($this, 8.0));
		$this->behaviorPool->setBehavior(7, new RandomLookAroundBehavior($this));

		$this->targetBehaviorPool->setBehavior(0, new HurtByTargetBehavior($this, true));
		$this->targetBehaviorPool->setBehavior(1, new OwnerHurtByTargetBehavior($this));
		$this->targetBehaviorPool->setBehavior(2, new OwnerHurtTargetBehavior($this));
		$this->targetBehaviorPool->setBehavior(3, new NearestAttackableTargetBehavior($this, Skeleton::class, false));
	}

	protected function initEntity() : void{
		$this->setMaxHealth(8);
		$this->setMovementSpeed(0.3);
		$this->setAttackDamage(3);
		$this->setFollowRange(16);

		$this->propertyManager->setInt(self::DATA_COLOR, 14); // collar color
		$this->setTamed(false);

		parent::initEntity();
	}

	public function getName() : string{
		return "Wolf";
	}

	public function onInteract(Player $player, Item $item, Vector3 $clickPos) : bool{
		if(!$this->isImmobile()){
			if($this->isTamed()){
				if($this->getOwningEntityId() == $player->id){
					$this->setSittingFromBehavior(!$this->isSitting());
					$this->setTargetEntity(null);
				}
			}else{
				if($item->getId() == Item::BONE){
					if($player->isSurvival()){
						$item->pop();
					}

					if(mt_rand(0, 2) == 0){
						$this->setOwningEntity($player);
						$this->setTamed();
						$this->setSittingFromBehavior(true);
						$this->setAngry(false);

						$this->broadcastEntityEvent(ActorEventPacket::TAME_SUCCESS);
					}else{
						$this->broadcastEntityEvent(ActorEventPacket::TAME_FAIL);
					}

					return true;
				}
			}
		}

		return parent::onInteract($player, $item, $clickPos);
	}

	public function setSittingFromBehavior(bool $value) : void{
		$this->behaviorSitting->setSitting($value);
	}

	public function setTargetEntity(?Entity $target) : void{
		parent::setTargetEntity($target);
		if($target == null){
			$this->setAngry(false);
		}elseif(!$this->isTamed()){
			$this->setAngry();
		}
	}

	public function isAngry() : bool{
		return $this->getGenericFlag(self::DATA_FLAG_ANGRY);
	}

	public function setAngry(bool $angry = true) : void{
		$this->setGenericFlag(self::DATA_FLAG_ANGRY, $angry);
	}

	public function setTamed(bool $tamed = true) : void{
		parent::setTamed($tamed);

		if($tamed){
			$this->setMaxHealth(20);
		}else{
			$this->setMaxHealth(8);
		}

		$this->setAttackDamage(4);
	}
}