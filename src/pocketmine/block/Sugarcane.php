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
use pocketmine\math\Vector3 as Vector3;
use pocketmine\Player;

class Sugarcane extends Flowable{
	public function __construct($meta = 0){
		parent::__construct(self::SUGARCANE_BLOCK, $meta, "Sugarcane");
		$this->hardness = 0;
	}

	public function getBoundingBox(){
		return null;
	}


	public function getDrops(Item $item){
		return array(
			array(Item::SUGARCANE, 0, 1),
		);
	}

	public function onActivate(Item $item, Player $player = null){
		if($item->getID() === Item::DYE and $item->getDamage() === 0x0F){ //Bonemeal
			if($this->getSide(0)->getID() !== self::SUGARCANE_BLOCK){
				for($y = 1; $y < 3; ++$y){
					$b = $this->getLevel()->getBlock(new Vector3($this->x, $this->y + $y, $this->z));
					if($b->getID() === self::AIR){
						$this->getLevel()->setBlock($b, new Sugarcane(), true, false, true);
						break;
					}
				}
				$this->meta = 0;
				$this->getLevel()->setBlock($this, $this, true, false, true);
			}
			if(($player->gamemode & 0x01) === 0){
				$item->count--;
			}

			return true;
		}

		return false;
	}

	public function onUpdate($type){
		if($type === Level::BLOCK_UPDATE_NORMAL){
			$down = $this->getSide(0);
			if($down->isTransparent === true and $down->getID() !== self::SUGARCANE_BLOCK){ //Replace with common break method
				//TODO
				//Server::getInstance()->api->entity->drop($this, Item::get(SUGARCANE));
				$this->getLevel()->setBlock($this, new Air(), false, false, true);

				return Level::BLOCK_UPDATE_NORMAL;
			}
		}elseif($type === Level::BLOCK_UPDATE_RANDOM){
			if($this->getSide(0)->getID() !== self::SUGARCANE_BLOCK){
				if($this->meta === 0x0F){
					for($y = 1; $y < 3; ++$y){
						$b = $this->getLevel()->getBlock(new Vector3($this->x, $this->y + $y, $this->z));
						if($b->getID() === self::AIR){
							$this->getLevel()->setBlock($b, new Sugarcane(), true, false, true);
							break;
						}
					}
					$this->meta = 0;
					$this->getLevel()->setBlock($this, $this, true, false, true);
				}else{
					++$this->meta;
					$this->getLevel()->setBlock($this, $this, true, false, true);
				}

				return Level::BLOCK_UPDATE_RANDOM;
			}
		}

		return false;
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$down = $this->getSide(0);
		if($down->getID() === self::SUGARCANE_BLOCK){
			$this->getLevel()->setBlock($block, new Sugarcane(), true, false, true);

			return true;
		}elseif($down->getID() === self::GRASS or $down->getID() === self::DIRT or $down->getID() === self::SAND){
			$block0 = $down->getSide(2);
			$block1 = $down->getSide(3);
			$block2 = $down->getSide(4);
			$block3 = $down->getSide(5);
			if(($block0 instanceof Water) or ($block1 instanceof Water) or ($block2 instanceof Water) or ($block3 instanceof Water)){
				$this->getLevel()->setBlock($block, new Sugarcane(), true, false, true);

				return true;
			}
		}

		return false;
	}
}