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
use pocketmine\block\inventory\ShelfInventory;
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
		$this->inventory = new ShelfInventory($this->getPosition()); // 3 slots
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
			foreach($inventoryTag as $index => $itemNbt){
				$slot = $itemNbt->getByte(SavedItemStackData::TAG_SLOT);
				try{
					\GlobalLogger::get()->info("TileShelf:loadItems itemIndex=" . $index . " tagSlot=" . $slot . " rawName=" . $itemNbt->getString("id") );
				}catch(\Throwable){ }
				if($slot >= 0 && $slot < 3){
					$inventoryItems[$slot] = Item::nbtDeserialize($itemNbt);
				}
			}

			$this->inventory->setContents($inventoryItems);
		}
	}

	protected function saveItems(CompoundTag $nbt) : void{
		$items = [];
		// Order items in the NBT list in visual left-to-right order so the client
		// renders them in the expected slots. Some clients render shelf items by
		// list order rather than by the stored slot tag, so we reverse the
		// serialization order for certain block facings.
		$contents = $this->inventory->getContents(true);
		// Default order: 0,1,2 (left->right)
		$reverse = false;
		$block = $this->position->getWorld()->getBlock($this->position);
		$front = null;
		if($block instanceof \pocketmine\block\Shelf){
			$front = \pocketmine\math\Facing::opposite($block->getFacing());
			// For NORTH or WEST fronts, the visual left-to-right mapping may be reversed
			if($front === \pocketmine\math\Facing::NORTH || $front === \pocketmine\math\Facing::WEST){
				$reverse = true;
			}
		}
		$orderList = [];
		// We will collect the serialized CompoundTag objects first so we can inspect their Slot fields
		$serialized = [];
		// Build explicit visual order: left->right mapping of inventory slot indices
		$visualOrder = $reverse ? [2, 1, 0] : [0, 1, 2];
		foreach($visualOrder as $visualIndex => $slotIndex){
			$item = $contents[$slotIndex] ?? null;
			if($item !== null && !$item->isNull()){
				// serialize using the actual inventory slot index (for backwards compatibility),
				// then overwrite the saved Slot tag with the visual index so clients that
				// interpret the compound Slot or the list order both see the item in the
				// expected visual position.
				$compound = $item->nbtSerialize($slotIndex);
				try{
					$compound->setByte(SavedItemStackData::TAG_SLOT, $visualIndex);
				}catch(\Throwable){
					// if setByte isn't available for some reason on the tag, ignore and continue
				}
				$serialized[] = $compound;
				$orderList[] = $slotIndex;
			}
		}

		// Log each compound's stored slot field to detect mismatches between the compound Slot tag and the list order
		try{
			$pos = $this->getPosition();
			$slotInfo = [];
			foreach($serialized as $idx => $compound){
				$compSlot = $compound->getByte(SavedItemStackData::TAG_SLOT);
				$slotInfo[] = "listIndex=" . $idx . "->compoundSlot=" . $compSlot . " id=" . $compound->getString("id");
				$items[] = $compound;
			}
			\GlobalLogger::get()->info("TileShelf:saveItems pos=" . $pos->getFloorX() . "/" . $pos->getFloorY() . "/" . $pos->getFloorZ() . " front=" . ($front ?? 'null') . " reverse=" . ($reverse ? '1' : '0') . " order=" . implode(',', $orderList) . " slotInfo=" . implode('|', $slotInfo));
			@fwrite(STDOUT, "TileShelf:STDOUT saveItems pos=" . $pos->getFloorX() . "/" . $pos->getFloorY() . "/" . $pos->getFloorZ() . " front=" . ($front ?? 'null') . " reverse=" . ($reverse ? '1' : '0') . " order=" . implode(',', $orderList) . " slotInfo=" . implode('|', $slotInfo) . "\n");
		}catch(\Throwable){
			// fall back to writing without detailed debug
			foreach($serialized as $compound){
				$items[] = $compound;
			}
			// also write fallback info to STDOUT so the server operator can see it immediately
			try{
				$pos = $this->getPosition();
				@fwrite(STDOUT, "TileShelf:STDOUT saveItems fallback pos=" . $pos->getFloorX() . "/" . $pos->getFloorY() . "/" . $pos->getFloorZ() . " serializedCount=" . count($serialized) . "\n");
			}catch(\Throwable){ }
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
