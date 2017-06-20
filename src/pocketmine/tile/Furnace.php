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

namespace pocketmine\tile;

use pocketmine\block\Block;
use pocketmine\event\inventory\FurnaceBurnEvent;
use pocketmine\event\inventory\FurnaceSmeltEvent;
use pocketmine\inventory\FurnaceInventory;
use pocketmine\inventory\FurnaceRecipe;
use pocketmine\inventory\InventoryHolder;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\ContainerSetDataPacket;

class Furnace extends Spawnable implements InventoryHolder, Container, Nameable{
	/** @var FurnaceInventory */
	protected $inventory;

	public function __construct(Level $level, CompoundTag $nbt){
		/** @var ShortTag $burnTime */
		if(!(($burnTime = $nbt->getTag("BurnTime")) instanceof ShortTag) or $burnTime->getValue() < 0){
			$nbt->setTag(new ShortTag("BurnTime", 0));
		}

		/** @var ShortTag $cookTime */
		if(!(($cookTime = $nbt->getTag("CookTime")) instanceof ShortTag) or $cookTime->getValue() < 0 or ($nbt->getTag("BurnTime")->getValue() === 0 and $cookTime->getValue() > 0)){
			$nbt->setTag(new ShortTag("CookTime", 0));
		}

		if(!$nbt->exists("MaxTime")){
			$nbt->setTag(new ShortTag("MaxTime", $nbt->getTag("BurnTime")->getValue()));
			$nbt->setTag(new ShortTag("BurnTicks", 0));
		}

		if(!($nbt->getTag("Items") instanceof ListTag)){
			$nbt->setTag(new ListTag("Items", [], NBT::TAG_Compound));
		}

		parent::__construct($level, $nbt);
		$this->inventory = new FurnaceInventory($this);

		for($i = 0; $i < $this->getSize(); ++$i){
			$this->inventory->setItem($i, $this->getItem($i));
		}

		if($this->namedtag["BurnTime"] > 0){
			$this->scheduleUpdate();
		}
	}

	public function getName() : string{
		return ($t = $this->namedtag->getTag("CustomName")) instanceof StringTag ? $t->getValue() : "Furnace";
	}

	public function hasName() : bool{
		return $this->namedtag->exists("CustomName");
	}

	public function setName(string $str){
		if($str === ""){
			$this->namedtag->remove("CustomName");
			return;
		}

		$this->namedtag->setTag(new StringTag("CustomName", $str));
	}

	public function close(){
		if($this->closed === false){
			foreach($this->getInventory()->getViewers() as $player){
				$player->removeWindow($this->getInventory());
			}

			$this->inventory = null;

			parent::close();
		}
	}

	public function saveNBT(){
		$this->namedtag->setTag(new ListTag("Items", [], NBT::TAG_Compound));
		for($index = 0; $index < $this->getSize(); ++$index){
			$this->setItem($index, $this->inventory->getItem($index));
		}
	}

	/**
	 * @return int
	 */
	public function getSize(){
		return 3;
	}

	/**
	 * @param $index
	 *
	 * @return int
	 */
	protected function getSlotIndex($index){
		foreach($this->namedtag->getTag("Items") as $i => $slot){
			/** @var CompoundTag $slot */
			if($slot["Slot"] === $index){
				return $i;
			}
		}

		return -1;
	}

	/**
	 * This method should not be used by plugins, use the Inventory
	 *
	 * @param int $index
	 *
	 * @return Item
	 */
	public function getItem($index){
		$i = $this->getSlotIndex($index);
		if($i < 0){
			return Item::get(Item::AIR, 0, 0);
		}else{
			return Item::nbtDeserialize($this->namedtag->getTag("Items")[$i]);
		}
	}

	/**
	 * This method should not be used by plugins, use the Inventory
	 *
	 * @param int  $index
	 * @param Item $item
	 *
	 * @return bool
	 */
	public function setItem($index, Item $item){
		$i = $this->getSlotIndex($index);

		$d = $item->nbtSerialize($index);

		$items = $this->namedtag->getTag("Items");

		if($item->getId() === Item::AIR or $item->getCount() <= 0){
			if($i >= 0){
				unset($items[$i]);
			}
		}elseif($i < 0){
			for($i = 0; $i <= $this->getSize(); ++$i){
				if(!isset($items[$i])){
					break;
				}
			}
			$items[$i] = $d;
		}else{
			$items[$i] = $d;
		}

		$this->namedtag->setTag($items);

		return true;
	}

	/**
	 * @return FurnaceInventory
	 */
	public function getInventory(){
		return $this->inventory;
	}

