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

namespace pocketmine\tile;

use pocketmine\block\Block;
use pocketmine\inventory\FurnaceInventory;
use pocketmine\inventory\FurnaceRecipe;
use pocketmine\inventory\InventoryHolder;
use pocketmine\item\Item;
use pocketmine\level\format\FullChunk;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\Byte;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Short;
use pocketmine\network\protocol\ContainerSetDataPacket;

class Furnace extends Tile implements InventoryHolder, Container{
	/** @var FurnaceInventory */
	protected $inventory;

	public function __construct(FullChunk $chunk, Compound $nbt){
		$nbt["id"] = Tile::FURNACE;
		parent::__construct($chunk, $nbt);
		$this->inventory = new FurnaceInventory($this);

		if(!isset($this->namedtag->Items) or !($this->namedtag->Items instanceof Enum)){
			$this->namedtag->Items = new Enum("Inventory", []);
			$this->namedtag->Items->setTagType(NBT::TAG_Compound);
		}

		for($i = 0; $i < $this->getSize(); ++$i){
			$this->inventory->setItem($i, $this->getItem($i));
		}

		if(!isset($this->namedtag->BurnTime) or $this->namedtag["BurnTime"] < 0){
			$this->namedtag->BurnTime = new Short("BurnTime", 0);
		}
		if(!isset($this->namedtag->CookTime) or $this->namedtag["CookTime"] < 0 or ($this->namedtag["BurnTime"] === 0 and $this->namedtag["CookTime"] > 0)){
			$this->namedtag->CookTime = new Short("CookTime", 0);
		}
		if(!isset($this->namedtag->MaxTime)){
			$this->namedtag->MaxTime = new Short("BurnTime", $this->namedtag["BurnTime"]);
			$this->namedtag->BurnTicks = new Short("BurnTicks", 0);
		}
		if($this->namedtag["BurnTime"] > 0){
			$this->scheduleUpdate();
		}
	}

	public function saveNBT(){
		$this->namedtag->Items = new Enum("Inventory", []);
		$this->namedtag->Items->setTagType(NBT::TAG_Compound);
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
		foreach($this->namedtag->Items as $i => $slot){
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
			return Item::get($this->namedtag->Items[$i]["id"], $this->namedtag->Items[$i]["Damage"], $this->namedtag->Items[$i]["Count"]);
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

		$d = new Compound(false, array(
			new Byte("Count", $item->getCount()),
			new Byte("Slot", $index),
			new Short("id", $item->getID()),
			new Short("Damage", $item->getDamage()),
		));

		if($item->getID() === Item::AIR or $item->getCount() <= 0){
			if($i >= 0){
				unset($this->namedtag->Items[$i]);
			}
		}elseif($i < 0){
			for($i = 0; $i <= $this->getSize(); ++$i){
				if(!isset($this->namedtag->Items[$i])){
					break;
				}
			}
			$this->namedtag->Items[$i] = $d;
		}else{
			$this->namedtag->Items[$i] = $d;
		}

		return true;
	}

	/**
	 * @return FurnaceInventory
	 */
	public function getInventory(){
		return $this->inventory;
	}

	public function onUpdate(){
		if($this->closed === true){
			return false;
		}

		$ret = false;

		$fuel = $this->inventory->getFuel();
		$raw = $this->inventory->getSmelting();
		$product = $this->inventory->getResult();
		$smelt = $this->server->getCraftingManager()->matchFurnaceRecipe($raw);
		$canSmelt = ($smelt instanceof FurnaceRecipe and $raw->getCount() > 0 and (($smelt->getResult()->equals($product, true) and $product->getCount() < $product->getMaxStackSize()) or $product->getID() === Item::AIR));
		if($this->namedtag["BurnTime"] <= 0 and $canSmelt and $fuel->getFuelTime() !== false and $fuel->getCount() > 0){
			$this->lastUpdate = microtime(true);
			$time = floor($fuel->getFuelTime() * 20);
			$this->namedtag->MaxTime = new Short("MaxTime", $time);
			$this->namedtag->BurnTime = new Short("BurnTime", $time);
			$this->namedtag->BurnTicks = new Short("BurnTicks", 0);
			$fuel->setCount($fuel->getCount() - 1);
			if($fuel->getCount() === 0){
				$fuel = Item::get(Item::AIR, 0, 0);
			}
			$this->inventory->setFuel($fuel);
			$current = $this->getLevel()->getBlock($this);
			if($current->getID() === Item::FURNACE){
				$this->getLevel()->setBlock($this, Block::get(Item::BURNING_FURNACE, $current->getDamage()), true, false, true);
			}
		}
		if($this->namedtag["BurnTime"] > 0){
			$ticks = (microtime(true) - $this->lastUpdate) * 20;
			$this->namedtag->BurnTime = new Short("BurnTime", $this->namedtag["BurnTime"] - $ticks);
			$this->namedtag->BurnTicks = new Short("BurnTicks", ceil(($this->namedtag["BurnTime"] / $this->namedtag["MaxTime"] * 200)));

			if($smelt instanceof FurnaceRecipe and $canSmelt){
				$this->namedtag->CookTime = new Short("CookTime", $this->namedtag["CookTime"] + $ticks);
				if($this->namedtag["CookTime"] >= 200){ //10 seconds
					$product = Item::get($smelt->getResult()->getID(), $smelt->getResult()->getDamage(), $product->getCount() + 1);
					$this->inventory->setResult($product);
					$raw->setCount($raw->getCount() - 1);
					if($raw->getCount() === 0){
						$raw = Item::get(Item::AIR, 0, 0);
					}
					$this->inventory->setSmelting($raw);
					$this->namedtag->CookTime = new Short("CookTime", $this->namedtag["CookTime"] - 200);
				}
			}elseif($this->namedtag["BurnTime"] <= 0){
				$this->namedtag->BurnTime = new Short("BurnTime", 0);
				$this->namedtag->CookTime = new Short("CookTime", 0);
				$this->namedtag->BurnTicks = new Short("BurnTicks", 0);
			}else{
				$this->namedtag->CookTime = new Short("CookTime", 0);
			}
			$ret = true;
		}else{
			$current = $this->getLevel()->getBlock($this);
			if($current->getID() === Item::BURNING_FURNACE){
				$this->getLevel()->setBlock($this, Block::get(Item::FURNACE, $current->getDamage()), true, false);
			}
			$this->namedtag->BurnTime = new Short("BurnTime", 0);
			$this->namedtag->CookTime = new Short("CookTime", 0);
			$this->namedtag->BurnTicks = new Short("BurnTicks", 0);
		}

		foreach($this->getInventory()->getViewers() as $player){
			$windowId = $player->getWindowId($this->getInventory());
			if($windowId > 0){
				$pk = new ContainerSetDataPacket;
				$pk->windowid = $windowId;
				$pk->property = 0; //Smelting
				$pk->value = floor($this->namedtag["CookTime"]);
				$player->dataPacket($pk);

				$pk = new ContainerSetDataPacket;
				$pk->windowid = $windowId;
				$pk->property = 1; //Fire icon
				$pk->value = $this->namedtag["BurnTicks"];
				$player->dataPacket($pk);
			}

		}

		$this->lastUpdate = microtime(true);

		return $ret;
	}
}
