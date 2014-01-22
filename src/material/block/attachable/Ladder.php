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

class LadderBlock extends TransparentBlock{
	public function __construct($meta = 0){
		parent::__construct(LADDER, $meta, "Ladder");
		$this->isSolid = false;
		$this->isFullBlock = false;
		$this->hardness = 2;
	}
	public function place(Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		if($target->isTransparent === false){
				$faces = array(
					2 => 2,
					3 => 3,
					4 => 4,
					5 => 5,
				);
			if(isset($faces[$face])){
				$this->meta = $faces[$face];
				$this->level->setBlock($block, $this, true, false, true);
				return true;
			}
		}
		return false;
	}

	public function onUpdate($type){
		if($type === BLOCK_UPDATE_NORMAL){
			/*if($this->getSide(0)->getID() === AIR){ //Replace with common break method
				ServerAPI::request()->api->entity->drop($this, BlockAPI::getItem(LADDER, 0, 1));
				$this->level->setBlock($this, new AirBlock(), true, true, true);
				return BLOCK_UPDATE_NORMAL;
			}*/
		}
		return false;
	}

	public function getDrops(Item $item, Player $player){
		return array(
			array($this->id, 0, 1),
		);
	}		
}