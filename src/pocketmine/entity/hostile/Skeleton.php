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
use pocketmine\entity\behavior\FloatBehavior;
use pocketmine\entity\behavior\LookAtPlayerBehavior;
use pocketmine\entity\behavior\RandomLookAroundBehavior;
use pocketmine\entity\behavior\RangedAttackBehavior;
use pocketmine\entity\behavior\RestrictSunBehavior;
use pocketmine\entity\behavior\WanderBehavior;
use pocketmine\entity\Entity;
use pocketmine\entity\Monster;
use pocketmine\entity\projectile\Arrow;
use pocketmine\entity\RangedAttackerMob;
use pocketmine\inventory\AltayEntityEquipment;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;

class Skeleton extends Monster implements RangedAttackerMob{

	public const NETWORK_ID = self::SKELETON;

	public $width = 0.6;
	public $height = 1.99;

	/** @var AltayEntityEquipment */
	protected $equipment;

	protected function initEntity(CompoundTag $nbt) : void{
		$this->setMovementSpeed(0.25);
		$this->setFollowRange(35);
		$this->setAttackDamage(2);

		parent::initEntity($nbt);

		$this->equipment = new AltayEntityEquipment($this);
		$loot = $this->level->random->nextBoundedInt(100);
		$bow = ItemFactory::get(Item::BOW);
		if($loot <= 89){
			$this->equipment->setItemInHand($bow);
		}else{
			$this->equipment->setOffhandItem($bow);
		}

		// TODO: Armors
	}

	public function getName() : string{
		return "Skeleton";
	}

	public function getDrops() : array{
		return [
			ItemFactory::get(Item::BONE, 0, rand(0,2)),
			ItemFactory::get(Item::ARROW, 0, rand(0,2))
		];
	}

	public function getXpDropAmount() : int{
		return 5;
	}

	protected function addBehaviors() : void{
		$this->behaviorPool->setBehavior(0, new FloatBehavior($this));
		$this->behaviorPool->setBehavior(1, new RestrictSunBehavior($this));
		$this->behaviorPool->setBehavior(2, new WanderBehavior($this, 1.0));
		$this->behaviorPool->setBehavior(2, new RangedAttackBehavior($this, 1.0, 20, 60, 15.0));
		$this->behaviorPool->setBehavior(3, new LookAtPlayerBehavior($this, 8.0));
		$this->behaviorPool->setBehavior(4, new RandomLookAroundBehavior($this));

		$this->targetBehaviorPool->setBehavior(0, new FindAttackableTargetBehavior($this, Player::class));
		//$this->targetBehaviorPool->setBehavior(2, new FindAttackableTargetBehavior($this, IronGolem::class));
	}

	public function onRangedAttackToTarget(Entity $target, float $power) : void{
		$dir = $this->getDirectionVector();
		/** @var Arrow $arrow */
		$arrow = Entity::createEntity("Arrow", $this->level, Entity::createBaseNBT($this->add($dir->add(0, $this->getEyeHeight(), 0))));
		// TODO: Enchants
		$arrow->setMotion($dir->multiply($power * 2.5)->add($this->level->random->nextFloat() * 0.02, $this->level->random->nextFloat() * 0.01 , $this->level->random->nextFloat() * 0.02));
		$arrow->setBaseDamage(2);

		$this->level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_BOW);
		$arrow->spawnToAll();
	}

	public function entityBaseTick(int $diff = 1) : bool{
		if(!$this->isOnFire() and $this->level->isDayTime() and !$this->isImmobile()){
			if(!$this->isUnderwater() and $this->level->canSeeSky($this)){
				$this->setOnFire(5);
			}
		}
		return parent::entityBaseTick($diff);
	}

	public function sendSpawnPacket(Player $player) : void{
		parent::sendSpawnPacket($player);

		$this->equipment->sendContents([$player]);
	}

	public function getLivingSound() : ?string{
		return "mob.skeleton.say";
	}
}