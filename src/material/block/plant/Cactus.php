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

class CactusBlock extends TransparentBlock{
	public function __construct($meta = 0){
		parent::__construct(CACTUS, $meta, "Cactus");
		$this->isFullBlock = false;
		$this->hardness = 2;
	}

	public function onUpdate($type){
		if($type === BLOCK_UPDATE_NORMAL){
			$down = $this->getSide(0);
			if($down->getID() !== SAND and $down->getID() !== CACTUS){ //Replace with common break method
				$this->level->setBlock($this, new AirBlock(), false);
				ServerAPI::request()->api->entity->drop($this, BlockAPI::getItem($this->id));
				return BLOCK_UPDATE_NORMAL;
			}
		}elseif($type === BLOCK_UPDATE_RANDOM){
			if($this->getSide(0)->getID() !== CACTUS){
				if($this->meta == 0x0F){
					for($y = 1; $y < 3; ++$y){
						$b = $this->level->getBlock(new Vector3($this->x, $this->y + $y, $this->z));
						if($b->getID() === AIR){
							$this->level->setBlock($b, new CactusBlock(), true, false, true);							
							break;
						}
					}
					$this->meta = 0;
					$this->level->setBlock($this, $this, false);
				}else{
					++$this->meta;
					$this->level->setBlock($this, $this, false);
				}
				return BLOCK_UPDATE_RANDOM;
			}
		}
		return false;
	}
	
	public function place(Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		$down = $this->getSide(0);
		if($down->getID() === SAND or $down->getID() === CACTUS){
			$block0 = $this->getSide(2);
			$block1 = $this->getSide(3);
			$block2 = $this->getSide(4);
			$block3 = $this->getSide(5);
			if($block0->isTransparent === true and $block1->isTransparent === true and $block2->isTransparent === true and $block3->isTransparent === true){
				$this->level->setBlock($this, $this, true, false, true);
				$this->level->scheduleBlockUpdate(new Position($this, 0, 0, $this->level), Utils::getRandomUpdateTicks(), BLOCK_UPDATE_RANDOM);
				return true;
			}
		}
		return false;
	}
	
	public function getDrops(Item $item, Player $player){
		return array(
			array($this->id, 0, 1),
		);
	}
}