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
use pocketmine\network\mcpe\protocol\ContainerSetDataPacket;
use pocketmine\Player;

class Furnace extends Spawnable implements InventoryHolder, Container, Nameable{
	use NameableTrait;

	const TAG_BURN_TIME = "BurnTime";
	const TAG_COOK_TIME = "CookTime";
	const TAG_MAX_TIME = "MaxTime";
	const TAG_BURN_TICKS = "BurnTicks";

	/** @var FurnaceInventory */
	protected $inventory;

	public function __construct(Level $level, CompoundTag $nbt){
		if(!$nbt->hasTag(self::TAG_BURN_TIME, ShortTag::class) or $nbt->getShort(self::TAG_BURN_TIME) < 0){
			$nbt->setTag(new ShortTag(self::TAG_BURN_TIME, 0));
		}

		if(
			!$nbt->hasTag(self::TAG_COOK_TIME, ShortTag::class) or
			$nbt->getShort(self::TAG_COOK_TIME) < 0 or
			($nbt->getShort(self::TAG_BURN_TIME) === 0 and $nbt->getShort(self::TAG_COOK_TIME) > 0)
		){
			$nbt->setTag(new ShortTag(self::TAG_COOK_TIME, 0));
		}

		if(!$nbt->hasTag(self::TAG_MAX_TIME, ShortTag::class)){
			$nbt->setTag(new ShortTag(self::TAG_MAX_TIME, $nbt->getShort(self::TAG_BURN_TIME)));
			$nbt->removeTag(self::TAG_BURN_TICKS);
		}

		if(!$nbt->getTag(self::TAG_BURN_TICKS, ShortTag::class)){
			$nbt->setTag(new ShortTag(self::TAG_BURN_TICKS, 0));
		}

		parent::__construct($level, $nbt);
		$this->inventory = new FurnaceInventory($this);

		if(!($this->namedtag->getTag("Items") instanceof ListTag)){
			$this->namedtag->setTag(new ListTag("Items", [], NBT::TAG_Compound));
		}

		for($i = 0; $i < $this->getSize(); ++$i){
			$this->inventory->setItem($i, $this->getItem($i), false);
		}

		if($this->namedtag->getShort(self::TAG_BURN_TIME) > 0){
			$this->scheduleUpdate();
		}
	}

	/**
	 * @return string
	 */
	public function getDefaultName() : string{
		return "Furnace";
	}

	public function close() : void{
		if($this->closed === false){
			$this->inventory->removeAllViewers(true);
			$this->inventory = null;

			parent::close();
		}
	}

