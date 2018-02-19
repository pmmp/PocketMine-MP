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

namespace pocketmine\event\inventory;

use pocketmine\event\block\BlockEvent;
use pocketmine\event\Cancellable;
use pocketmine\item\Item;
use pocketmine\tile\Furnace;

class FurnaceBurnEvent extends BlockEvent implements Cancellable{
	public static $handlerList = null;

	/** @var Furnace */
	private $furnace;
	/** @var Item */
	private $fuel;
	/** @var int */
	private $burnTime;
	/** @var bool */
	private $burning = true;

	/**
	 * @param Furnace $furnace
	 * @param Item $fuel
	 * @param int $burnTime
	 */
	public function __construct(Furnace $furnace, Item $fuel, int $burnTime){
		parent::__construct($furnace->getBlock());
		$this->fuel = $fuel;
		$this->burnTime = $burnTime;
		$this->furnace = $furnace;
	}

	/**
	 * @return Furnace
	 */
	public function getFurnace() : Furnace{
		return $this->furnace;
	}

	/**
	 * @return Item
	 */
	public function getFuel() : Item{
		return $this->fuel;
	}

	/**
	 * @return int
	 */
	public function getBurnTime() : int{
		return $this->burnTime;
	}

	/**
	 * @param int $burnTime
	 */
	public function setBurnTime(int $burnTime){
		$this->burnTime = $burnTime;
	}

	/**
	 * @return bool
	 */
	public function isBurning() : bool{
		return $this->burning;
	}

	/**
	 * @param bool $burning
	 */
	public function setBurning(bool $burning){
		$this->burning = $burning;
	}
}
