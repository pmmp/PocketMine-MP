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

namespace pocketmine\event\block;

use pocketmine\block\tile\BrewingStand;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;

/**
 * Called when a brewing stand consumes a new fuel item.
 */
class BrewingFuelUseEvent extends BlockEvent implements Cancellable{
	use CancellableTrait;

	private int $fuelTime = 20;

	public function __construct(
		private BrewingStand $brewingStand
	){
		parent::__construct($brewingStand->getBlock());
	}

	public function getBrewingStand() : BrewingStand{
		return $this->brewingStand;
	}

	/**
	 * Returns how many times the fuel can be used for potion brewing before it runs out.
	 */
	public function getFuelTime() : int{
		return $this->fuelTime;
	}

	/**
	 * Sets how many times the fuel can be used for potion brewing before it runs out.
	 */
	public function setFuelTime(int $fuelTime) : void{
		if($fuelTime <= 0){
			throw new \InvalidArgumentException("Fuel time must be positive");
		}
		$this->fuelTime = $fuelTime;
	}
}
