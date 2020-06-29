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

use pocketmine\block\inventory\BrewingStandInventory;
use pocketmine\inventory\CallbackInventoryListener;
use pocketmine\inventory\Inventory;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\World;

class BrewingStand extends Spawnable implements Container, Nameable{

	use ContainerTrait;
	use NameableTrait;

	private const TAG_BREW_TIME = "BrewTime"; //TAG_Short
	private const TAG_BREW_TIME_PE = "CookTime"; //TAG_Short
	private const TAG_MAX_FUEL_TIME = "FuelTotal"; //TAG_Short
	private const TAG_REMAINING_FUEL_TIME = "Fuel"; //TAG_Byte
	private const TAG_REMAINING_FUEL_TIME_PE = "FuelAmount"; //TAG_Short

	/** @var BrewingStandInventory */
	private $inventory;

	/** @var int */
	private $brewTime = 0;
	/** @var int */
	private $maxFuelTime = 0;
	/** @var int */
	private $remainingFuelTime = 0;

	public function __construct(World $world, Vector3 $pos){
		parent::__construct($world, $pos);
		$this->inventory = new BrewingStandInventory($this->pos);
		$this->inventory->getListeners()->add(CallbackInventoryListener::onAnyChange(function(Inventory $unused) : void{
			$this->pos->getWorld()->scheduleDelayedBlockUpdate($this->pos, 1);
		}));
	}

	public function readSaveData(CompoundTag $nbt) : void{
		$this->loadName($nbt);
		$this->loadItems($nbt);

		$this->brewTime = $nbt->getShort(self::TAG_BREW_TIME, $nbt->getShort(self::TAG_BREW_TIME_PE, 0));
		$this->maxFuelTime = $nbt->getShort(self::TAG_MAX_FUEL_TIME, 0);
		$this->remainingFuelTime = $nbt->getByte(self::TAG_REMAINING_FUEL_TIME, $nbt->getShort(self::TAG_REMAINING_FUEL_TIME_PE, 0));
		if($this->maxFuelTime === 0){
			$this->maxFuelTime = $this->remainingFuelTime;
		}
		if($this->remainingFuelTime === 0){
			$this->maxFuelTime = $this->remainingFuelTime = $this->brewTime = 0;
		}
	}

	protected function writeSaveData(CompoundTag $nbt) : void{
		$this->saveName($nbt);
		$this->saveItems($nbt);

		$nbt->setShort(self::TAG_BREW_TIME_PE, $this->brewTime);
		$nbt->setShort(self::TAG_MAX_FUEL_TIME, $this->maxFuelTime);
		$nbt->setShort(self::TAG_REMAINING_FUEL_TIME_PE, $this->remainingFuelTime);
	}

	public function getDefaultName() : string{
		return "Brewing Stand";
	}

	public function close() : void{
		if(!$this->closed){
			$this->inventory->removeAllViewers();
			$this->inventory = null;

			parent::close();
		}
	}

	/**
	 * @return BrewingStandInventory
	 */
	public function getInventory(){
		return $this->inventory;
	}

	/**
	 * @return BrewingStandInventory
	 */
	public function getRealInventory(){
		return $this->inventory;
	}
}
