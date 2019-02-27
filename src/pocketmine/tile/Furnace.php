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

use pocketmine\block\Furnace as BlockFurnace;
use pocketmine\event\inventory\FurnaceBurnEvent;
use pocketmine\event\inventory\FurnaceSmeltEvent;
use pocketmine\inventory\FurnaceInventory;
use pocketmine\inventory\FurnaceRecipe;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\InventoryHolder;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\ContainerSetDataPacket;
use function ceil;
use function max;

class Furnace extends Spawnable implements InventoryHolder, Container, Nameable{
	use NameableTrait {
		addAdditionalSpawnData as addNameSpawnData;
	}
	use ContainerTrait;

	public const TAG_BURN_TIME = "BurnTime";
	public const TAG_COOK_TIME = "CookTime";
	public const TAG_MAX_TIME = "MaxTime";

	/** @var FurnaceInventory */
	protected $inventory;
	/** @var int */
	private $burnTime = 0;
	/** @var int */
	private $cookTime = 0;
	/** @var int */
	private $maxTime = 0;

	public function __construct(Level $level, Vector3 $pos){
		$this->inventory = new FurnaceInventory($this);
		$this->inventory->setSlotChangeListener(function(Inventory $inventory, int $slot, Item $oldItem, Item $newItem) : ?Item{
			$this->scheduleUpdate();
			return $newItem;
		});

		parent::__construct($level, $pos);
	}

	public function readSaveData(CompoundTag $nbt) : void{
		$this->burnTime = max(0, $nbt->getShort(self::TAG_BURN_TIME, $this->burnTime, true));

		$this->cookTime = $nbt->getShort(self::TAG_COOK_TIME, $this->cookTime, true);
		if($this->burnTime === 0){
			$this->cookTime = 0;
		}

		$this->maxTime = $nbt->getShort(self::TAG_MAX_TIME, $this->maxTime, true);
		if($this->maxTime === 0){
			$this->maxTime = $this->burnTime;
		}

		$this->loadName($nbt);
		$this->loadItems($nbt);
	}

	protected function writeSaveData(CompoundTag $nbt) : void{
		$nbt->setShort(self::TAG_BURN_TIME, $this->burnTime);
		$nbt->setShort(self::TAG_COOK_TIME, $this->cookTime);
		$nbt->setShort(self::TAG_MAX_TIME, $this->maxTime);
		$this->saveName($nbt);
		$this->saveItems($nbt);
	}

	/**
	 * @return string
	 */
	public function getDefaultName() : string{
		return "Furnace";
	}

	public function close() : void{
		if(!$this->closed){
			$this->inventory->removeAllViewers(true);
			$this->inventory = null;

			parent::close();
		}
	}

	/**
	 * @return FurnaceInventory
	 */
	public function getInventory(){
		return $this->inventory;
	}

	/**
	 * @return FurnaceInventory
	 */
	public function getRealInventory(){
		return $this->getInventory();
	}

	protected function checkFuel(Item $fuel){
		$ev = new FurnaceBurnEvent($this, $fuel, $fuel->getFuelTime());
		$ev->call();
		if($ev->isCancelled()){
			return;
		}

		$this->maxTime = $this->burnTime = $ev->getBurnTime();

		$block = $this->getBlock();
		if($block instanceof BlockFurnace and !$block->isLit()){
			$block->setLit(true);
			$this->getLevel()->setBlock($block, $block);
		}

		if($this->burnTime > 0 and $ev->isBurning()){
			$fuel->pop();
			$this->inventory->setFuel($fuel);
		}
	}

	protected function getFuelTicksLeft() : int{
		return $this->maxTime > 0 ? (int) ceil($this->burnTime / $this->maxTime * 200) : 0;
	}

	public function onUpdate() : bool{
		if($this->closed){
			return false;
		}

		$this->timings->startTiming();

		$prevCookTime = $this->cookTime;
		$prevFuelTicksLeft = $this->getFuelTicksLeft();

		$ret = false;

		$fuel = $this->inventory->getFuel();
		$raw = $this->inventory->getSmelting();
		$product = $this->inventory->getResult();
		$smelt = $this->level->getServer()->getCraftingManager()->matchFurnaceRecipe($raw);
		$canSmelt = ($smelt instanceof FurnaceRecipe and $raw->getCount() > 0 and (($smelt->getResult()->equals($product) and $product->getCount() < $product->getMaxStackSize()) or $product->isNull()));

		if($this->burnTime <= 0 and $canSmelt and $fuel->getFuelTime() > 0 and $fuel->getCount() > 0){
			$this->checkFuel($fuel);
		}

		if($this->burnTime > 0){
			--$this->burnTime;

			if($smelt instanceof FurnaceRecipe and $canSmelt){
				++$this->cookTime;

				if($this->cookTime >= 200){ //10 seconds
					$product = ItemFactory::get($smelt->getResult()->getId(), $smelt->getResult()->getMeta(), $product->getCount() + 1);

					$ev = new FurnaceSmeltEvent($this, $raw, $product);
					$ev->call();

					if(!$ev->isCancelled()){
						$this->inventory->setResult($ev->getResult());
						$raw->pop();
						$this->inventory->setSmelting($raw);
					}

					$this->cookTime -= 200;
				}
			}elseif($this->burnTime <= 0){
				$this->burnTime = $this->cookTime = $this->maxTime = 0;
			}else{
				$this->cookTime = 0;
			}
			$ret = true;
		}else{
			$block = $this->getBlock();
			if($block instanceof BlockFurnace and $block->isLit()){
				$block->setLit(false);
				$this->getLevel()->setBlock($block, $block);
			}
			$this->burnTime = $this->cookTime = $this->maxTime = 0;
		}

		/** @var ContainerSetDataPacket[] $packets */
		$packets = [];
		if($prevCookTime !== $this->cookTime){
			$pk = new ContainerSetDataPacket();
			$pk->property = ContainerSetDataPacket::PROPERTY_FURNACE_TICK_COUNT;
			$pk->value = $this->cookTime;
			$packets[] = $pk;
		}

		$fuelTicksLeft = $this->getFuelTicksLeft();
		if($prevFuelTicksLeft !== $fuelTicksLeft){
			$pk = new ContainerSetDataPacket();
			$pk->property = ContainerSetDataPacket::PROPERTY_FURNACE_LIT_TIME;
			$pk->value = $fuelTicksLeft;
			$packets[] = $pk;
		}

		if(!empty($packets)){
			foreach($this->getInventory()->getViewers() as $player){
				$windowId = $player->getWindowId($this->getInventory());
				if($windowId > 0){
					foreach($packets as $pk){
						$pk->windowId = $windowId;
						$player->sendDataPacket(clone $pk);
					}
				}
			}
		}

		$this->timings->stopTiming();

		return $ret;
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void{
		$nbt->setShort(self::TAG_BURN_TIME, $this->burnTime);
		$nbt->setShort(self::TAG_COOK_TIME, $this->cookTime);

		$this->addNameSpawnData($nbt);
	}
}
