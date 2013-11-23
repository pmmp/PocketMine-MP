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

class PumpkinStemBlock extends FlowableBlock{
	public function __construct($meta = 0){
		parent::__construct(PUMPKIN_STEM, $meta, "Pumpkin Stem");
		$this->isActivable = true;
		$this->hardness = 0;
	}
	public function place(Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
			$down = $this->getSide(0);
			if($down->getID() === FARMLAND){
				$this->level->setBlock($block, $this, true, false, true);
				$this->level->scheduleBlockUpdate(new Position($this, 0, 0, $this->level), Utils::getRandomUpdateTicks(), BLOCK_UPDATE_RANDOM);
				return true;
			}
		return false;
	}

	public function onUpdate($type){
		if($type === BLOCK_UPDATE_NORMAL){
			if($this->getSide(0)->isTransparent === true){ //Replace with common break method
				ServerAPI::request()->api->entity->drop($this, BlockAPI::getItem(PUMPKIN_SEEDS, 0, mt_rand(0, 2)));
				$this->level->setBlock($this, new AirBlock(), false, false, true);
				return BLOCK_UPDATE_NORMAL;
			}
		}elseif($type === BLOCK_UPDATE_RANDOM){
			if(mt_rand(0, 2) == 1){
				if($this->meta < 0x07){
					++$this->meta;
					$this->level->setBlock($this, $this, true, false, true);
					return BLOCK_UPDATE_RANDOM;
				}else{
					for($side = 2; $side <= 5; ++$side){
						$b = $this->getSide($side);
						if($b->getID() === PUMPKIN){
							return BLOCK_UPDATE_RANDOM;
						}
					}
					$side = $this->getSide(mt_rand(2,5));
					$d = $side->getSide(0);
					if($side->getID() === AIR and ($d->getID() === FARMLAND or $d->getID() === GRASS or $d->getID() === DIRT)){
						$this->level->setBlock($side, new PumpkinBlock(), true, false, true);
					}
				}
			}
			return BLOCK_UPDATE_RANDOM;
		}
		return false;
	}
	
	public function onActivate(Item $item, Player $player){
		if($item->getID() === DYE and $item->getMetadata() === 0x0F){ //Bonemeal
			$this->meta = 0x07;
			$this->level->setBlock($this, $this, true, false, true);
			if(($player->gamemode & 0x01) === 0){
				$item->count--;
			}
			return true;
		}
		return false;
	}
	
	public function getDrops(Item $item, Player $player){
		return array(
			array(PUMPKIN_SEEDS, 0, mt_rand(0, 2)),
		);
	}
}