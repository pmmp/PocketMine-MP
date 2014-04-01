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
use pocketmine\Player;

class Dirt extends Solid{
	public function __construct(){
		parent::__construct(self::DIRT, 0, "Dirt");
		$this->isActivable = true;
		$this->hardness = 2.5;
	}

	public function onActivate(Item $item, Player $player = null){
		if($item->isHoe()){
			$item->useOn($this);
			$this->level->setBlock($this, Block::get(Item::FARMLAND, 0), true, false, true);

			return true;
		}

		return false;
	}
}