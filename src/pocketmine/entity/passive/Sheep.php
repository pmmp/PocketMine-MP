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

use pocketmine\block\utils\Color;
use pocketmine\entity\Animal;
use pocketmine\entity\behavior\EatBlockBehavior;
use pocketmine\entity\behavior\FloatBehavior;
use pocketmine\entity\behavior\FollowParentBehavior;
use pocketmine\entity\behavior\LookAtPlayerBehavior;
use pocketmine\entity\behavior\MateBehavior;
use pocketmine\entity\behavior\PanicBehavior;
use pocketmine\entity\behavior\RandomLookAroundBehavior;
use pocketmine\entity\behavior\TemptedBehavior;
use pocketmine\entity\behavior\WanderBehavior;
use pocketmine\item\Dye;
use pocketmine\item\Shears;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;
use pocketmine\utils\Random;

class Sheep extends Animal{

	public const NETWORK_ID = self::SHEEP;

	public $width = 0.9;
	public $height = 1.3;

	protected function addBehaviors() : void{
		$this->behaviorPool->setBehavior(0, new FloatBehavior($this));
		$this->behaviorPool->setBehavior(1, new PanicBehavior($this, 1.25));
		$this->behaviorPool->setBehavior(2, new MateBehavior($this, 1.0));
		$this->behaviorPool->setBehavior(3, new TemptedBehavior($this, [Item::WHEAT], 1.1));
		$this->behaviorPool->setBehavior(4, new FollowParentBehavior($this, 1.1));
		$this->behaviorPool->setBehavior(5, new EatBlockBehavior($this));
		$this->behaviorPool->setBehavior(6, new WanderBehavior($this, 1.0));
		$this->behaviorPool->setBehavior(7, new LookAtPlayerBehavior($this, 6.0));
		$this->behaviorPool->setBehavior(8, new RandomLookAroundBehavior($this));
	}

	protected function initEntity(CompoundTag $nbt) : void{
		$this->setMaxHealth(8);
		$this->setMovementSpeed(0.23000000417232513);
		$this->setFollowRange(10);
		$this->propertyManager->setByte(self::DATA_COLOR, $nbt->getByte("Color", $this->getRandomColor($this->level->random)));
		$this->setSheared(boolval($nbt->getByte("Sheared", 0)));

		parent::initEntity($nbt);
	}

	public function getName() : string{
		return "Sheep";
	}

	public function onInteract(Player $player, Item $item, Vector3 $clickPos, int $slot) : bool{
		if(!$this->isImmobile()){
			if($item instanceof Shears and !$this->isSheared()){
				$this->setSheared(true);
				$item->applyDamage(1);

				$i = 1 + $this->level->random->nextBoundedInt(3);
				for($a = 0; $a < $i; $a++){
					$this->level->dropItem($this, ItemFactory::get(Item::WOOL, intval($this->propertyManager->getByte(self::DATA_COLOR)), 1));

					$this->motion->y += $this->level->random->nextFloat() * 0.05;
					$this->motion->x += ($this->level->random->nextFloat() - $this->level->random->nextFloat()) * 0.1;
					$this->motion->z += ($this->level->random->nextFloat() - $this->level->random->nextFloat()) * 0.1;
				}

				return true;
			}

			if($item instanceof Dye){
				if($player->isSurvival()){
					$item->pop();
				}

				$this->propertyManager->setByte(self::DATA_COLOR, $item->getDamage());
				return true;
			}
		}
		return parent::onInteract($player, $item, $clickPos, $slot);
	}

	public function getXpDropAmount() : int{
		return rand(1, ($this->isInLove() ? 7 : 3));
	}

	public function getDrops() : array{
		return [
			ItemFactory::get(Item::WOOL, intval($this->propertyManager->getByte(self::DATA_COLOR)), $this->isSheared() ? 0 : 1),
			($this->isOnFire() ? ItemFactory::get(Item::COOKED_MUTTON, 0, rand(1, 3)) : ItemFactory::get(Item::RAW_MUTTON, 0, rand(1, 3)))
		];
	}

	public function isSheared() : bool{
		return $this->getGenericFlag(self::DATA_FLAG_SHEARED);
	}

	public function setSheared(bool $value = true) : void{
		$this->setGenericFlag(self::DATA_FLAG_SHEARED, $value);
	}

	public function saveNBT() : CompoundTag{
		$nbt = parent::saveNBT();

		$nbt->setByte("Sheared", intval($this->isSheared()));
		$nbt->setByte("Color", intval($this->propertyManager->getByte(self::DATA_COLOR)));

		return $nbt;
	}

	public function getLivingSound() : ?string{
		return "mob.sheep.say";
	}

	/**
	 * @param Random $random
	 *
	 * @return int
	 */
	public function getRandomColor(Random $random) : int{
		$i = $random->nextBoundedInt(100);

		if($i < 5){
			return Color::BLACK;
		}elseif($i < 10){
			return Color::GRAY;
		}elseif($i < 15){
			return Color::LIGHT_GRAY;
		}elseif($i < 18){
			return Color::BROWN;
		}elseif($random->nextBoundedInt(500) === 0){
			return Color::PINK;
		}else{
			return Color::WHITE;
		}
	}

	/**
	 * @param Vector3 $pos
	 */
	public function eatGrassBonus(Vector3 $pos) : void{
		if(!$this->isBaby()){
			if($this->isSheared()){
				$this->setSheared(false);
			}
		}else{
			// TODO: enlarge baby
		}
	}
}