<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
 */

declare(strict_types=1);

namespace pocketmine\block\tile;

use pocketmine\data\bedrock\item\SavedItemStackData;
use pocketmine\data\SavedDataLoadingException;
use pocketmine\inventory\SimpleInventory;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\world\World;

class Shelf extends Spawnable implements Container{
	use ContainerTrait;

	public const TAG_ITEMS = "Items"; //TAG_List<TAG_Compound>

	private SimpleInventory $inventory;

	public function __construct(World $world, Vector3 $pos){
		parent::__construct($world, $pos);
		$this->inventory = new SimpleInventory(3); // 3 slots
	}

	public function getInventory() : SimpleInventory{
		return $this->inventory;
	}

	public function getRealInventory() : SimpleInventory{
		return $this->inventory;
	}

	public function readSaveData(CompoundTag $nbt) : void{
		$this->loadItems($nbt);
	}

	protected function writeSaveData(CompoundTag $nbt) : void{
		$this->saveItems($nbt);
	}

	/**
	 * @throws SavedDataLoadingException
	 */
	protected function loadItems(CompoundTag $nbt) : void{
		$inventoryTag = $nbt->getListTag(self::TAG_ITEMS, CompoundTag::class);
		if($inventoryTag !== null){
			$inventoryItems = [];

			/** @var CompoundTag $itemNbt */
			foreach($inventoryTag as $itemNbt){
				$slot = $itemNbt->getByte(SavedItemStackData::TAG_SLOT);
				if($slot >= 0 && $slot < 3){
					$inventoryItems[$slot] = Item::nbtDeserialize($itemNbt);
				}
			}

			$this->inventory->setContents($inventoryItems);
		}
	}

	protected function saveItems(CompoundTag $nbt) : void{
		$items = [];
		foreach($this->inventory->getContents(true) as $slot => $item){
			if(!$item->isNull()){
				$items[] = $item->nbtSerialize($slot);
			}
		}

		$nbt->setTag(self::TAG_ITEMS, new ListTag($items, NBT::TAG_Compound));
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void{
		$this->saveItems($nbt);
	}

	public function close() : void{
		if(!$this->closed){
			$this->inventory->removeAllViewers();
			parent::close();
		}
	}
}
