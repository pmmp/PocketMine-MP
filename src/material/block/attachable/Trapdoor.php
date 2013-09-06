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

class TrapdoorBlock extends TransparentBlock{
	public function __construct($meta = 0){
		parent::__construct(TRAPDOOR, $meta, "Trapdoor");
		$this->isActivable = true;
		if(($this->meta & 0x04) === 0x04){
			$this->isFullBlock = false;
		}else{
			$this->isFullBlock = true;
		}
		$this->hardness = 15;
	}
	public function place(Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
			if(($target->isTransparent === false or $target->getID() === SLAB) and $face !== 0 and $face !== 1){
				$faces = array(
					2 => 0,
					3 => 1,
					4 => 2,
					5 => 3,
				);
				$this->meta = $faces[$face] & 0x03;
				if($fy > 0.5){
					$this->meta |= 0x08;
				}
				$this->level->setBlock($block, $this, true, false, true);
				return true;
			}
	}
	public function getDrops(Item $item, Player $player){
		return array(
			array($this->id, 0, 1),
		);
	}
	public function onActivate(Item $item, Player $player){
		$this->meta ^= 0x04;
		$this->level->setBlock($this, $this, true, false, true);
		return true;
	}
}