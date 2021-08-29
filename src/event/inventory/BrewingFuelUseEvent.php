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

use pocketmine\block\tile\BrewingStand;
use pocketmine\event\block\BlockEvent;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;

class BrewingFuelUseEvent extends BlockEvent implements Cancellable{
	use CancellableTrait;

	/** @var BrewingStand */
	private $brewingStand;
	/** @var int */
	private $fuelTime = 20;

	public function __construct(BrewingStand $brewingStand){
		parent::__construct($brewingStand->getBlock());
		$this->brewingStand = $brewingStand;
	}

	public function getBrewingStand() : BrewingStand{
		return $this->brewingStand;
	}

	/**
	 * Returns how many brewing steps could be made with the fuel
	 */
	public function getFuelTime() : int{
		return $this->fuelTime;
	}

	/**
	 * Sets how many brewing steps could be made with the fuel
	 */
	public function setFuelTime(int $fuelTime) : void{
		$this->fuelTime = $fuelTime;
	}
}