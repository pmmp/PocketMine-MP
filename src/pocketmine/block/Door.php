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
use pocketmine\level\Level;
use pocketmine\network\protocol\LevelEventPacket;
use pocketmine\Player;
use pocketmine\Server;


abstract class Door extends Transparent{
	public function __construct($id, $meta = 0, $name = "Unknown"){
		parent::__construct($id, $meta, $name);
		$this->isSolid = false;
	}

	public function onUpdate($type){
		if($type === Level::BLOCK_UPDATE_NORMAL){
			if($this->getSide(0)->getID() === self::AIR){ //Replace with common break method
				$this->getLevel()->setBlock($this, new Air(), false);
				if($this->getSide(1) instanceof Door){
					$this->getLevel()->setBlock($this->getSide(1), new Air(), false);
				}

				return Level::BLOCK_UPDATE_NORMAL;
			}
		}

		return false;
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		if($face === 1){
			$blockUp = $this->getSide(1);
			$blockDown = $this->getSide(0);
			if($blockUp->isReplaceable === false or $blockDown->isTransparent === true){
				return false;
			}
			$direction = $player instanceof Player ? $player->getDirection() : 0;
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
			$this->getLevel()->setBlock($blockUp, Block::get($this->id, $metaUp), true, false, true); //Top

			$this->meta = $player->getDirection() & 0x03;
			$this->getLevel()->setBlock($block, $this, true, false, true); //Bottom
			return true;
		}

		return false;
	}

	public function onBreak(Item $item){
		if(($this->meta & 0x08) === 0x08){
			$down = $this->getSide(0);
			if($down->getID() === $this->id){
				$this->getLevel()->setBlock($down, new Air(), true, false, true);
			}
		}else{
			$up = $this->getSide(1);
			if($up->getID() === $this->id){
				$this->getLevel()->setBlock($up, new Air(), true, false, true);
			}
		}
		$this->getLevel()->setBlock($this, new Air(), true, false, true);

		return true;
	}

	public function onActivate(Item $item, Player $player = null){
		if(($this->meta & 0x08) === 0x08){ //Top
			$down = $this->getSide(0);
			if($down->getID() === $this->id){
				$meta = $down->getDamage() ^ 0x04;
				$this->getLevel()->setBlock($down, Block::get($this->id, $meta), true, false, true);
				$players = $this->getLevel()->getUsingChunk($this->x >> 4, $this->z >> 4);
				if($player instanceof Player){
					unset($players[$player->getID()]);
				}
				$pk = new LevelEventPacket;
				$pk->x = $this->x;
				$pk->y = $this->y;
				$pk->z = $this->z;
				$pk->evid = 1003;
				$pk->data = 0;
				Server::broadcastPacket($players, $pk);

				return true;
			}

			return false;
		}else{
			$this->meta ^= 0x04;
			$this->getLevel()->setBlock($this, $this, true, false, true);
			$players = $this->getLevel()->getUsingChunk($this->x >> 4, $this->z >> 4);
			if($player instanceof Player){
				unset($players[$player->getID()]);
			}
			$pk = new LevelEventPacket;
			$pk->x = $this->x;
			$pk->y = $this->y;
			$pk->z = $this->z;
			$pk->evid = 1003;
			$pk->data = 0;
			Server::broadcastPacket($players, $pk);
		}

		return true;
	}
}