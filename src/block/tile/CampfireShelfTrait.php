<?php

namespace pocketmine\block\tile;

use pocketmine\block\inventory\CampfireInventory;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;

trait CampfireShelfTrait{

	/**
	 * @var int[]
	 */
	private array $cookingTimes = [];

	protected CampfireInventory $inventory;

	protected function readData(CompoundTag $nbt) : void{
		$items = [];
		$listeners = $this->inventory->getListeners()->toArray();
		$this->inventory->getListeners()->remove(...$listeners);

		for($slot = 1; $slot <= Campfire::MAX_ITEMS; $slot++){
			$tag = $nbt->getTag(Campfire::ITEM_SLOTS . $slot);
			if($tag instanceof CompoundTag){
				$items[$slot - 1] = Item::nbtDeserialize($tag);
			}
			$tag = $nbt->getTag(Campfire::ITEM_TIMES . $slot);
			if($tag instanceof IntTag){
				$this->cookingTimes[$slot - 1] = $tag->getValue();
			}
		}

		$this->inventory->setContents($items);
		$this->inventory->getListeners()->add(...$listeners);
	}

	protected function writeData(CompoundTag $nbt) : void{
		for($slot = 1; $slot <= Campfire::MAX_ITEMS; $slot++){
			$item = $this->inventory->getItem($slot - 1);
			if(!$item->isNull()){
				$nbt->setTag(Campfire::ITEM_SLOTS . $slot, $item->nbtSerialize($slot));
			}

			$cookingTime = $this->cookingTimes[$slot - 1] ?? 0;
			if($cookingTime !== 0){
				$nbt->setInt(Campfire::ITEM_TIMES . $slot, $cookingTime);
			}
		}
	}
}
