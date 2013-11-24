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

class CakeBlock extends TransparentBlock{
	public function __construct($meta = 0){
		parent::__construct(CAKE_BLOCK, 0, "Cake Block");
		$this->isFullBlock = false;
		$this->isActivable = true;
		$this->meta = $meta & 0x07;
		$this->hardness = 2.5;
	}
	
	public function place(Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		$down = $this->getSide(0);
		if($down->getID() !== AIR){
			$this->level->setBlock($block, $this, true, false, true);
			return true;
		}
		return false;
	}
	
	public function onUpdate($type){
		if($type === BLOCK_UPDATE_NORMAL){
			if($this->getSide(0)->getID() === AIR){ //Replace with common break method
				$this->level->setBlock($this, new AirBlock(), true, false, true);
				return BLOCK_UPDATE_NORMAL;
			}
		}
		return false;
	}
	
	public function getDrops(Item $item, Player $player){
		return array();
	}
	
	public function onActivate(Item $item, Player $player){
		if($player->entity->getHealth() < 20){
			++$this->meta;
			$player->entity->heal(3, "cake");
			if($this->meta >= 0x06){
				$this->level->setBlock($this, new AirBlock(), true, false, true);
			}else{
				$this->level->setBlock($this, $this, true, false, true);
			}
			return true;
		}
		return false;
	}
	
}