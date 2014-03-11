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
use PocketMine\Level\Level;

class MelonStem extends Flowable{
	public function __construct($meta = 0){
		parent::__construct(self::MELON_STEM, $meta, "Melon Stem");
		$this->isActivable = true;
		$this->hardness = 0;
	}

	public function place(Item $item, PocketMine\Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		$down = $this->getSide(0);
		if($down->getID() === self::FARMLAND){
			$this->level->setBlock($block, $this, true, false, true);

			return true;
		}

		return false;
	}

	public function onUpdate($type){
		if($type === Level::BLOCK_UPDATE_NORMAL){
			if($this->getSide(0)->isTransparent === true){ //Replace with common break method
				//TODO
				//ServerAPI::request()->api->entity->drop($this, Item::get(MELON_SEEDS, 0, mt_rand(0, 2)));
				$this->level->setBlock($this, new Air(), false, false, true);

				return Level::BLOCK_UPDATE_NORMAL;
			}
		} elseif($type === Level::BLOCK_UPDATE_RANDOM){
			if(mt_rand(0, 2) == 1){
				if($this->meta < 0x07){
					++$this->meta;
					$this->level->setBlock($this, $this, true, false, true);

					return Level::BLOCK_UPDATE_RANDOM;
				} else{
					for($side = 2; $side <= 5; ++$side){
						$b = $this->getSide($side);
						if($b->getID() === self::MELON_BLOCK){
							return Level::BLOCK_UPDATE_RANDOM;
						}
					}
					$side = $this->getSide(mt_rand(2, 5));
					$d = $side->getSide(0);
					if($side->getID() === self::AIR and ($d->getID() === self::FARMLAND or $d->getID() === self::GRASS or $d->getID() === self::DIRT)){
						$this->level->setBlock($side, new Melon(), true, false, true);
					}
				}
			}

			return Level::BLOCK_UPDATE_RANDOM;
		}

		return false;
	}

	public function onActivate(Item $item, PocketMine\Player $player){
		if($item->getID() === Item::DYE and $item->getMetadata() === 0x0F){ //Bonemeal
			$this->meta = 0x07;
			$this->level->setBlock($this, $this, true, false, true);
			if(($player->gamemode & 0x01) === 0){
				$item->count--;
			}

			return true;
		}

		return false;
	}

	public function getDrops(Item $item, PocketMine\Player $player){
		return array(
			array(Item::MELON_SEEDS, 0, mt_rand(0, 2)),
		);
	}
}