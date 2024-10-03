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

use pocketmine\block\utils\ChiseledBookshelfSlot;
use pocketmine\data\bedrock\item\SavedItemData;
use pocketmine\data\bedrock\item\SavedItemStackData;
use pocketmine\data\SavedDataLoadingException;
use pocketmine\inventory\SimpleInventory;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\world\World;
use function count;

class ChiseledBookshelf extends Tile implements Container{
	use ContainerTrait;

	private const TAG_LAST_INTERACTED_SLOT = "LastInteractedSlot"; //TAG_Int

	private SimpleInventory $inventory;

	private ?ChiseledBookshelfSlot $lastInteractedSlot = null;

	public function __construct(World $world, Vector3 $pos){
		parent::__construct($world, $pos);
		$this->inventory = new SimpleInventory(count(ChiseledBookshelfSlot::cases()));
	}

	public function getInventory() : SimpleInventory{
		return $this->inventory;
	}

	public function getRealInventory() : SimpleInventory{
		return $this->inventory;
	}

	public function getLastInteractedSlot() : ?ChiseledBookshelfSlot{
		return $this->lastInteractedSlot;
	}

	public function setLastInteractedSlot(?ChiseledBookshelfSlot $lastInteractedSlot) : void{
		$this->lastInteractedSlot = $lastInteractedSlot;
	}

	public function readSaveData(CompoundTag $nbt) : void{
		$this->loadItems($nbt);

		$lastInteractedSlot = $nbt->getInt(self::TAG_LAST_INTERACTED_SLOT, 0);
		if($lastInteractedSlot !== 0){
			$this->lastInteractedSlot = ChiseledBookshelfSlot::tryFrom($lastInteractedSlot - 1);
		}
	}

	protected function writeSaveData(CompoundTag $nbt) : void{
		$this->saveItems($nbt);

		$nbt->setInt(self::TAG_LAST_INTERACTED_SLOT, $this->lastInteractedSlot !== null ?
			$this->lastInteractedSlot->value + 1 :
			0
		);
	}

	protected function loadItems(CompoundTag $tag) : void{
		if(($inventoryTag = $tag->getTag(Container::TAG_ITEMS)) instanceof ListTag && $inventoryTag->getTagType() === NBT::TAG_Compound){
			$inventory = $this->getRealInventory();
			$listeners = $inventory->getListeners()->toArray();
			$inventory->getListeners()->remove(...$listeners); //prevent any events being fired by initialization

			$newContents = [];
			/** @var CompoundTag $itemNBT */
			foreach($inventoryTag as $slot => $itemNBT){
				try{
					$count = $itemNBT->getByte(SavedItemStackData::TAG_COUNT);
					if($count === 0){
						continue;
					}
					$newContents[$slot] = Item::nbtDeserialize($itemNBT);
				}catch(SavedDataLoadingException $e){
					//TODO: not the best solution
					\GlobalLogger::get()->logException($e);
					continue;
				}
			}
			$inventory->setContents($newContents);

			$inventory->getListeners()->add(...$listeners);
		}

		if(($lockTag = $tag->getTag(Container::TAG_LOCK)) instanceof StringTag){
			$this->lock = $lockTag->getValue();
		}
	}

	protected function saveItems(CompoundTag $tag) : void{
		$items = [];
		foreach($this->getRealInventory()->getContents(true) as $slot => $item){
			if($item->isNull()){
				$items[$slot] = CompoundTag::create()
					->setByte(SavedItemStackData::TAG_COUNT, 0)
					->setShort(SavedItemData::TAG_DAMAGE, 0)
					->setString(SavedItemData::TAG_NAME, "")
					->setByte(SavedItemStackData::TAG_WAS_PICKED_UP, 0);
			}else{
				$items[$slot] = $item->nbtSerialize();
			}
		}

		$tag->setTag(Container::TAG_ITEMS, new ListTag($items, NBT::TAG_Compound));

		if($this->lock !== null){
			$tag->setString(Container::TAG_LOCK, $this->lock);
		}
	}
}
