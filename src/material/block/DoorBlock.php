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

class DoorBlock extends TransparentBlock{
    /**
     * @param int $id
     * @param int $meta
     * @param string $name
     */
    public function __construct($id, $meta = 0, $name = "Unknown"){
		parent::__construct($id, $meta, $name);
		$this->isSolid = false;
	}

    /**
     * @param int $type
     *
     * @return bool|int
     */
    public function onUpdate($type){
		if($type === BLOCK_UPDATE_NORMAL){
			if($this->getSide(0)->getID() === AIR){ //Replace with common break method
				$this->level->setBlock($this, new AirBlock(), false);
				if($this->getSide(1) instanceof DoorBlock){
					$this->level->setBlock($this->getSide(1), new AirBlock(), false);
				}
				return BLOCK_UPDATE_NORMAL;
			}
		}
		return false;
	}

    /**
     * @param Item $item
     * @param Player $player
     * @param Block $block
     * @param Block $target
     * @param integer $face
     * @param integer $fx
     * @param integer $fy
     * @param integer $fz
     *
     * @return boolean
     */
    public function place(Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		if($face === 1){
			$blockUp = $this->getSide(1);
			$blockDown = $this->getSide(0);
			if($blockUp->isReplaceable === false or $blockDown->isTransparent === true){
				return false;
			}
			$direction = $player->entity->getDirection();
			$face = array(
				0 => 3,
				1 => 4,
				2 => 2,
				3 => 5,
			);
			$next = $this->getSide($face[(($direction + 2) % 4)]);
			$next2 = $this->getSide($face[$direction]);
			$metaUp = 0x08;
			if($next->getID() === $this->id or ($next2->isTransparent === false and $next->isTransparent === true)){ //Door hinge
				$metaUp |= 0x01;
			}
			$this->level->setBlock($blockUp, BlockAPI::get($this->id, $metaUp), true, false, true); //Top
			
			$this->meta = $direction & 0x03;
			$this->level->setBlock($block, $this, true, false, true); //Bottom
			return true;			
		}
		return false;
	}

    /**
     * @param Item $item
     * @param Player $player
     *
     * @return boolean
     */
    public function onBreak(Item $item, Player $player){
		if(($this->meta & 0x08) === 0x08){
			$down = $this->getSide(0);
			if($down->getID() === $this->id){
				$this->level->setBlock($down, new AirBlock(), true, false, true);
			}
		}else{
			$up = $this->getSide(1);
			if($up->getID() === $this->id){
				$this->level->setBlock($up, new AirBlock(), true, false, true);
			}
		}
		$this->level->setBlock($this, new AirBlock(), true, false, true);
		return true;
	}

    /**
     * @param Item $item
     * @param Player $player
     *
     * @return boolean
     */
    public function onActivate(Item $item, Player $player){
		if(($this->meta & 0x08) === 0x08){ //Top
			$down = $this->getSide(0);
			if($down->getID() === $this->id){
				$meta = $down->getMetadata() ^ 0x04;
				$this->level->setBlock($down, BlockAPI::get($this->id, $meta), true, false, true);
				$players = ServerAPI::request()->api->player->getAll($this->level);
				unset($players[$player->CID]);
				ServerAPI::request()->api->player->broadcastPacket($players, MC_LEVEL_EVENT, array(
					"x" => $this->x,
					"y" => $this->y,
					"z" => $this->z,
					"evid" => 1003,
					"data" => 0
				));
				return true;
			}
			return false;
		}else{
			$this->meta ^= 0x04;
			$this->level->setBlock($this, $this, true, false, true);
			$players = ServerAPI::request()->api->player->getAll($this->level);
			unset($players[$player->CID]);
			ServerAPI::request()->api->player->broadcastPacket($players, MC_LEVEL_EVENT, array(
				"x" => $this->x,
				"y" => $this->y,
				"z" => $this->z,
				"evid" => 1003,
				"data" => 0
			));
		}
		return true;
	}
}