	protected function checkFuel(Item $fuel){
		$this->server->getPluginManager()->callEvent($ev = new FurnaceBurnEvent($this, $fuel, $fuel->getFuelTime()));

		if($ev->isCancelled()){
			return;
		}

		$this->namedtag->setTag(new ShortTag("MaxTime", $ev->getBurnTime()));
		$this->namedtag->setTag(new ShortTag("BurnTime", $ev->getBurnTime()));
		$this->namedtag->setTag(new ShortTag("BurnTicks", 0));
		if($this->getBlock()->getId() === Item::FURNACE){
			$this->getLevel()->setBlock($this, Block::get(Item::BURNING_FURNACE, $this->getBlock()->getDamage()), true);
		}

		if($ev->getBurnTime() > 0 and $ev->isBurning()){
			$fuel->setCount($fuel->getCount() - 1);
			if($fuel->getCount() === 0){
				$fuel = Item::get(Item::AIR, 0, 0);
			}
			$this->inventory->setFuel($fuel);
		}
	}

	public function onUpdate(){
		if($this->closed === true){
			return false;
		}

		$this->timings->startTiming();

		$ret = false;

		$fuel = $this->inventory->getFuel();
		$raw = $this->inventory->getSmelting();
		$product = $this->inventory->getResult();
		$smelt = $this->server->getCraftingManager()->matchFurnaceRecipe($raw);
		$canSmelt = ($smelt instanceof FurnaceRecipe and $raw->getCount() > 0 and (($smelt->getResult()->equals($product) and $product->getCount() < $product->getMaxStackSize()) or $product->getId() === Item::AIR));

		$burnTime = $this->namedtag->getTag("BurnTime")->getValue();
		if($burnTime <= 0 and $canSmelt and $fuel->getFuelTime() !== null and $fuel->getCount() > 0){
			$this->checkFuel($fuel);
		}

		if($burnTime > 0){
			$burnTime -= 1;
			$this->namedtag->setTag(new ShortTag("BurnTime", $burnTime));
			$this->namedtag->setTag(new ShortTag("BurnTicks", (int) ceil(($burnTime / $this->namedtag->getTag("MaxTime")->getValue() * 200))));

			if($smelt instanceof FurnaceRecipe and $canSmelt){
				$this->namedtag->setTag(new ShortTag("CookTime", (int) ($this->namedtag["CookTime"]) + 1));
				if($this->namedtag->getTag("CookTime")->getValue() >= 200){ //10 seconds
					$product = Item::get($smelt->getResult()->getId(), $smelt->getResult()->getDamage(), $product->getCount() + 1);

					$this->server->getPluginManager()->callEvent($ev = new FurnaceSmeltEvent($this, $raw, $product));

					if(!$ev->isCancelled()){
						$this->inventory->setResult($ev->getResult());
						$raw->setCount($raw->getCount() - 1);
						if($raw->getCount() === 0){
							$raw = Item::get(Item::AIR, 0, 0);
						}
						$this->inventory->setSmelting($raw);
					}

					$this->namedtag->setTag(new ShortTag("CookTime", ((int) $this->namedtag->getTag("CookTime")->getValue()) - 200));
				}
			}elseif($burnTime <= 0){
				$this->namedtag->setTag(new ShortTag("BurnTime", 0));
				$this->namedtag->setTag(new ShortTag("CookTime", 0));
				$this->namedtag->setTag(new ShortTag("BurnTicks", 0));
			}else{
				$this->namedtag->setTag(new ShortTag("CookTime", 0));
			}
			$ret = true;
		}else{
			if($this->getBlock()->getId() === Item::BURNING_FURNACE){
				$this->getLevel()->setBlock($this, Block::get(Item::FURNACE, $this->getBlock()->getDamage()), true);
			}
			$this->namedtag->setTag(new ShortTag("BurnTime", 0));
			$this->namedtag->setTag(new ShortTag("CookTime", 0));
			$this->namedtag->setTag(new ShortTag("BurnTicks", 0));
		}

		foreach($this->getInventory()->getViewers() as $player){
			$windowId = $player->getWindowId($this->getInventory());
			if($windowId > 0){
				$pk = new ContainerSetDataPacket();
				$pk->windowid = $windowId;
				$pk->property = 0; //Smelting
				$pk->value = $this->namedtag->getTag("CookTime")->getValue();
				$player->dataPacket($pk);

				$pk = new ContainerSetDataPacket();
				$pk->windowid = $windowId;
				$pk->property = 1; //Fire icon
				$pk->value = $this->namedtag->getTag("BurnTicks")->getValue();
				$player->dataPacket($pk);
			}

		}

		$this->lastUpdate = microtime(true);

		$this->timings->stopTiming();

		return $ret;
	}

	public function getSpawnCompound(){
		$nbt = new CompoundTag("", [
			new StringTag("id", Tile::FURNACE),
			new IntTag("x", (int) $this->x),
			new IntTag("y", (int) $this->y),
			new IntTag("z", (int) $this->z),
			$this->namedtag->getTag("BurnTime"),
			$this->namedtag->getTag("CookTime")
		]);

		if($this->hasName()){
			$nbt->setTag(clone $this->namedtag->getTag("CustomName"));
		}
		return $nbt;
	}
}
