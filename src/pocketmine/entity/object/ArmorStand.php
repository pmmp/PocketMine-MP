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

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityIds;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\inventory\AltayEntityEquipment;
use pocketmine\inventory\ArmorInventory;
use pocketmine\item\Armor;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\Player;
use function array_merge;
use function min;

class ArmorStand extends Living{

	public const NETWORK_ID = EntityIds::ARMOR_STAND;

	public const TAG_MAINHAND = "Mainhand";
	public const TAG_OFFHAND = "Offhand";
	public const TAG_POSE_INDEX = "PoseIndex";
	public const TAG_ARMOR = "Armor";

	/** @var AltayEntityEquipment */
	protected $equipment;

	public $width = 0.5;
	public $height = 1.975;

	protected $gravity = 0.04;

	protected $vibrateTimer = 0;

	/**
	 * @return AltayEntityEquipment
	 */
	public function getEquipment() : AltayEntityEquipment{
		return $this->equipment;
	}

	protected function initEntity() : void{
		$this->setMaxHealth(6);
		$this->setImmobile(true);

		parent::initEntity();

		$this->equipment = new AltayEntityEquipment($this);

		if($this->namedtag->hasTag(self::TAG_ARMOR, ListTag::class)){
			$armors = $this->namedtag->getListTag(self::TAG_ARMOR);

			/** @var CompoundTag $armor */
			foreach($armors as $armor){
				$slot = $armor->getByte("Slot", 0);

				$this->armorInventory->setItem($slot, Item::nbtDeserialize($armor));
			}
		}

		if($this->namedtag->hasTag(self::TAG_MAINHAND, CompoundTag::class)){
			$this->equipment->setItemInHand(Item::nbtDeserialize($this->namedtag->getCompoundTag(self::TAG_MAINHAND)));
		}
		if($this->namedtag->hasTag(self::TAG_OFFHAND, CompoundTag::class)){
			$this->equipment->setItemInHand(Item::nbtDeserialize($this->namedtag->getCompoundTag(self::TAG_OFFHAND)));
		}

		$this->setPose(min($this->namedtag->getInt(self::TAG_POSE_INDEX, 0), 12));
		$this->propertyManager->setString(self::DATA_INTERACTIVE_TAG, "armorstand.change.pose");
	}

	public function setPose(int $pose) : void{
		$this->propertyManager->setInt(self::DATA_ARMOR_STAND_POSE_INDEX, $pose);
	}

	public function getPose() : int{
		return $this->propertyManager->getInt(self::DATA_ARMOR_STAND_POSE_INDEX);
	}

	public function onFirstInteract(Player $player, Item $itemstack, Vector3 $clickPos) : bool{
		if($player->isSneaking()){
			if($this->getPose() >= 12){
				$this->setPose(0);
			}else{
				$this->setPose($this->getPose() + 1);
			}
			return true;
		}
		if($this->isValid() and !$player->isSpectator()){
			$i = 0;
			$flag = !$itemstack->isNull();

			$isArmorSlot = false;

			if($flag and $itemstack instanceof Armor){
				$i = $itemstack->getArmorSlot();
				$isArmorSlot = true;
			}

			if($flag and ($itemstack->getId() === Item::SKULL or $itemstack->getId() === Item::PUMPKIN)){
				$i = 0;
			}
			$j = 0;
			$d3 = $clickPos->y - $this->y;
			$flag2 = false;

			if($d3 >= 0.1 and $d3 < 0.55 and !$this->armorInventory->getItem(ArmorInventory::SLOT_FEET)->isNull()){
				$j = 3;
				$flag2 = $isArmorSlot = true;
			}elseif($d3 >= 0.9 and $d3 < 1.6 and !$this->armorInventory->getItem(ArmorInventory::SLOT_CHEST)->isNull()){
				$j = 1;
				$flag2 = $isArmorSlot = true;
			}elseif($d3 >= 0.4 and $d3 < 1.2 and !$this->armorInventory->getItem(ArmorInventory::SLOT_LEGS)->isNull()){
				$j = 2;
				$flag2 = $isArmorSlot = true;
			}elseif($d3 >= 1.6 and !$this->armorInventory->getItem(ArmorInventory::SLOT_HEAD)->isNull()){
				$j = 0;
				$flag2 = $isArmorSlot = true;
			}elseif(!$this->equipment->getItem($j)->isNull()){
				$flag2 = true;
			}

			if($flag){
				$this->tryChangeEquipment($player, $itemstack, $i, $isArmorSlot);
			}elseif($flag2){
				$this->tryChangeEquipment($player, $itemstack, $j, $isArmorSlot);
			}

			$this->equipment->sendContents($player);
			$this->armorInventory->sendContents($player);

			return $flag or $flag2;
		}
		return false;
	}

