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

use pocketmine\block\Block;
use pocketmine\block\Farmland;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;

/**
 * Called when farmland hydration is updated.
 */
class FarmlandHydrationChangeEvent extends BlockEvent implements Cancellable{
	use CancellableTrait;

	public function __construct(
		Block $block,
		private int $oldHydration,
		private int $newHydration,
	){
		parent::__construct($block);
	}

	public function getOldHydration() : int{
		return $this->oldHydration;
	}

	public function getNewHydration() : int{
		return $this->newHydration;
	}

	public function setNewHydration(int $hydration) : void{
		if($hydration < 0 || $hydration > Farmland::MAX_WETNESS){
			throw new \InvalidArgumentException("Hydration must be in range 0 ... " . Farmland::MAX_WETNESS);
		}
		$this->newHydration = $hydration;
	}
}
