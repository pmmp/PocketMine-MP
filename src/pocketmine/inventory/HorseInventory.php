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

namespace pocketmine\inventory;

use pocketmine\entity\EntityIds;
use pocketmine\entity\passive\Horse;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\nbt\NetworkLittleEndianNBTStream;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\MobArmorEquipmentPacket;
use pocketmine\network\mcpe\protocol\types\WindowTypes;
use pocketmine\network\mcpe\protocol\UpdateEquipPacket;
use pocketmine\Player;

class HorseInventory extends AbstractHorseInventory{
	/** @var Horse */
	protected $holder;

	public function getName() : string{
		return "Horse";
	}

	public function getDefaultSize() : int{
		return 2;
	}

	/**
	 * @return Item
	 */
	public function getArmor() : Item{
		return $this->getItem(1);
	}

	/**
	 * @param Item $armor
	 */
	public function setArmor(Item $armor) : void{
		$this->setItem(1, $armor);

		foreach($this->viewers as $player){
			$this->sendArmor($player);
		}
	}

	public function getNetworkType() : int{
		return WindowTypes::HORSE;
	}

	public function onSlotChange(int $index, Item $before, bool $send) : void{
		parent::onSlotChange($index, $before, $send);

		if($index === 1){
			foreach($this->viewers as $player){
				$this->sendArmor($player);
			}

			$this->holder->level->broadcastLevelSoundEvent($this->holder, LevelSoundEventPacket::SOUND_ARMOR, -1, EntityIds::HORSE);
		}
	}

	/**
	 * @return Horse
	 */
	public function getHolder(){
		return $this->holder;
	}

	public function sendArmor(Player $player) : void{
		$air = ItemFactory::get(Item::AIR);

		$pk = new MobArmorEquipmentPacket();
		$pk->entityRuntimeId = $this->getHolder()->getId();
		$pk->slots = [0 => $air, 1 => $this->getItem(1), 2 => $air, 3 => $air];

		$player->sendDataPacket($pk);
	}

	public function onOpen(Player $who) : void{
		$pk = new UpdateEquipPacket();
		$pk->entityUniqueId = $this->holder->getId();
		$pk->unknownVarint = 0;
		$pk->windowType = $this->getNetworkType();
		$pk->windowId = $who->getWindowId($this);
		$pk->namedtag = (new NetworkLittleEndianNBTStream())->write($this->getNamedtag());

		$who->sendDataPacket($pk);

		parent::onOpen($who);

		$this->sendArmor($who);
	}

	public function getNamedtag() : CompoundTag{
		return new CompoundTag("", [
			new ListTag("slots", [
				new CompoundTag("", [
					new ListTag("acceptedItems", [
						new CompoundTag("", [
							(ItemFactory::get(Item::SADDLE))->nbtSerialize(-1, "slotItem")
						])
					]),
					$this->getSaddle()->nbtSerialize(-1, "item"),
					new IntTag("slotNumber", 0)
				]),
				new CompoundTag("", [
					new ListTag("acceptedItems", [
						new CompoundTag("", [
							(ItemFactory::get(Item::HORSE_ARMOR_DIAMOND))->nbtSerialize(-1, "slotItem")
						]),
						new CompoundTag("", [
							(ItemFactory::get(Item::HORSE_ARMOR_GOLD))->nbtSerialize(-1, "slotItem")
						]),
						new CompoundTag("", [
							(ItemFactory::get(Item::HORSE_ARMOR_IRON))->nbtSerialize(-1, "slotItem")
						]),
						new CompoundTag("", [
							(ItemFactory::get(Item::HORSE_ARMOR_LEATHER))->nbtSerialize(-1, "slotItem")
						])
					]),
					$this->getArmor()->nbtSerialize(-1, "item"),
					new IntTag("slotNumber", 1)
				])
			])
		]);
	}
}