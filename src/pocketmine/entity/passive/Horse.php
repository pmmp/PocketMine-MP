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

use pocketmine\entity\Attribute;
use pocketmine\entity\behavior\FloatBehavior;
use pocketmine\entity\behavior\FollowParentBehavior;
use pocketmine\entity\behavior\MountPathingBehavior;
use pocketmine\entity\behavior\LookAtPlayerBehavior;
use pocketmine\entity\behavior\MateBehavior;
use pocketmine\entity\behavior\PanicBehavior;
use pocketmine\entity\behavior\RandomLookAroundBehavior;
use pocketmine\entity\behavior\TemptBehavior;
use pocketmine\entity\behavior\RandomStrollBehavior;
use pocketmine\inventory\HorseInventory;
use pocketmine\inventory\InventoryHolder;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\Player;

class Horse extends AbstractHorse implements InventoryHolder{

	public const NETWORK_ID = self::HORSE;

	public const HORSE_VARIANT_WHITE = 0;
	public const HORSE_VARIANT_CREAMY = 1;
	public const HORSE_VARIANT_CHESTNUT = 2;
	public const HORSE_VARIANT_BROWN = 3;
	public const HORSE_VARIANT_BLACK = 4;
	public const HORSE_VARIANT_GRAY = 5;
	public const HORSE_VARIANT_DARK_BROWN = 6;

	public const HORSE_MARK_VARIANT_NONE = 0;
	public const HORSE_MARK_VARIANT_WHITE = 1;
	public const HORSE_MARK_VARIANT_WHITE_FIELD = 2;
	public const HORSE_MARK_VARIANT_WHITE_DOTS = 3;
	public const HORSE_MARK_VARIANT_BLACK_DOTS = 4;

	public $width = 1.4;
	public $height = 1.6;

	/** @var HorseInventory */
	protected $inventory;

	public function getName() : string{
		return "Horse";
	}

	/**
	 * @return HorseInventory
	 */
	public function getInventory() : HorseInventory{
		return $this->inventory;
	}

	protected function addBehaviors() : void{
		$this->behaviorPool->setBehavior(0, new MountPathingBehavior($this));
		$this->behaviorPool->setBehavior(1, new FloatBehavior($this));
		$this->behaviorPool->setBehavior(2, new PanicBehavior($this, 1.25));
		$this->behaviorPool->setBehavior(3, new MateBehavior($this, 1.0));
		$this->behaviorPool->setBehavior(4, new TemptBehavior($this, [Item::WHEAT, Item::APPLE, Item::WHEAT_BLOCK, Item::GOLDEN_APPLE, Item::ENCHANTED_GOLDEN_APPLE, Item::GOLDEN_CARROT, Item::SUGAR], 1.2));
		$this->behaviorPool->setBehavior(5, new FollowParentBehavior($this, 1.1));
		$this->behaviorPool->setBehavior(6, new RandomStrollBehavior($this, 1.0));
		$this->behaviorPool->setBehavior(7, new LookAtPlayerBehavior($this, 6.0));
		$this->behaviorPool->setBehavior(8, new RandomLookAroundBehavior($this));
	}

	protected function initEntity() : void{
		$this->setMaxHealth($this->getModifiedMaxHealth());
		$this->setMovementSpeed($this->getModifiedMovementSpeed());
		$this->setJumpStrength($this->getModifiedJumpStrength());
		$this->setFollowRange(35);

		if($this->namedtag->hasTag("Variant", IntTag::class) and $this->namedtag->hasTag("MarkVariant", IntTag::class)){
			$this->setVariant($this->namedtag->getInt("Variant"));
			$this->setMarkVariant($this->namedtag->getInt("MarkVariant"));
		}else{
			$this->setVariant(mt_rand(0, 7));
			$this->setMarkVariant(mt_rand(0, 5));
		}

		$this->inventory = new HorseInventory($this);

		if($this->namedtag->hasTag("ArmorItem", CompoundTag::class)){
			$this->inventory->setArmor(Item::nbtDeserialize($this->namedtag->getCompoundTag("ArmorItem")));
		}

		if($this->namedtag->hasTag("SaddleItem", CompoundTag::class)){
			$this->inventory->setSaddle(Item::nbtDeserialize($this->namedtag->getCompoundTag("SaddleItem")));
		}

		parent::initEntity();
	}

	public function addAttributes() : void{
		parent::addAttributes();

		$this->attributeMap->addAttribute(Attribute::getAttribute(Attribute::HORSE_JUMP_STRENGTH));
	}

	public function getRiderSeatPosition(int $seatNumber = 0) : Vector3{
		return new Vector3(0, 1.1, -0.2);
	}

	public function setSaddled(bool $value = true) : void{
		parent::setSaddled($value);

		$this->setGenericFlag(self::DATA_FLAG_CAN_POWER_JUMP, $value);
	}

	public function onInteract(Player $player, Item $item, Vector3 $clickPos) : bool{
		if(!$this->isImmobile()){
			// TODO: feeding

			if($player->isSneaking()){
				if($this->isTamed()){
					$player->addWindow($this->inventory);
				}else{
					$this->rearUp();
				}

				return true;
			}
		}
		return parent::onInteract($player, $item, $clickPos);
	}

	public function sendSpawnPacket(Player $player) : void{
		parent::sendSpawnPacket($player);

		$this->inventory->sendArmor($player);
	}

	public function doHitAnimation() : void{
		parent::doHitAnimation();

		foreach($this->getViewers() as $player){ // WTF
			$this->inventory->sendArmor($player);
		}
	}

	public function onDeath() : void{
		parent::onDeath();

		$this->inventory->dropContents($this->level, $this);
	}

	public function saveNBT() : void{
		parent::saveNBT();

		if($this->inventory !== null){
			$this->namedtag->setTag($this->inventory->getSaddle()->nbtSerialize(-1, "SaddleItem"));
			$this->namedtag->setTag($this->inventory->getArmor()->nbtSerialize(-1, "ArmorItem"));
		}
	}
}