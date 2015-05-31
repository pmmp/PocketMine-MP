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

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\level\Level;

class RedstoneOre extends Solid{

	protected $id = self::REDSTONE_ORE;

	public function __construct(){

	}

	public function getName(){
		return "Redstone Ore";
	}

	public function getHardness(){
		return 3;
	}

	public function onUpdate($type){
		if($type === Level::BLOCK_UPDATE_NORMAL or $type === Level::BLOCK_UPDATE_TOUCH){
			$this->getLevel()->setBlock($this, Block::get(Item::GLOWING_REDSTONE_ORE, $this->meta), false, true);

			return Level::BLOCK_UPDATE_WEAK;
		}

		return false;
	}

	public function getDrops(Item $item){
		if($item->isPickaxe() >= 2){
			return [
				[Item::REDSTONE_DUST, 0, mt_rand(4, 5)],
			];
		}else{
			return [];
		}
	}
}