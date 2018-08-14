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

use pocketmine\entity\Ageable;
use pocketmine\entity\behavior\FindAttackableTargetBehavior;
use pocketmine\entity\behavior\FloatBehavior;
use pocketmine\entity\behavior\JumpAttackBehavior;
use pocketmine\entity\behavior\LookAtPlayerBehavior;
use pocketmine\entity\behavior\MeleeAttackBehavior;
use pocketmine\entity\behavior\RandomLookAroundBehavior;
use pocketmine\entity\behavior\WanderBehavior;
use pocketmine\entity\Monster;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;

class Spider extends Monster implements Ageable{

	public const NETWORK_ID = self::SPIDER;

	public $width = 1.4;
	public $height = 0.9;

	protected function initEntity(CompoundTag $nbt) : void{
		$this->setMaxHealth(16);
		$this->setMovementSpeed(0.30000001192092896);
		$this->setFollowRange(35);
		$this->setAttackDamage(2);
		$this->setCanClimb(true);
		$this->setCanClimbWalls(true);

		parent::initEntity($nbt);
	}

	public function getName() : string{
		return "Spider";
	}

	protected function addBehaviors() : void{
		// TODO: Spider Attack
		$this->behaviorPool->setBehavior(0, new FloatBehavior($this));
		$this->behaviorPool->setBehavior(1, new JumpAttackBehavior($this, 0.4));
		$this->behaviorPool->setBehavior(2, new MeleeAttackBehavior($this, 1.0));
		$this->behaviorPool->setBehavior(3, new WanderBehavior($this, 1.0));
		$this->behaviorPool->setBehavior(4, new LookAtPlayerBehavior($this, 8.0));
		$this->behaviorPool->setBehavior(5, new RandomLookAroundBehavior($this));

		$this->targetBehaviorPool->setBehavior(0, new FindAttackableTargetBehavior($this, Player::class));
	}

	public function getXpDropAmount() : int{
		return 5;
	}

	public function getDrops() : array{
		$drops = [ItemFactory::get(Item::STRING, 0, rand(0, 2))];
		if($this->getLastAttacker() instanceof Player){
			$drops[] = ItemFactory::get(Item::SPIDER_EYE);
		}
		return $drops;
	}
}