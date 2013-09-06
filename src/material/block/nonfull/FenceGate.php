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

class FenceGateBlock extends TransparentBlock{
	public function __construct($meta = 0){
		parent::__construct(FENCE_GATE, $meta, "Fence Gate");
		$this->isActivable = true;
		if(($this->meta & 0x04) === 0x04){
			$this->isFullBlock = true;
		}else{
			$this->isFullBlock = false;
		}
		$this->hardness = 15;
	}
	public function place(Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		$faces = array(
			0 => 3,
			1 => 0,
			2 => 1,
			3 => 2,
		);
		$this->meta = $faces[$player->entity->getDirection()] & 0x03;
		$this->level->setBlock($block, $this, true, false, true);
		return true;
	}
	public function getDrops(Item $item, Player $player){
		return array(
			array($this->id, 0, 1),
		);
	}
	public function onActivate(Item $item, Player $player){
		$faces = array(
			0 => 3,
			1 => 0,
			2 => 1,
			3 => 2,
		);
		$this->meta = ($faces[$player->entity->getDirection()] & 0x03) | ((~$this->meta) & 0x04);
		if(($this->meta & 0x04) === 0x04){
			$this->isFullBlock = true;
		}else{
			$this->isFullBlock = false;
		}
		$this->level->setBlock($this, $this, true, false, true);
		return true;
	}	
}