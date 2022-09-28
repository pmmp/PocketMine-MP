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
use pocketmine\block\BlockToolType;
use pocketmine\entity\Entity;

class Shovel extends TieredTool{

	public function getBlockToolType() : int{
		return BlockToolType::SHOVEL;
	}

	public function getBlockToolHarvestLevel() : int{
		return $this->tier->getHarvestLevel();
	}

	public function getAttackPoints() : int{
		return $this->tier->getBaseAttackPoints() - 3;
	}

	public function onDestroyBlock(Block $block) : bool{
		if(!$block->getBreakInfo()->breaksInstantly()){
			return $this->applyDamage(1);
		}
		return false;
	}

	public function onAttackEntity(Entity $victim) : bool{
		return $this->applyDamage(2);
	}
}
