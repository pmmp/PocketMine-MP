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
use pocketmine\inventory\utils\EquipmentSlot;
use pocketmine\item\Armor;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
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
			$this->equipment->setOffhandItem(Item::nbtDeserialize($this->namedtag->getCompoundTag(self::TAG_OFFHAND)));
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

	public function onFirstInteract(Player $player, Item $item, Vector3 $clickPos) : bool{
		if($player->isSneaking()){
			$this->setPose(($this->getPose() + 1) % 13);
			return true;
		}

		if($this->isValid() and !$player->isSpectator()){
			$targetSlot = EquipmentSlot::MAINHAND;
			$isArmorSlot = false;

			if($item instanceof Armor){
				$targetSlot = $item->getArmorSlot();
				$isArmorSlot = true;
			}elseif($item->getId() === Item::SKULL or $item->getId() === Item::PUMPKIN){
				$targetSlot = Armor::SLOT_HELMET;
				$isArmorSlot = true;
			}elseif($item->isNull()){
				$clickOffset = $clickPos->y - $this->y;

				if($clickOffset >= 0.1 and $clickOffset < 0.55 and !$this->armorInventory->getItem(ArmorInventory::SLOT_FEET)->isNull()){
					$targetSlot = Armor::SLOT_BOOTS;
					$isArmorSlot = true;
				}elseif($clickOffset >= 0.9 and $clickOffset < 1.6 and !$this->armorInventory->getItem(ArmorInventory::SLOT_CHEST)->isNull()){
					$targetSlot = Armor::SLOT_CHESTPLATE;
					$isArmorSlot = true;
				}elseif($clickOffset >= 0.4 and $clickOffset < 1.2 and !$this->armorInventory->getItem(ArmorInventory::SLOT_LEGS)->isNull()){
					$targetSlot = Armor::SLOT_LEGGINGS;
					$isArmorSlot = true;
				}elseif($clickOffset >= 1.6 and !$this->armorInventory->getItem(ArmorInventory::SLOT_HEAD)->isNull()){
					$targetSlot = Armor::SLOT_HELMET;
					$isArmorSlot = true;
				}
			}

			$this->level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_MOB_ARMOR_STAND_PLACE);

			$this->tryChangeEquipment($player, $item, $targetSlot, $isArmorSlot);

			return true;
		}

		return false;
	}

	protected function tryChangeEquipment(Player $player, Item $targetItem, int $slot, bool $isArmorSlot = false) : void{
		$sourceItem = $isArmorSlot ? $this->armorInventory->getItem($slot) : $this->equipment->getItem($slot);

		if($isArmorSlot){
			$this->armorInventory->setItem($slot, (clone $targetItem)->setCount(1));
		}else{
			$this->equipment->setItem($slot, (clone $targetItem)->setCount(1));
		}

		if(!$targetItem->isNull() and $player->isSurvival()){
			$targetItem->pop();
		}

		if(!$targetItem->isNull() and $targetItem->equals($sourceItem)){
			$targetItem->setCount($targetItem->getCount() + $sourceItem->getCount());
		}else{
			$player->getInventory()->addItem($sourceItem);
		}

		$this->equipment->sendContents($player);
		$this->armorInventory->sendContents($player);
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
			$this->vibrateTimer += 30;
		}
	}

	protected function doHitAnimation() : void{
		$this->level->broadcastLevelEvent($this, LevelEventPacket::EVENT_SOUND_ARMOR_STAND_HIT);
	}

	public function startDeathAnimation() : void{
		$this->level->broadcastLevelEvent($this, LevelEventPacket::EVENT_SOUND_ARMOR_STAND_BREAK);
		$this->level->broadcastLevelEvent($this, LevelEventPacket::EVENT_PARTICLE_ARMOR_STAND_DESTROY);
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
