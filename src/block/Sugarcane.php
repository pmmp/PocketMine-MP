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
use PocketMine\Item\Item as Item;
use PocketMine\Math\Vector3 as Vector3;

class Sugarcane extends Flowable{
	public function __construct($meta = 0){
		parent::__construct(SUGARCANE_BLOCK, $meta, "Sugarcane");
		$this->hardness = 0;
	}

	public function getDrops(Item $item, Player $player){
		return array(
			array(SUGARCANE, 0, 1),
		);
	}

	public function onActivate(Item $item, Player $player){
		if($item->getID() === self::DYE and $item->getMetadata() === 0x0F){ //Bonemeal
			if($this->getSide(0)->getID() !== self::SUGARCANE_BLOCK){
				for($y = 1; $y < 3; ++$y){
					$b = $this->level->getBlock(new Vector3($this->x, $this->y + $y, $this->z));
					if($b->getID() === self::AIR){
						$this->level->setBlock($b, new Sugarcane(), true, false, true);
						break;
					}
				}
				$this->meta = 0;
				$this->level->setBlock($this, $this, true, false, true);
			}
			if(($player->gamemode & 0x01) === 0){
				$item->count--;
			}

			return true;
		}

		return false;
	}

	public function onUpdate($type){
		if($type === BLOCK_UPDATE_NORMAL){
			$down = $this->getSide(0);
			if($down->isTransparent === true and $down->getID() !== self::SUGARCANE_BLOCK){ //Replace with common break method
				//TODO
				//ServerAPI::request()->api->entity->drop($this, Item::get(SUGARCANE));
				$this->level->setBlock($this, new Air(), false, false, true);

				return BLOCK_UPDATE_NORMAL;
			}
		} elseif($type === BLOCK_UPDATE_RANDOM){
			if($this->getSide(0)->getID() !== self::SUGARCANE_BLOCK){
				if($this->meta === 0x0F){
					for($y = 1; $y < 3; ++$y){
						$b = $this->level->getBlock(new Vector3($this->x, $this->y + $y, $this->z));
						if($b->getID() === self::AIR){
							$this->level->setBlock($b, new Sugarcane(), true, false, true);
							break;
						}
					}
					$this->meta = 0;
					$this->level->setBlock($this, $this, true, false, true);
				} else{
					++$this->meta;
					$this->level->setBlock($this, $this, true, false, true);
				}

				return BLOCK_UPDATE_RANDOM;
			}
		}

		return false;
	}

	public function place(Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		$down = $this->getSide(0);
		if($down->getID() === self::SUGARCANE_BLOCK){
			$this->level->setBlock($block, new Sugarcane(), true, false, true);

			return true;
		} elseif($down->getID() === self::GRASS or $down->getID() === self::DIRT or $down->getID() === self::SAND){
			$block0 = $down->getSide(2);
			$block1 = $down->getSide(3);
			$block2 = $down->getSide(4);
			$block3 = $down->getSide(5);
			if(($block0 instanceof Water) or ($block1 instanceof Water) or ($block2 instanceof Water) or ($block3 instanceof Water)){
				$this->level->setBlock($block, new Sugarcane(), true, false, true);

				return true;
			}
		}

		return false;
	}
}