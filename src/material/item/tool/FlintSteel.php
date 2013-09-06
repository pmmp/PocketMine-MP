<?php

/**
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

class FlintSteelItem extends Item{
	public function __construct($meta = 0, $count = 1){
		parent::__construct(FLINT_STEEL, $meta, $count, "Flint and Steel");
		$this->isActivable = true;
		$this->maxStackSize = 1;
	}
	
	public function onActivate(Level $level, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		if(($player->gamemode & 0x01) === 0 and $this->useOn($block) and $this->getMetadata() >= $this->getMaxDurability()){
			$player->setSlot($player->slot, new Item(AIR, 0, 0), false);
		}

		if($block->getID() === AIR and ($target instanceof SolidBlock)){
			$level->setBlock($block, new FireBlock(), true, false, true);
			$block->level->scheduleBlockUpdate(new Position($block, 0, 0, $block->level), Utils::getRandomUpdateTicks(), BLOCK_UPDATE_RANDOM);
			return true;
		}
		return false;
	}
}