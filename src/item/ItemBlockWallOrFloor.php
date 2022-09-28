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

namespace pocketmine\item;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\math\Axis;
use pocketmine\math\Facing;

class ItemBlockWallOrFloor extends Item{
	private int $floorVariant;
	private int $wallVariant;

	public function __construct(ItemIdentifier $identifier, Block $floorVariant, Block $wallVariant){
		parent::__construct($identifier, $floorVariant->getName());
		$this->floorVariant = $floorVariant->getFullId();
		$this->wallVariant = $wallVariant->getFullId();
	}

	public function getBlock(?int $clickedFace = null) : Block{
		if($clickedFace !== null && Facing::axis($clickedFace) !== Axis::Y){
			return BlockFactory::getInstance()->fromFullBlock($this->wallVariant);
		}
		return BlockFactory::getInstance()->fromFullBlock($this->floorVariant);
	}

	public function getFuelTime() : int{
		return $this->getBlock()->getFuelTime();
	}

	public function getMaxStackSize() : int{
		return $this->getBlock()->getMaxStackSize();
	}
}
