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

class Sword extends TieredTool{

	public function getBlockToolType() : int{
		return BlockToolType::TYPE_SWORD;
	}

	public function getAttackPoints() : int{
		return self::getBaseDamageFromTier($this->tier);
	}

	public function getBlockToolHarvestLevel() : int{
		return 1;
	}

	public function getMiningEfficiency(Block $block) : float{
		return parent::getMiningEfficiency($block) * 1.5; //swords break any block 1.5x faster than hand
	}

	protected function getBaseMiningEfficiency() : float{
		return 10;
	}

	public function onDestroyBlock(Block $block) : bool{
		if($block->getHardness() > 0){
			return $this->applyDamage(2);
		}
		return false;
	}

	public function onAttackEntity(Entity $victim) : bool{
		return $this->applyDamage(1);
	}
}
