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

class TNTBlock extends SolidBlock{
	public function __construct(){
		parent::__construct(TNT, 0, "TNT");
		$this->hardness = 0;
		$this->isActivable = true;
	}
	
	public function onActivate(Item $item, Player $player){
		if($item->getID() === FLINT_STEEL){
			if(($player->gamemode & 0x01) === 0){
				$item->useOn($this);
			}
			$explosion = new Explosion($this->level, $this, 3);
			$explosion->explode();
			return true;
		}
		return false;
	}
}