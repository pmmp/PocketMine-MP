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

use pocketmine\block\Furnace as BlockFurnace;
use pocketmine\block\inventory\FurnaceInventory;
use pocketmine\crafting\FurnaceRecipe;
use pocketmine\event\inventory\FurnaceBurnEvent;
use pocketmine\event\inventory\FurnaceSmeltEvent;
use pocketmine\inventory\CallbackInventoryListener;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\ContainerSetDataPacket;
use pocketmine\world\World;
use function max;

class Furnace extends Spawnable implements Container, Nameable{
	use NameableTrait;
	use ContainerTrait;

	public const TAG_BURN_TIME = "BurnTime";
	public const TAG_COOK_TIME = "CookTime";
	public const TAG_MAX_TIME = "MaxTime";

	/** @var FurnaceInventory */
	protected $inventory;
	/** @var int */
	private $remainingFuelTime = 0;
	/** @var int */
	private $cookTime = 0;
	/** @var int */
	private $maxFuelTime = 0;

	public function __construct(World $world, Vector3 $pos){
		parent::__construct($world, $pos);
		$this->inventory = new FurnaceInventory($this->pos);
		$this->inventory->getListeners()->add(CallbackInventoryListener::onAnyChange(
			function(Inventory $unused) : void{
				$this->pos->getWorld()->scheduleDelayedBlockUpdate($this->pos, 1);
			})
		);
	}

	public function readSaveData(CompoundTag $nbt) : void{
		$this->remainingFuelTime = max(0, $nbt->getShort(self::TAG_BURN_TIME, $this->remainingFuelTime));

		$this->cookTime = $nbt->getShort(self::TAG_COOK_TIME, $this->cookTime);
		if($this->remainingFuelTime === 0){
			$this->cookTime = 0;
		}

		$this->maxFuelTime = $nbt->getShort(self::TAG_MAX_TIME, $this->maxFuelTime);
		if($this->maxFuelTime === 0){
			$this->maxFuelTime = $this->remainingFuelTime;
		}

		$this->loadName($nbt);
		$this->loadItems($nbt);
	}

	protected function writeSaveData(CompoundTag $nbt) : void{
		$nbt->setShort(self::TAG_BURN_TIME, $this->remainingFuelTime);
		$nbt->setShort(self::TAG_COOK_TIME, $this->cookTime);
		$nbt->setShort(self::TAG_MAX_TIME, $this->maxFuelTime);
		$this->saveName($nbt);
		$this->saveItems($nbt);
	}

	public function getDefaultName() : string{
		return "Furnace";
	}

	public function close() : void{
		if(!$this->closed){
			$this->inventory->removeAllViewers();
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

	protected function checkFuel(Item $fuel) : void{
		$ev = new FurnaceBurnEvent($this, $fuel, $fuel->getFuelTime());
		$ev->call();
		if($ev->isCancelled()){
			return;
		}

		$this->maxFuelTime = $this->remainingFuelTime = $ev->getBurnTime();

		$block = $this->getBlock();
		if($block instanceof BlockFurnace and !$block->isLit()){
			$block->setLit(true);
			$this->pos->getWorld()->setBlock($block->getPos(), $block);
		}

		if($this->remainingFuelTime > 0 and $ev->isBurning()){
			$this->inventory->setFuel($fuel->getFuelResidue());
		}
	}

	public function onUpdate() : bool{
		//TODO: move this to Block
		if($this->closed){
			return false;
		}

		$this->timings->startTiming();

		$prevCookTime = $this->cookTime;
		$prevRemainingFuelTime = $this->remainingFuelTime;
		$prevMaxFuelTime = $this->maxFuelTime;

		$ret = false;

		$fuel = $this->inventory->getFuel();
		$raw = $this->inventory->getSmelting();
		$product = $this->inventory->getResult();
		$smelt = $this->pos->getWorld()->getServer()->getCraftingManager()->getFurnaceRecipeManager()->match($raw);
		$canSmelt = ($smelt instanceof FurnaceRecipe and $raw->getCount() > 0 and (($smelt->getResult()->equals($product) and $product->getCount() < $product->getMaxStackSize()) or $product->isNull()));

		if($this->remainingFuelTime <= 0 and $canSmelt and $fuel->getFuelTime() > 0 and $fuel->getCount() > 0){
			$this->checkFuel($fuel);
		}

		if($this->remainingFuelTime > 0){
			--$this->remainingFuelTime;

			if($smelt instanceof FurnaceRecipe and $canSmelt){
				++$this->cookTime;

				if($this->cookTime >= 200){ //10 seconds
					$product = $smelt->getResult()->setCount($product->getCount() + 1);

					$ev = new FurnaceSmeltEvent($this, $raw, $product);
					$ev->call();

					if(!$ev->isCancelled()){
						$this->inventory->setResult($ev->getResult());
						$raw->pop();
						$this->inventory->setSmelting($raw);
					}

					$this->cookTime -= 200;
				}
			}elseif($this->remainingFuelTime <= 0){
				$this->remainingFuelTime = $this->cookTime = $this->maxFuelTime = 0;
			}else{
				$this->cookTime = 0;
			}
			$ret = true;
		}else{
			$block = $this->getBlock();
			if($block instanceof BlockFurnace and $block->isLit()){
				$block->setLit(false);
				$this->pos->getWorld()->setBlock($block->getPos(), $block);
			}
			$this->remainingFuelTime = $this->cookTime = $this->maxFuelTime = 0;
		}

		if($prevCookTime !== $this->cookTime){
			foreach($this->inventory->getViewers() as $v){
				$v->getNetworkSession()->getInvManager()->syncData($this->inventory, ContainerSetDataPacket::PROPERTY_FURNACE_SMELT_PROGRESS, $this->cookTime);
			}
		}
		if($prevRemainingFuelTime !== $this->remainingFuelTime){
			foreach($this->inventory->getViewers() as $v){
				$v->getNetworkSession()->getInvManager()->syncData($this->inventory, ContainerSetDataPacket::PROPERTY_FURNACE_REMAINING_FUEL_TIME, $this->remainingFuelTime);
			}
		}
		if($prevMaxFuelTime !== $this->maxFuelTime){
			foreach($this->inventory->getViewers() as $v){
				$v->getNetworkSession()->getInvManager()->syncData($this->inventory, ContainerSetDataPacket::PROPERTY_FURNACE_MAX_FUEL_TIME, $this->maxFuelTime);
			}
		}

		$this->timings->stopTiming();

		return $ret;
	}
}
