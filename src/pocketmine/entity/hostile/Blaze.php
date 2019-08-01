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

namespace pocketmine\entity\hostile;

use pocketmine\entity\behavior\FindAttackableTargetBehavior;
use pocketmine\entity\behavior\HurtByTargetBehavior;
use pocketmine\entity\behavior\LookAtPlayerBehavior;
use pocketmine\entity\behavior\RandomLookAroundBehavior;
use pocketmine\entity\behavior\RangedAttackBehavior;
use pocketmine\entity\behavior\RandomStrollBehavior;
use pocketmine\entity\Entity;
use pocketmine\entity\Monster;
use pocketmine\entity\projectile\SmallFireball;
use pocketmine\entity\RangedAttackerMob;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\Player;
use function rand;

class Blaze extends Monster implements RangedAttackerMob{

	public const NETWORK_ID = self::BLAZE;

	public $height = 1.8;
	public $width = 0.6;

	public function initEntity() : void{
		$this->setMaxHealth(20);
		$this->setMovementSpeed(0.23000000417232513);
		$this->setFollowRange(35);

		parent::initEntity();
	}

	public function getName() : string{
		return "Blaze";
	}

	protected function addBehaviors() : void{
		$this->targetBehaviorPool->setBehavior(0, new HurtByTargetBehavior($this));
		$this->targetBehaviorPool->setBehavior(1, new FindAttackableTargetBehavior($this, Player::class));

		$this->behaviorPool->setBehavior(1, new RangedAttackBehavior($this, 1.0, 30, 60, 10));
		$this->behaviorPool->setBehavior(2, new RandomStrollBehavior($this, 1.0));
		$this->behaviorPool->setBehavior(3, new LookAtPlayerBehavior($this, 8.0));
		$this->behaviorPool->setBehavior(4, new RandomLookAroundBehavior($this));
	}

	public function getXpDropAmount() : int{
		return 10;
	}

	public function getDrops() : array{
		return [
			ItemFactory::get(Item::BLAZE_ROD, 0, rand(0, 1)), ItemFactory::get(Item::GLOWSTONE_DUST, 0, rand(0, 2))
		];
	}

	public function onBehaviorUpdate() : void{
		parent::onBehaviorUpdate();

		if($this->isWet()){
			$this->attack(new EntityDamageEvent($this, EntityDamageEvent::CAUSE_DROWNING, 1));
		}

		$target = $this->getTargetEntity();
		if($target !== null and $target->y + $target->getEyeHeight() > $this->y + $this->getEyeHeight()){
			$this->motion->y += (0.30000001192092896 - $this->motion->y) * 0.30000001192092896;
		}
	}

	public function onRangedAttackToTarget(Entity $target, float $power) : void{
		$dv = $target->subtract($this)->normalize();
		$fireball = new SmallFireball($this->level, Entity::createBaseNBT($this->add($this->random->nextFloat() * $power, $this->getEyeHeight(), $this->random->nextFloat() * $power), $dv), $this);
		$fireball->setMotion($dv->multiply($power));
		$fireball->spawnToAll();
	}

	public function canDespawn() : bool{
		return true;
	}

	public function isValidLightLevel() : bool{
		return true;
	}
}
