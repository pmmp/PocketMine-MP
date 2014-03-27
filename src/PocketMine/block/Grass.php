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
use PocketMine\Level\Generator\Object\TallGrass;
use PocketMine\Player;
use PocketMine\Utils\Random;

class Grass extends Solid{
	public function __construct(){
		parent::__construct(self::GRASS, 0, "Grass");
		$this->isActivable = true;
		$this->hardness = 3;
	}

	public function getDrops(Item $item){
		return array(
			array(Item::DIRT, 0, 1),
		);
	}

	public function onActivate(Item $item, Player $player = null){
		if($item->getID() === Item::DYE and $item->getMetadata() === 0x0F){
			$item->count--;
			TallGrass::growGrass($this->level, $this, new Random(), 8, 2);

			return true;
		}elseif($item->isHoe()){
			$item->useOn($this);
			$this->level->setBlock($this, new Farmland());

			return true;
		}

		return false;
	}
}