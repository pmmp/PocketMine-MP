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


namespace pocketmine\item;


use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\Player;

class Sword extends TieredTool{

	public function getToolType() : int{
		return Tool::TYPE_SWORD;
	}

	public function isSword(){
		return $this->tier;
	}

	public function onDestroyBlock(Block $block, Player $player = null) : bool{
		if($block->getHardness() > 0){
			return $this->applyDamage(2);
		}

		return false;
	}

	public function onAttackEntity(Entity $entity, Player $player = null) : bool{
		return $this->applyDamage(1);
	}
}