	public function saveNBT() : void{
		parent::saveNBT();
		$this->namedtag->setTag(new ListTag("Items", [], NBT::TAG_Compound));
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
		foreach($this->namedtag->getListTag("Items") as $i => $slot){
			/** @var CompoundTag $slot */
			if($slot->getByte("Slot") === $index){
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
			return Item::nbtDeserialize($this->namedtag->getListTag("Items")[$i]);
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

		$items = $this->namedtag->getListTag("Items");
		assert($items instanceof ListTag);

		if($item->isNull()){
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

		$this->namedtag->setShort(self::TAG_MAX_TIME, $ev->getBurnTime());
		$this->namedtag->setShort(self::TAG_BURN_TIME, $ev->getBurnTime());
		$this->namedtag->setShort(self::TAG_BURN_TICKS, 0);
		if($this->getBlock()->getId() === Block::FURNACE){
			$this->getLevel()->setBlock($this, BlockFactory::get(Block::BURNING_FURNACE, $this->getBlock()->getDamage()), true);
		}

		if($this->namedtag->getShort(self::TAG_BURN_TIME) > 0 and $ev->isBurning()){
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

		if($this->namedtag->getShort(self::TAG_BURN_TIME) <= 0 and $canSmelt and $fuel->getFuelTime() > 0 and $fuel->getCount() > 0){
			$this->checkFuel($fuel);
		}

		if($this->namedtag->getShort(self::TAG_BURN_TIME) > 0){
			$this->namedtag->setShort(self::TAG_BURN_TIME, $this->namedtag->getShort(self::TAG_BURN_TIME) - 1);
			$this->namedtag->setShort(self::TAG_BURN_TICKS, (int) ceil($this->namedtag->getShort(self::TAG_BURN_TIME) / $this->namedtag->getShort(self::TAG_MAX_TIME) * 200));

			if($smelt instanceof FurnaceRecipe and $canSmelt){
				$this->namedtag->setShort(self::TAG_COOK_TIME, $this->namedtag->getShort(self::TAG_COOK_TIME) + 1);
				if($this->namedtag->getShort(self::TAG_COOK_TIME) >= 200){ //10 seconds
					$product = ItemFactory::get($smelt->getResult()->getId(), $smelt->getResult()->getDamage(), $product->getCount() + 1);

					$this->server->getPluginManager()->callEvent($ev = new FurnaceSmeltEvent($this, $raw, $product));

					if(!$ev->isCancelled()){
						$this->inventory->setResult($ev->getResult());
						$raw->pop();
						$this->inventory->setSmelting($raw);
					}

					$this->namedtag->setShort(self::TAG_COOK_TIME, $this->namedtag->getShort(self::TAG_COOK_TIME) - 200);
				}
			}elseif($this->namedtag->getShort(self::TAG_BURN_TIME) <= 0){
				$this->namedtag->setShort(self::TAG_BURN_TIME, 0);
				$this->namedtag->setShort(self::TAG_COOK_TIME, 0);
				$this->namedtag->setShort(self::TAG_BURN_TICKS, 0);
			}else{
				$this->namedtag->setShort(self::TAG_COOK_TIME, 0);
			}
			$ret = true;
		}else{
			if($this->getBlock()->getId() === Block::BURNING_FURNACE){
				$this->getLevel()->setBlock($this, BlockFactory::get(Block::FURNACE, $this->getBlock()->getDamage()), true);
			}
			$this->namedtag->setShort(self::TAG_BURN_TIME, 0);
			$this->namedtag->setShort(self::TAG_COOK_TIME, 0);
			$this->namedtag->setShort(self::TAG_BURN_TICKS, 0);
		}

		foreach($this->getInventory()->getViewers() as $player){
			$windowId = $player->getWindowId($this->getInventory());
			if($windowId > 0){
				$pk = new ContainerSetDataPacket();
				$pk->windowId = $windowId;
				$pk->property = ContainerSetDataPacket::PROPERTY_FURNACE_TICK_COUNT; //Smelting
				$pk->value = $this->namedtag->getShort(self::TAG_COOK_TIME);
				$player->dataPacket($pk);

				$pk = new ContainerSetDataPacket();
				$pk->windowId = $windowId;
				$pk->property = ContainerSetDataPacket::PROPERTY_FURNACE_LIT_TIME;
				$pk->value = $this->namedtag->getShort(self::TAG_BURN_TICKS);
				$player->dataPacket($pk);
			}
		}

		$this->timings->stopTiming();

		return $ret;
	}

	public function addAdditionalSpawnData(CompoundTag $nbt) : void{
		$nbt->setTag($this->namedtag->getTag(self::TAG_BURN_TIME));
		$nbt->setTag($this->namedtag->getTag(self::TAG_COOK_TIME));

		if($this->hasName()){
			$nbt->setTag($this->namedtag->getTag("CustomName"));
		}
	}

	protected static function createAdditionalNBT(CompoundTag $nbt, Vector3 $pos, ?int $face = null, ?Item $item = null, ?Player $player = null) : void{
		$nbt->setTag(new ListTag("Items", [], NBT::TAG_Compound));

		if($item !== null and $item->hasCustomName()){
			$nbt->setString("CustomName", $item->getCustomName());
		}
	}
}
