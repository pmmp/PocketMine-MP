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
use pocketmine\block\utils\CoralTypeTrait;
use pocketmine\block\VanillaBlocks;
use pocketmine\data\bedrock\CoralTypeIdMap;
use pocketmine\math\Axis;
use pocketmine\math\Facing;

final class CoralFan extends Item{
	use CoralTypeTrait;

	public function __construct(private ItemIdentifierFlattened $identifierFlattened){
		parent::__construct($this->identifierFlattened, VanillaBlocks::CORAL_FAN()->getName());
	}

	public function getId() : int{
		return $this->dead ? $this->identifierFlattened->getAdditionalIds()[0] : $this->identifierFlattened->getId();
	}

	public function getMeta() : int{
		return CoralTypeIdMap::getInstance()->toId($this->coralType);
	}

	public function getBlock(?int $clickedFace = null) : Block{
		$block = $clickedFace !== null && Facing::axis($clickedFace) !== Axis::Y ? VanillaBlocks::WALL_CORAL_FAN() : VanillaBlocks::CORAL_FAN();

		return $block->setCoralType($this->coralType)->setDead($this->dead);
	}

	public function getFuelTime() : int{
		return $this->getBlock()->getFuelTime();
	}

	public function getMaxStackSize() : int{
		return $this->getBlock()->getMaxStackSize();
	}
}