	protected function tryChangeEquipment(Player $player, Item $handItem, int $slot, bool $isArmorSlot = false) : void{
		$itemstack = $isArmorSlot ? $this->armorInventory->getItem($slot) : $this->equipment->getItem($slot);

		if($player->isCreative() and $itemstack->isNull() and !$handItem->isNull()){
			$itemstack3 = clone $handItem;
			$itemstack3->setCount(1);
			if($isArmorSlot){
				$this->armorInventory->setItem($slot, $itemstack3);
			}else{
				$this->equipment->setItem($slot, $itemstack3);
			}
		}elseif(!$handItem->isNull() and $handItem->getCount() > 1){
			if($itemstack->isNull()){
				$itemstack2 = clone $handItem;
				$itemstack2->setCount(1);
				if($isArmorSlot){
					$this->armorInventory->setItem($slot, $itemstack2);
				}else{
					$this->equipment->setItem($slot, $itemstack2);
				}
				$handItem->pop();
			}
		}else{
			if($isArmorSlot){
				$this->armorInventory->setItem($slot, $handItem);
			}else{
				$this->equipment->setItem($slot, $handItem);
			}
			$player->getInventory()->clear($player->getInventory()->getHeldItemIndex());
			$player->getInventory()->addItem($itemstack);
		}
	}

	public function fall(float $fallDistance) : void{
		parent::fall($fallDistance);

		$this->level->broadcastLevelEvent($this, LevelEventPacket::EVENT_SOUND_ARMOR_STAND_FALL, $this->getId());
	}

	public function saveNBT() : void{
		parent::saveNBT();

		if($this->equipment instanceof AltayEntityEquipment){
			$this->namedtag->setTag($this->equipment->getItemInHand()->nbtSerialize(-1, self::TAG_MAINHAND), true);
			$this->namedtag->setTag($this->equipment->getOffhandItem()->nbtSerialize(-1, self::TAG_OFFHAND), true);
		}

		if($this->armorInventory !== null){
			$armorTag = new ListTag(self::TAG_ARMOR, [], NBT::TAG_Compound);

			for($i = 0; $i < 4; $i++){
				$armorTag->push($this->armorInventory->getItem($i)->nbtSerialize($i));
			}

			$this->namedtag->setTag($armorTag, true);
		}

		$this->namedtag->setInt(self::TAG_POSE_INDEX, $this->getPose(), true);
	}

	public function getDrops() : array{
		return array_merge($this->equipment->getContents(), $this->armorInventory->getContents(), [ItemFactory::get(Item::ARMOR_STAND)]);
	}

	public function attack(EntityDamageEvent $source) : void{
		if($source instanceof EntityDamageByEntityEvent){
			$damager = $source->getDamager();
			if($damager instanceof Player){
				if($damager->isCreative()){
					$this->kill();
				}
			}
		}
		if($source->getCause() === EntityDamageEvent::CAUSE_CONTACT){ // cactus
			$source->setCancelled(true);
		}

		Entity::attack($source);

		if(!$source->isCancelled()){
			$this->setGenericFlag(self::DATA_FLAG_VIBRATING, true);
			$this->vibrateTimer = 20;
		}
	}

	public function startDeathAnimation() : void{
		$this->level->addParticle(new DestroyBlockParticle($this, BlockFactory::get(Block::WOODEN_PLANKS)));
	}

	protected function onDeathUpdate(int $tickDiff) : bool{
		return true;
	}

	protected function sendSpawnPacket(Player $player) : void{
		parent::sendSpawnPacket($player);

		$this->equipment->sendContents($player);
	}

	public function getName() : string{
		return "ArmorStand";
	}

	public function canBePushed() : bool{
		return false;
	}

	public function entityBaseTick(int $tickDiff = 1) : bool{
		$hasUpdate = parent::entityBaseTick($tickDiff);

		if($this->getGenericFlag(self::DATA_FLAG_VIBRATING) and $this->vibrateTimer-- <= 0){
			$this->setGenericFlag(self::DATA_FLAG_VIBRATING, false);
		}

		return $hasUpdate;
	}
}