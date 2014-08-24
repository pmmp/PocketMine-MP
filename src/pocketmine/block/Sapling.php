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
use pocketmine\level\generator\object\Tree;
use pocketmine\level\Level;
use pocketmine\Player;
use pocketmine\utils\Random;

class Sapling extends Flowable{
	const OAK = 0;
	const SPRUCE = 1;
	const BIRCH = 2;
	const JUNGLE = 3;
	const BURN_TIME = 5;

	public function __construct($meta = Sapling::OAK){
		parent::__construct(self::SAPLING, $meta, "Sapling");
		$this->isActivable = true;
		$names = array(
			0 => "Oak Sapling",
			1 => "Spruce Sapling",
			2 => "Birch Sapling",
			3 => "Jungle Sapling",
			4 => "Acacia Sapling",
			5 => "Dark Oak Sapling",
		);
		$this->name = $names[$this->meta & 0x07];
		$this->hardness = 0;
	}

	public function getBoundingBox(){
		return null;
	}


	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$down = $this->getSide(0);
		if($down->getID() === self::GRASS or $down->getID() === self::DIRT or $down->getID() === self::FARMLAND){
			$this->getLevel()->setBlock($block, $this, true, false, true);

			return true;
		}

		return false;
	}

	public function onActivate(Item $item, Player $player = null){
		if($item->getID() === Item::DYE and $item->getDamage() === 0x0F){ //Bonemeal
			//TODO: change log type
			Tree::growTree($this->getLevel(), $this->x, $this->y, $this->z, new Random(mt_rand()), $this->meta & 0x07);
			if(($player->gamemode & 0x01) === 0){
				$item->count--;
			}

			return true;
		}

		return false;
	}

	public function onUpdate($type){
		if($type === Level::BLOCK_UPDATE_NORMAL){
			if($this->getSide(0)->isTransparent === true){ //Replace with common break method
				//TODO
				//Server::getInstance()->api->entity->drop($this, Item::get($this->id));
				$this->getLevel()->setBlock($this, new Air(), false, false, true);

				return Level::BLOCK_UPDATE_NORMAL;
			}
		}elseif($type === Level::BLOCK_UPDATE_RANDOM){ //Growth
			if(mt_rand(1, 7) === 1){
				if(($this->meta & 0x08) === 0x08){
					Tree::growTree($this->getLevel(), $this->x, $this->y, $this->z, new Random(mt_rand()), $this->meta & 0x07);
				}else{
					$this->meta |= 0x08;
					$this->getLevel()->setBlock($this, $this, true, false, true);

					return Level::BLOCK_UPDATE_RANDOM;
				}
			}else{
				return Level::BLOCK_UPDATE_RANDOM;
			}
		}

		return false;
	}

	public function getDrops(Item $item){
		return array(
			array($this->id, $this->meta & 0x07, 1),
		);
	}
}