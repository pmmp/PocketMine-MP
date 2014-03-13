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

namespace PocketMine\Block;

use PocketMine;
use PocketMine\Item\Item;
use PocketMine\Level\Generator\Object\Tree;
use PocketMine\Level\Level;
use PocketMine\Utils\Random;

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
		);
		$this->name = $names[$this->meta & 0x03];
		$this->hardness = 0;
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, PocketMine\Player $player = null){
		$down = $this->getSide(0);
		if($down->getID() === self::GRASS or $down->getID() === self::DIRT or $down->getID() === self::FARMLAND){
			$this->level->setBlock($block, $this, true, false, true);

			return true;
		}

		return false;
	}

	public function onActivate(Item $item, PocketMine\Player $player = null){
		if($item->getID() === Item::DYE and $item->getMetadata() === 0x0F){ //Bonemeal
			Tree::growTree($this->level, $this, new Random(), $this->meta & 0x03);
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
				//ServerAPI::request()->api->entity->drop($this, Item::get($this->id));
				$this->level->setBlock($this, new Air(), false, false, true);

				return Level::BLOCK_UPDATE_NORMAL;
			}
		}elseif($type === Level::BLOCK_UPDATE_RANDOM){ //Growth
			if(mt_rand(1, 7) === 1){
				if(($this->meta & 0x08) === 0x08){
					Tree::growTree($this->level, $this, new Random(), $this->meta & 0x03);
				}else{
					$this->meta |= 0x08;
					$this->level->setBlock($this, $this, true, false, true);

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
			array($this->id, $this->meta & 0x03, 1),
		);
	}
}