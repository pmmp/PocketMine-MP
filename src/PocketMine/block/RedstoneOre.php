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

namespace PocketMine\Block;

use PocketMine\Item\Item;
use PocketMine\Level\Level;
use PocketMine;

class RedstoneOre extends Solid{
	public function __construct(){
		parent::__construct(self::REDSTONE_ORE, 0, "Redstone Ore");
		$this->hardness = 15;
	}

	public function onUpdate($type){
		if($type === Level::BLOCK_UPDATE_NORMAL or $type === Level::BLOCK_UPDATE_TOUCH){
			$this->level->setBlock($this, Block::get(Item::GLOWING_REDSTONE_ORE, $this->meta), false, false, true);

			return Level::BLOCK_UPDATE_WEAK;
		}

		return false;
	}

	public function getDrops(Item $item, PocketMine\Player $player){
		if($item->isPickaxe() >= 2){
			return array(
				array(Item::REDSTONE_DUST, 0, mt_rand(4, 5)),
			);
		} else{
			return array();
		}
	}
}