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

class BucketItem extends Item{
	public function __construct($meta = 0, $count = 1){
		parent::__construct(BUCKET, $meta, $count, "Bucket");
		$this->isActivable = true;
		$this->maxStackSize = 1;
	}
	
	public function onActivate(Level $level, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		if($this->meta === AIR){
			if($target instanceof LiquidBlock){
				$level->setBlock($target, new AirBlock());
				if(($player->gamemode & 0x01) === 0){
					$this->meta = ($target instanceof WaterBlock) ? WATER:LAVA;
				}
				return true;
			}
		}elseif($this->meta === WATER){
			if($block->getID() === AIR){
				$level->setBlock($block, new StillWaterBLock());
				if(($player->gamemode & 0x01) === 0){
					$this->meta = 0;
				}
				return true;
			}
		}elseif($this->meta === LAVA){
			if($block->getID() === AIR){
				$level->setBlock($block, new StillLavaBlock());
				if(($player->gamemode & 0x01) === 0){
					$this->meta = 0;
				}
				return true;
			}
		}
		return false;
	}
}