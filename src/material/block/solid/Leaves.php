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

class LeavesBlock extends TransparentBlock{
	const OAK = 0;
	const SPRUCE = 1;
	const BIRCH = 2;
	public function __construct($meta = 0){
		parent::__construct(LEAVES, $meta, "Leaves");
		$names = array(
			LeavesBlock::OAK => "Oak Leaves",
			LeavesBlock::SPRUCE => "Spruce Leaves",
			LeavesBlock::BIRCH => "Birch Leaves",
			3 => "",
		);
		$this->name = $names[$this->meta & 0x03];
		$this->hardness = 1;
	}
	
	private function findLog(Block $pos, array $visited, $distance, &$check, $fromSide = null){
		++$check;
		$index = $pos->x.".".$pos->y.".".$pos->z;
		if(isset($visited[$index])){
			return false;
		}
		if($pos->getID() === WOOD){
			return true;
		}elseif($pos->getID() === LEAVES and $distance < 3){
			$visited[$index] = true;
			$down = $pos->getSide(0)->getID();
			if($down === WOOD){
				return true;
			}
			if($fromSide === null){
				for($side = 2; $side <= 5; ++$side){
					if($this->findLog($pos->getSide($side), $visited, $distance + 1, $check, $side) === true){
						return true;
					}
				}
			}else{ //No more loops
				switch($fromSide){
					case 2:
						if($this->findLog($pos->getSide(2), $visited, $distance + 1, $check, $fromSide) === true){
							return true;
						}elseif($this->findLog($pos->getSide(4), $visited, $distance + 1, $check, $fromSide) === true){
							return true;
						}elseif($this->findLog($pos->getSide(5), $visited, $distance + 1, $check, $fromSide) === true){
							return true;
						}
						break;
					case 3:
						if($this->findLog($pos->getSide(3), $visited, $distance + 1, $check, $fromSide) === true){
							return true;
						}elseif($this->findLog($pos->getSide(4), $visited, $distance + 1, $check, $fromSide) === true){
							return true;
						}elseif($this->findLog($pos->getSide(5), $visited, $distance + 1, $check, $fromSide) === true){
							return true;
						}
						break;
					case 4:
						if($this->findLog($pos->getSide(2), $visited, $distance + 1, $check, $fromSide) === true){
							return true;
						}elseif($this->findLog($pos->getSide(3), $visited, $distance + 1, $check, $fromSide) === true){
							return true;
						}elseif($this->findLog($pos->getSide(4), $visited, $distance + 1, $check, $fromSide) === true){
							return true;
						}
						break;
					case 5:
						if($this->findLog($pos->getSide(2), $visited, $distance + 1, $check, $fromSide) === true){
							return true;
						}elseif($this->findLog($pos->getSide(3), $visited, $distance + 1, $check, $fromSide) === true){
							return true;
						}elseif($this->findLog($pos->getSide(5), $visited, $distance + 1, $check, $fromSide) === true){
							return true;
						}
						break;
				}
			}
		}

		return false;
	}
	
	public function onUpdate($type){
		if($type === BLOCK_UPDATE_NORMAL){
			if(($this->meta & 0b00001100) === 0){
				$this->meta |= 0x08;
				$this->level->setBlock($this, $this, false, false, true);
				return BLOCK_UPDATE_RANDOM;
			}
		}elseif($type === BLOCK_UPDATE_RANDOM){
			if(($this->meta & 0b00001100) === 0x08){
				$this->meta &= 0x03;
				$visited = array();
				$check = 0;
				if($this->findLog($this, $visited, 0, $check) === true){
					$this->level->setBlock($this, $this, false, false, true);
				}else{
					$this->level->setBlock($this, new AirBlock(), false, false, true);
					if(mt_rand(1,20) === 1){ //Saplings
						ServerAPI::request()->api->entity->drop($this, BlockAPI::getItem(SAPLING, $this->meta & 0x03, 1));
					}
					if(($this->meta & 0x03) === LeavesBlock::OAK and mt_rand(1,200) === 1){ //Apples
						ServerAPI::request()->api->entity->drop($this, BlockAPI::getItem(APPLE, 0, 1));
					}
					return BLOCK_UPDATE_NORMAL;
				}
			}
		}
		return false;
	}
	
	public function place(Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		$this->meta |= 0x04;
		$this->level->setBlock($this, $this, true, false, true);
	}
	
	public function getDrops(Item $item, Player $player){
		$drops = array();
		if($item->isShears()){
			$drops[] = array(LEAVES, $this->meta & 0x03, 1);
		}else{
			if(mt_rand(1,20) === 1){ //Saplings
				$drops[] = array(SAPLING, $this->meta & 0x03, 1);
			}
			if(($this->meta & 0x03) === LeavesBlock::OAK and mt_rand(1,200) === 1){ //Apples
				$drops[] = array(APPLE, 0, 1);
			}
		}
		return $drops;
	}
}