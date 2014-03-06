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

use PocketMine;

class Planks extends Solid{
	public function __construct($meta = 0){
		parent::__construct(PLANKS, $meta, "Wooden Planks");
		$names = array(
			WoodBlock::OAK => "Oak Wooden Planks",
			WoodBlock::SPRUCE => "Spruce Wooden Planks",
			WoodBlock::BIRCH => "Birch Wooden Planks",
			WoodBlock::JUNGLE => "Jungle Wooden Planks",
		);
		$this->name = $names[$this->meta & 0x03];
		$this->hardness = 15;
	}

}