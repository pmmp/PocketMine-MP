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
use pocketmine\crafting\FurnaceType;
use pocketmine\event\inventory\FurnaceBurnEvent;
use pocketmine\event\inventory\FurnaceSmeltEvent;
use pocketmine\inventory\CallbackInventoryListener;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\ContainerSetDataPacket;
use pocketmine\player\Player;
use pocketmine\world\World;
use function array_map;
use function max;

abstract class Furnace extends Spawnable implements Container, Nameable{
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
		$this->inventory = new FurnaceInventory($this->position, $this->getFurnaceType());
		$this->inventory->getListeners()->add(CallbackInventoryListener::onAnyChange(
			static function(Inventory $unused) use ($world, $pos) : void{
				$world->scheduleDelayedBlockUpdate($pos, 1);
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

		if($this->remainingFuelTime > 0){
			$this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, 1);
		}
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
		$this->onStartSmelting();

		if($this->remainingFuelTime > 0 and $ev->isBurning()){
			$this->inventory->setFuel($fuel->getFuelResidue());
		}
	}

	protected function onStartSmelting() : void{
		$block = $this->getBlock();
		if($block instanceof BlockFurnace and !$block->isLit()){
			$block->setLit(true);
			$this->position->getWorld()->setBlock($block->getPosition(), $block);
		}
	}

	protected function onStopSmelting() : void{
		$block = $this->getBlock();
		if($block instanceof BlockFurnace and $block->isLit()){
			$block->setLit(false);
			$this->position->getWorld()->setBlock($block->getPosition(), $block);
		}
	}

	abstract public function getFurnaceType() : FurnaceType;

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

		$furnaceType = $this->getFurnaceType();
		$smelt = $this->position->getWorld()->getServer()->getCraftingManager()->getFurnaceRecipeManager($furnaceType)->match($raw);
		$canSmelt = ($smelt instanceof FurnaceRecipe and $raw->getCount() > 0 and (($smelt->getResult()->equals($product) and $product->getCount() < $product->getMaxStackSize()) or $product->isNull()));

		if($this->remainingFuelTime <= 0 and $canSmelt and $fuel->getFuelTime() > 0 and $fuel->getCount() > 0){
			$this->checkFuel($fuel);
		}

		if($this->remainingFuelTime > 0){
			--$this->remainingFuelTime;

			if($smelt instanceof FurnaceRecipe and $canSmelt){
				++$this->cookTime;

				if($this->cookTime >= $furnaceType->getCookDurationTicks()){
					$product = $smelt->getResult()->setCount($product->getCount() + 1);

					$ev = new FurnaceSmeltEvent($this, $raw, $product);
					$ev->call();

					if(!$ev->isCancelled()){
						$this->inventory->setResult($ev->getResult());
						$raw->pop();
						$this->inventory->setSmelting($raw);
					}

					$this->cookTime -= $furnaceType->getCookDurationTicks();
				}
			}elseif($this->remainingFuelTime <= 0){
				$this->remainingFuelTime = $this->cookTime = $this->maxFuelTime = 0;
			}else{
				$this->cookTime = 0;
			}
			$ret = true;
		}else{
			$this->onStopSmelting();
			$this->remainingFuelTime = $this->cookTime = $this->maxFuelTime = 0;
		}

		$viewers = array_map(fn(Player $p) => $p->getNetworkSession()->getInvManager(), $this->inventory->getViewers());
		foreach($viewers as $v){
			if($v === null){
				continue;
			}
			if($prevCookTime !== $this->cookTime){
				$v->syncData($this->inventory, ContainerSetDataPacket::PROPERTY_FURNACE_SMELT_PROGRESS, $this->cookTime);
			}
			if($prevRemainingFuelTime !== $this->remainingFuelTime){
				$v->syncData($this->inventory, ContainerSetDataPacket::PROPERTY_FURNACE_REMAINING_FUEL_TIME, $this->remainingFuelTime);
			}
			if($prevMaxFuelTime !== $this->maxFuelTime){
				$v->syncData($this->inventory, ContainerSetDataPacket::PROPERTY_FURNACE_MAX_FUEL_TIME, $this->maxFuelTime);
			}
		}

		$this->timings->stopTiming();

		return $ret;
	}
}
