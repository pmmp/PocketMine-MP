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
use pocketmine\block\BlockFactory;
use pocketmine\event\inventory\FurnaceBurnEvent;
use pocketmine\event\inventory\FurnaceSmeltEvent;
use pocketmine\inventory\FurnaceInventory;
use pocketmine\inventory\FurnaceRecipe;
use pocketmine\inventory\InventoryHolder;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\ContainerSetDataPacket;
use pocketmine\Player;

class Furnace extends Spawnable implements InventoryHolder, Container, Nameable{
	use NameableTrait;

	/** @var FurnaceInventory */
	protected $inventory;

	public function __construct(Level $level, CompoundTag $nbt){
		if(!isset($nbt->BurnTime) or $nbt->BurnTime->getValue() < 0){
			$nbt->BurnTime = new ShortTag("BurnTime", 0);
		}
		if(!isset($nbt->CookTime) or $nbt->CookTime->getValue() < 0 or ($nbt->BurnTime->getValue() === 0 and $nbt->CookTime->getValue() > 0)){
			$nbt->CookTime = new ShortTag("CookTime", 0);
		}
		if(!isset($nbt->MaxTime)){
			$nbt->MaxTime = new ShortTag("BurnTime", $nbt->BurnTime->getValue());
			unset($nbt->BurnTicks);
		}

		if(!isset($nbt->BurnTicks)){
			$nbt->BurnTicks = new ShortTag("BurnTicks", 0);
		}

		parent::__construct($level, $nbt);
		$this->inventory = new FurnaceInventory($this);

		if(!isset($this->namedtag->Items) or !($this->namedtag->Items instanceof ListTag)){
			$this->namedtag->Items = new ListTag("Items", [], NBT::TAG_Compound);
		}

		for($i = 0; $i < $this->getSize(); ++$i){
			$this->inventory->setItem($i, $this->getItem($i), false);
		}

		if($this->namedtag->BurnTime->getValue() > 0){
			$this->scheduleUpdate();
		}
	}

	/**
	 * @return string
	 */
	public function getDefaultName() : string{
		return "Furnace";
	}

	public function close(){
		if($this->closed === false){
			$this->inventory->removeAllViewers(true);
			$this->inventory = null;

			parent::close();
		}
	}

	public function saveNBT(){
		$this->namedtag->Items->setValue([]);
		for($index = 0; $index < $this->getSize(); ++$index){
			$this->setItem($index, $this->inventory->getItem($index));
		}
	}

	/**
	 * @return int
	 */
	public function getSize() : int{
		return 3;
	}

