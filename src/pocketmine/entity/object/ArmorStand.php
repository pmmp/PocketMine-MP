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
use pocketmine\inventory\utils\EquipmentSlot;
use pocketmine\item\Armor;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\level\particle\ItemBreakParticle;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\Player;

class ArmorStand extends Living{

	public const NETWORK_ID = EntityIds::ARMOR_STAND;

	public const TAG_MAINHAND = "Mainhand";
	public const TAG_OFFHAND = "Offhand";
	public const TAG_POSE_INDEX = "PoseIndex";

	/** @var AltayEntityEquipment */
	protected $equipment;

	public $width = 0.5;
	public $height = 1.975;

	protected $gravity = 0.04;

	protected $vibrateTimer = 0;

	protected function initEntity(CompoundTag $nbt) : void{
		$this->setMaxHealth(6);

		parent::initEntity($nbt);

		$this->equipment = new AltayEntityEquipment($this);

		if($nbt->hasTag("armorInv", ListTag::class)){
			$armors = $nbt->getListTag("armorInv");

			/** @var CompoundTag $armor */
			foreach($armors as $armor){
				$slot = $armor->getByte("Slot", 0);

				$this->armorInventory->setItem($slot, Item::nbtDeserialize($armor));
			}
		}

		if($nbt->hasTag(self::TAG_MAINHAND, CompoundTag::class)){
			$this->equipment->setItemInHand(Item::nbtDeserialize($nbt->getCompoundTag(self::TAG_MAINHAND)));
		}
		if($nbt->hasTag(self::TAG_OFFHAND, CompoundTag::class)){
			$this->equipment->setItemInHand(Item::nbtDeserialize($nbt->getCompoundTag(self::TAG_OFFHAND)));
		}

		$this->setPose($nbt->getInt(self::TAG_POSE_INDEX, 0));
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

			return -1;
		}
	}

	public function setPose(int $pose) : void{
		$this->propertyManager->setInt(self::DATA_ARMOR_STAND_POSE_INDEX, $pose);
	}

	public function getPose() : int{
		return $this->propertyManager->getInt(self::DATA_ARMOR_STAND_POSE_INDEX);
	}

	public function onInteract(Player $player, Item $item, Vector3 $clickPos, int $slot) : void{
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
						$playerInv->setItemInHand($this->armorInventory->getChestplate());
						$this->armorInventory->setChestplate($item);
					}else{
						$playerInv->setItemInHand($this->equipment->getItemInHand());
						$this->equipment->setItemInHand($item);
					}
				}else{
					$playerInv->setItemInHand($this->armorInventory->getItem($clicked));
					$this->armorInventory->setItem($clicked, $item);
				}
			}else{
				if($type == -1){
					if($this->equipment->getItemInHand()->equals($item)){
						$playerInv->addItem($this->equipment->getItemInHand());
						$this->equipment->clear(EquipmentSlot::MAINHAND);
					}else{
						$playerInv->setItemInHand($this->equipment->getItemInHand());
						$this->equipment->setItemInHand(clone $item);
					}
				}else{
					$playerInv->setItemInHand($this->armorInventory->getItem($type));
					$this->armorInventory->setItem($type, $item);
				}
			}

			$this->equipment->sendContents($this->getViewers());
		}
	}

	public function fall(float $fallDistance) : void{
		parent::fall($fallDistance);

		$this->level->broadcastLevelEvent($this, LevelEventPacket::EVENT_SOUND_ARMOR_STAND_FALL, $this->getId());
	}

	public function saveNBT() : CompoundTag{
		$nbt = parent::saveNBT();

		if($this->equipment instanceof AltayEntityEquipment){
			$nbt->setTag($this->equipment->getItemInHand()->nbtSerialize(-1, self::TAG_MAINHAND));
			$nbt->setTag($this->equipment->getOffhandItem()->nbtSerialize(-1, self::TAG_OFFHAND));
		}

		if($this->armorInventory !== null){
			$armorTag = new ListTag("armorInv", [], NBT::TAG_Compound);

			for($i = 0; $i < 4; $i++){
				$armorTag->push($this->armorInventory->getItem($i)->nbtSerialize($i));
			}

			$nbt->setTag($armorTag);
		}

		$nbt->setInt(self::TAG_POSE_INDEX, $this->getPose());

		return $nbt;
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
		$this->level->addParticle(new DestroyBlockParticle($this, BlockFactory::get(Block::WOOD2)));
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