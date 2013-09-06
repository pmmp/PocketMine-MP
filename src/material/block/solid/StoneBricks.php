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

class StoneBricksBlock extends SolidBlock{
	public function __construct($meta = 0){
		parent::__construct(STONE_BRICKS, $meta, "Stone Bricks");
		$names = array(
			0 => "Stone Bricks",
			1 => "Mossy Stone Bricks",
			2 => "Cracked Stone Bricks",
			3 => "Chiseled Stone Bricks",
		);
		$this->name = $names[$this->meta & 0x03];
		$this->hardness = 30;
	}

	public function getBreakTime(Item $item, Player $player){
		if(($player->gamemode & 0x01) === 0x01){
			return 0.20;
		}		
		switch($item->isPickaxe()){
			case 5:
				return 0.4;
			case 4:
				return 0.5;
			case 3:
				return 0.75;
			case 2:
				return 0.25;
			case 1:
				return 1.5;
			default:
				return 7.5;
		}
	}
	
	public function getDrops(Item $item, Player $player){
		if($item->isPickaxe() >= 1){
			return array(
				array(STONE_BRICKS, $this->meta & 0x03, 1),
			);
		}else{
			return array();
		}
	}
	
}