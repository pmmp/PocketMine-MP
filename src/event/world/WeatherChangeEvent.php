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

namespace pocketmine\event\world;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\world\World;

class WeatherChangeEvent extends WorldEvent implements Cancellable{
	use CancellableTrait;

	public function __construct(
		private World $world,
		private float $oldRainLevel,
		private float $oldThunderLevel,
		private float $newRainLevel,
		private float $newThunderLevel
	){
		parent::__construct($world);
	}

	public function getWorld() : World{ return $this->world; }

	public function getOldRainLevel() : float{ return $this->oldRainLevel; }
	public function getOldThunderLevel() : float{ return $this->oldThunderLevel; }

	public function getNewRainLevel() : float{ return $this->newRainLevel; }
	public function getNewThunderLevel() : float{ return $this->newThunderLevel; }

	public function setNewRainLevel(float $rainLevel) : void{ $this->newRainLevel = $rainLevel; }
	public function setNewThunderLevel(float $thunderLevel) : void{ $this->newThunderLevel = $thunderLevel; }
}
