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

namespace pocketmine\entity\object;

use pocketmine\entity\Entity;
use pocketmine\entity\EntityIds;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\inventory\AltayEntityEquipment;
use pocketmine\inventory\utils\EquipmentSlot;
use pocketmine\item\Armor;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\Player;

class ArmorStand extends Living{
	public const NETWORK_ID = EntityIds::ARMOR_STAND;

	public const TAG_ARMOR = "Armor";
	public const TAG_MAINHAND = "Mainhand";
	public const TAG_OFFHAND = "Offhand";
	public const TAG_POSE = "Pose";
	public const TAG_LAST_SIGNAL = "LastSignal";
	public const TAG_POSE_INDEX = "PoseIndex";

	/** @var AltayEntityEquipment */
	protected $equipment;

	public $width = 0.5;
	public $height = 1.975;

	protected $gravity = 0.04;

	public function __construct(Level $level, CompoundTag $nbt){
		$air = Item::get(Item::AIR)->nbtSerialize();
		if(!$nbt->hasTag(self::TAG_MAINHAND, ListTag::class)){
			$nbt->setTag(new ListTag(self::TAG_MAINHAND, [
				$air
			], NBT::TAG_Compound));
		}

		if(!$nbt->hasTag(self::TAG_OFFHAND, ListTag::class)){
			$nbt->setTag(new ListTag(self::TAG_OFFHAND, [
				$air
			], NBT::TAG_Compound));
		}

		if(!$nbt->hasTag(self::TAG_ARMOR, ListTag::class)){
			$nbt->setTag(new ListTag(self::TAG_ARMOR, [
				$air, // helmet
				$air, // chestplate
				$air, // legging
				$air  // boots
			], NBT::TAG_Compound));
		}

		if(!$nbt->hasTag(self::TAG_POSE, CompoundTag::class)){
			$nbt->setTag(new CompoundTag(self::TAG_POSE, [
				new IntTag(self::TAG_LAST_SIGNAL, 0),
				new IntTag(self::TAG_POSE_INDEX, 0)
			]));
		}

		parent::__construct($level, $nbt);
	}

	protected function initEntity() : void{
		$this->setMaxHealth(6);
		parent::initEntity();

		$this->equipment = new AltayEntityEquipment($this);

		/** @var ListTag $armor */
		$armor = $this->namedtag->getTag(self::TAG_ARMOR);
		/** @var ListTag $mainhand */
		$mainhand = $this->namedtag->getTag(self::TAG_MAINHAND);
		/** @var ListTag $offhand */
		$offhand = $this->namedtag->getTag(self::TAG_OFFHAND);

		$contents = array_merge(array_map(function(CompoundTag $tag) : Item{ return Item::nbtDeserialize($tag); }, $armor->getAllValues()), [Item::nbtDeserialize($offhand->offsetGet(0))], [Item::nbtDeserialize($mainhand->offsetGet(0))]);
		$this->equipment->setContents($contents);

		/** @var CompoundTag $pose */
		$pose = $this->namedtag->getTag(self::TAG_POSE);
		$pose = $pose->getInt(self::TAG_POSE_INDEX, 0);
		$this->setPose($pose);
	}

	public function getEquipmentSlot(Item $item) : int{
		if($item instanceof Armor){
			return $item->getArmorSlot();
		}else{
			switch($item->getId()){
				case Item::SKULL:
				case Item::SKULL_BLOCK:
				case Item::PUMPKIN:
					return EquipmentSlot::HEAD;
			}

			return -1; // mainhand
		}
	}

	public function setPose(int $pose) : void{
		$this->propertyManager->setInt(self::DATA_ARMOR_STAND_POSE_INDEX, $pose);
	}

	public function getPose() : int{
		return $this->propertyManager->getInt(self::DATA_ARMOR_STAND_POSE_INDEX);
	}

