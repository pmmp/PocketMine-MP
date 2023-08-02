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

/**
 * Called when farmland wetness is updated.
 */
class FarmlandHydrationChangeEvent extends BlockUpdateEvent{

	public function __construct(
		Block $block,
		private int $oldWetness,
		private int $newWetness,
	){
		parent::__construct($block);
	}

	public function getOldHydration() : int{
		return $this->oldWetness;
	}

	public function getNewHydration() : int{
		return $this->newWetness;
	}

	public function setNewWetness(int $wetness) : void{
		if($wetness < 0 || $wetness > Farmland::MAX_WETNESS){
			throw new \InvalidArgumentException("Wetness must be in range 0 ... " . Farmland::MAX_WETNESS);
		}
		$this->newWetness = $wetness;
	}
}