	/**
	 * @param $index
	 *
	 * @return int
	 */
	protected function getSlotIndex(int $index) : int{
		foreach($this->namedtag->Items as $i => $slot){
			if($slot->Slot->getValue() === $index){
				return (int) $i;
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
	public function getItem(int $index) : Item{
		$i = $this->getSlotIndex($index);
		if($i < 0){
			return ItemFactory::get(Item::AIR, 0, 0);
		}else{
			return Item::nbtDeserialize($this->namedtag->Items[$i]);
		}
	}

	/**
	 * This method should not be used by plugins, use the Inventory
	 *
	 * @param int  $index
	 * @param Item $item
	 */
	public function setItem(int $index, Item $item){
		$i = $this->getSlotIndex($index);

		$d = $item->nbtSerialize($index);

		if($item->isNull()){
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

		$this->namedtag->MaxTime->setValue($ev->getBurnTime());
		$this->namedtag->BurnTime->setValue($ev->getBurnTime());
		$this->namedtag->BurnTicks->setValue(0);
		if($this->getBlock()->getId() === Block::FURNACE){
			$this->getLevel()->setBlock($this, BlockFactory::get(Block::BURNING_FURNACE, $this->getBlock()->getDamage()), true);
		}

		if($this->namedtag->BurnTime->getValue() > 0 and $ev->isBurning()){
			$fuel->pop();
			$this->inventory->setFuel($fuel);
		}
	}

	public function onUpdate() : bool{
		if($this->closed === true){
			return false;
		}

		$this->timings->startTiming();

		$ret = false;

		$fuel = $this->inventory->getFuel();
		$raw = $this->inventory->getSmelting();
		$product = $this->inventory->getResult();
		$smelt = $this->server->getCraftingManager()->matchFurnaceRecipe($raw);
		$canSmelt = ($smelt instanceof FurnaceRecipe and $raw->getCount() > 0 and (($smelt->getResult()->equals($product) and $product->getCount() < $product->getMaxStackSize()) or $product->isNull()));

		if($this->namedtag->BurnTime->getValue() <= 0 and $canSmelt and $fuel->getFuelTime() > 0 and $fuel->getCount() > 0){
			$this->checkFuel($fuel);
		}

		if($this->namedtag->BurnTime->getValue() > 0){
			$this->namedtag->BurnTime->setValue($this->namedtag->BurnTime->getValue() - 1);
			$this->namedtag->BurnTicks->setValue((int) ceil($this->namedtag->BurnTime->getValue() / $this->namedtag->MaxTime->getValue() * 200));

			if($smelt instanceof FurnaceRecipe and $canSmelt){
				$this->namedtag->CookTime->setValue($this->namedtag->CookTime->getValue() + 1);
				if($this->namedtag->CookTime->getValue() >= 200){ //10 seconds
					$product = ItemFactory::get($smelt->getResult()->getId(), $smelt->getResult()->getDamage(), $product->getCount() + 1);

					$this->server->getPluginManager()->callEvent($ev = new FurnaceSmeltEvent($this, $raw, $product));

					if(!$ev->isCancelled()){
						$this->inventory->setResult($ev->getResult());
						$raw->pop();
						$this->inventory->setSmelting($raw);
					}

					$this->namedtag->CookTime->setValue($this->namedtag->CookTime->getValue() - 200);
				}
			}elseif($this->namedtag->BurnTime->getValue() <= 0){
				$this->namedtag->BurnTime->setValue(0);
				$this->namedtag->CookTime->setValue(0);
				$this->namedtag->BurnTicks->setValue(0);
			}else{
				$this->namedtag->CookTime->setValue(0);
			}
			$ret = true;
		}else{
			if($this->getBlock()->getId() === Block::BURNING_FURNACE){
				$this->getLevel()->setBlock($this, BlockFactory::get(Block::FURNACE, $this->getBlock()->getDamage()), true);
			}
			$this->namedtag->BurnTime->setValue(0);
			$this->namedtag->CookTime->setValue(0);
			$this->namedtag->BurnTicks->setValue(0);
		}

		foreach($this->getInventory()->getViewers() as $player){
			$windowId = $player->getWindowId($this->getInventory());
			if($windowId > 0){
				$pk = new ContainerSetDataPacket();
				$pk->windowId = $windowId;
				$pk->property = ContainerSetDataPacket::PROPERTY_FURNACE_TICK_COUNT; //Smelting
				$pk->value = $this->namedtag->CookTime->getValue();
				$player->dataPacket($pk);

				$pk = new ContainerSetDataPacket();
				$pk->windowId = $windowId;
				$pk->property = ContainerSetDataPacket::PROPERTY_FURNACE_LIT_TIME;
				$pk->value = $this->namedtag->BurnTicks->getValue();
				$player->dataPacket($pk);
			}

		}

		$this->lastUpdate = microtime(true);

		$this->timings->stopTiming();

		return $ret;
	}

	public function addAdditionalSpawnData(CompoundTag $nbt){
		$nbt->BurnTime = $this->namedtag->BurnTime;
		$nbt->CookTime = $this->namedtag->CookTime;

		if($this->hasName()){
			$nbt->CustomName = $this->namedtag->CustomName;
		}
	}

	protected static function createAdditionalNBT(CompoundTag $nbt, Vector3 $pos, ?int $face = null, ?Item $item = null, ?Player $player = null) : void{
		$nbt->Items = new ListTag("Items", [], NBT::TAG_Compound);

		if($item !== null and $item->hasCustomName()){
			$nbt->CustomName = new StringTag("CustomName", $item->getCustomName());
		}
	}
}