	public function onInteract(Player $player, Item $item, Vector3 $clickPos, int $slot) : void{
		$player->getInventory()->sendContents($player);

		if($player->isSneaking()){
			$pose = $this->getPose();
			if(++$pose >= 13){
				$pose = 0;
			}

			$this->setPose($pose);
		}else{
			$diff = $clickPos->getY() - $this->getY();
			$type = $this->getEquipmentSlot($item);
			$playerInv = $player->getInventory();

			switch(true){ // yes order matter here.
				case ($diff < 0.5):
					$clicked = EquipmentSlot::FEET;
					break;
				case ($diff < 1):
					$clicked = EquipmentSlot::LEGS;
					break;
				case ($diff < 1.5):
					$clicked = EquipmentSlot::CHEST;
					break;
				default: // armor stands are only 2-ish blocks tall :shrug:
					$clicked = EquipmentSlot::HEAD;
					break;
			}

			if($item->isNull()){
				if($clicked == EquipmentSlot::CHEST){
					if($this->equipment->getItemInHand()->isNull()){
						$ASchestplate = clone $this->armorInventory->getChestplate();
						$this->armorInventory->setChestplate($item);
						$playerInv->setItemInHand(Item::get(Item::AIR));
						$playerInv->addItem($ASchestplate);
					}else{
						$ASiteminhand = clone $this->equipment->getItemInHand();
						$this->equipment->setItemInHand($item);
						$playerInv->setItemInHand(Item::get(Item::AIR));
						$playerInv->addItem($ASiteminhand);
					}
				}else{
					$old = clone $this->armorInventory->getItem($clicked);
					$this->armorInventory->setItem($clicked, $item);
					$playerInv->setItemInHand(Item::get(Item::AIR));
					$playerInv->addItem($old);
				}
			}else{
				if($type == -1){
					if($this->equipment->getItemInHand()->equals($item)){
						$playerInv->addItem(clone $this->equipment->getItemInHand());
						$this->equipment->setItemInHand(Item::get(Item::AIR));
					}else{
						$playerInv->addItem(clone $this->equipment->getItemInHand());

						$ic = clone $item;
						$this->equipment->setItemInHand($ic->pop());
						$playerInv->setItemInHand($ic);
					}
				}else{
					$old = clone $this->armorInventory->getItem($type);
					$this->armorInventory->setItem($type, $item);
					$playerInv->setItemInHand(Item::get(Item::AIR));
					$playerInv->addItem($old);
				}
			}

			$this->equipment->sendContents($this->getViewers());
		}
	}

	protected function applyGravity() : void{
		parent::applyGravity();
		$this->level->broadcastLevelEvent($this, LevelEventPacket::EVENT_SOUND_ARMOR_STAND_FALL);
	}

	public function saveNBT() : void{
		parent::saveNBT();

		$this->namedtag->setTag(new ListTag(self::TAG_MAINHAND, [$this->equipment->getItemInHand()->nbtSerialize()], NBT::TAG_Compound));
		$this->namedtag->setTag(new ListTag(self::TAG_OFFHAND, [$this->equipment->getOffhandItem()->nbtSerialize()], NBT::TAG_Compound));

		$armorNBT = array_map(function(Item $item) : CompoundTag{ return $item->nbtSerialize(); }, $this->getArmorInventory()->getContents());
		$this->namedtag->setTag(new ListTag(self::TAG_ARMOR, $armorNBT, NBT::TAG_Compound));

		/** @var CompoundTag $poseTag */
		$poseTag = $this->namedtag->getTag(self::TAG_POSE);
		$poseTag->setInt(self::TAG_POSE_INDEX, $this->getPose());
		$this->namedtag->setTag($poseTag);
	}

	public function getDrops() : array{
		return array_merge($this->equipment->getContents(), $this->armorInventory->getContents(), [ItemFactory::get(Item::ARMOR_STAND)]);
	}

	public function attack(EntityDamageEvent $source) : void{
		if($source instanceof EntityDamageByEntityEvent){
			$damager = $source->getDamager();
			if($damager instanceof Player){
				if($damager->isCreative()){
					$this->level->broadcastLevelEvent($this, LevelEventPacket::EVENT_SOUND_ARMOR_STAND_BREAK);
					$this->level->broadcastLevelEvent($this, LevelEventPacket::EVENT_PARTICLE_DESTROY, 5);
					$this->flagForDespawn();
				}else{
					$this->level->broadcastLevelEvent($this, LevelEventPacket::EVENT_SOUND_ARMOR_STAND_HIT);
				}
			}
		}
		if($source->getCause() != EntityDamageEvent::CAUSE_CONTACT){ // cactus
			Entity::attack($source);
		}
	}

	protected function sendSpawnPacket(Player $player): void{
		parent::sendSpawnPacket($player);

		$this->equipment->sendContents($player);
	}

	public function getName(): string{
		return "Armor Stand";
	}

	public function hasEntityColissionUpdate() : bool{
		return false;
	}
}