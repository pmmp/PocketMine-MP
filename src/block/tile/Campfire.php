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

use pocketmine\block\Campfire as BlockCampfire;
use pocketmine\block\inventory\CampfireInventory;
use pocketmine\inventory\CallbackInventoryListener;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\world\World;

class Campfire extends Spawnable implements Container{
	use ContainerTrait;

	public const TAG_ITEM = "Item";
	public const TAG_COOKING_TIME = "ItemTime";

	protected CampfireInventory $inventory;
	/** @var array<int, int> */
	private array $cookingTimes = [];

	public function __construct(World $world, Vector3 $pos){
		parent::__construct($world, $pos);
		$this->inventory = new CampfireInventory($this->position);
		$this->inventory->getListeners()->add(CallbackInventoryListener::onAnyChange(
			static function(Inventory $unused) use ($world, $pos) : void{
				$block = $world->getBlock($pos);
				if($block instanceof BlockCampfire){
					$world->setBlock($pos, $block);
				}
			})
		);
	}

	public function getInventory() : CampfireInventory{
		return $this->inventory;
	}

	public function getRealInventory() : CampfireInventory{
		return $this->inventory;
	}

	/**
	 * @return array<int, int>
	 */
	public function getCookingTimes() : array{
		return $this->cookingTimes;
	}

	/**
	 * @param array<int, int> $cookingTimes
	 */
	public function setCookingTimes(array $cookingTimes) : void{
		$this->cookingTimes = $cookingTimes;
	}

	public function readSaveData(CompoundTag $nbt) : void{
		$items = [];
		$listeners = $this->inventory->getListeners()->toArray();
		$this->inventory->getListeners()->remove(...$listeners); //prevent any events being fired by initialization

		for($slot = 1; $slot <= 4; $slot++){
			if(($tag = $nbt->getTag(self::TAG_ITEM . $slot)) instanceof CompoundTag){
				$items[$slot - 1] = Item::nbtDeserialize($tag);
			}

			if(($tag = $nbt->getTag(self::TAG_COOKING_TIME . $slot)) instanceof IntTag){
				$this->cookingTimes[$slot - 1] = $tag->getValue();
			}
		}

		$this->inventory->setContents($items);
		$this->inventory->getListeners()->add(...$listeners);
	}

	protected function writeSaveData(CompoundTag $nbt) : void{
		for($slot = 1; $slot <= 4; $slot++){
			$item = $this->inventory->getItem($slot - 1);
			if(!$item->isNull()){
				$nbt->setTag(self::TAG_ITEM . $slot, $item->nbtSerialize($slot));
			}

			$cookingTime = $this->cookingTimes[$slot - 1] ?? 0;
			if($cookingTime !== 0){
				$nbt->setInt(self::TAG_COOKING_TIME . $slot, $cookingTime);
			}
		}
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void{
		for($slot = 1; $slot <= 4; $slot++){
			$item = $this->inventory->getItem($slot - 1);
			if(!$item->isNull()){
				$nbt->setTag(self::TAG_ITEM . $slot, $item->nbtSerialize());
			}
		}
	}
}
