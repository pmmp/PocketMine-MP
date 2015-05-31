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

use pocketmine\event\block\BlockSpreadEvent;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\Server;

class Mycelium extends Solid{

	protected $id = self::MYCELIUM;

	public function __construct(){

	}

	public function getName(){
		return "Mycelium";
	}

	public function getHardness(){
		return 0.6;
	}

	public function getDrops(Item $item){
		return [
			[Item::DIRT, 0, 1],
		];
	}

	public function onUpdate($type){
		if($type === Level::BLOCK_UPDATE_RANDOM){
			//TODO: light levels
			$x = mt_rand($this->x - 1, $this->x + 1);
			$y = mt_rand($this->y - 2, $this->y + 2);
			$z = mt_rand($this->z - 1, $this->z + 1);
			$block = $this->getLevel()->getBlock(new Vector3($x, $y, $z));
			if($block->getId() === Block::DIRT){
				if($block->getSide(1) instanceof Transparent){
					Server::getInstance()->getPluginManager()->callEvent($ev = new BlockSpreadEvent($block, $this, new Mycelium()));
					if(!$ev->isCancelled()){
						$this->getLevel()->setBlock($block, $ev->getNewState());
					}
				}
			}
		}
	}
}
