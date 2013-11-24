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

class LavaBlock extends LiquidBlock{
	public function __construct($meta = 0){
		parent::__construct(LAVA, $meta, "Lava");
		$this->hardness = 0;
	}
	
		public function place(Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		$ret = $this->level->setBlock($this, $this, true, false, true);
		ServerAPI::request()->api->block->scheduleBlockUpdate(clone $this, 40, BLOCK_UPDATE_NORMAL);
		return $ret;
	}
	
	public function getSourceCount(){
		$count = 0;
		for($side = 2; $side <= 5; ++$side){
			if($this->getSide($side) instanceof LavaBlock ){
				$b = $this->getSide($side);
				$level = $b->meta & 0x07;
				if($level == 0x00){
					$count++;
				}
			}
		}
		return $count;
	}
	
	public function checkWater(){
		for($side = 1; $side <= 5; ++$side){
			$b = $this->getSide($side);
			if($b instanceof WaterBlock){
				$level = $this->meta & 0x07;
				if($level == 0x00){
					$this->level->setBlock($this, new ObsidianBlock(), false, false, true);
				}else{
					$this->level->setBlock($this, new CobblestoneBlock(), false, false, true);
				}
			}
		}
	}
	
	public function getFrom(){
		for($side = 0; $side <= 5; ++$side){
			$b = $this->getSide($side);
			if($b instanceof LavaBlock){
				$tlevel = $b->meta & 0x07;
				$level = $this->meta & 0x07;
				if( ($tlevel + 2) == $level || ($side == 0x01 && $level == 0x01 ) || ($tlevel == 6 && $level == 7 )){
					return $b;
				}
			}
		}
		return null;
	}
		
	public function onUpdate($type){
		//return false;
		$newId = $this->id;
		$level = $this->meta & 0x07;
		if($type !== BLOCK_UPDATE_NORMAL){
			return false;
		}
		
		if( $this->checkWater()){
			return;
		}
		
		$falling = $this->meta >> 3;
		$down = $this->getSide(0);
		
		$from = $this->getFrom();
		if($from !== null || $level == 0x00){
			if($level !== 0x07){
				if($down instanceof AirBlock || $down instanceof LavaBlock){
					$this->level->setBlock($down, new LavaBlock(0x01), false, false, true);
					ServerAPI::request()->api->block->scheduleBlockUpdate(new Position($down, 0, 0, $this->level), 40, BLOCK_UPDATE_NORMAL);
				}else{
					for($side = 2; $side <= 5; ++$side){
						$b = $this->getSide($side);
						if($b instanceof LavaBlock){
							
						}else if($b->isFlowable === true){
							$this->level->setBlock($b, new LavaBlock( min($level + 2,7) ), false, false, true);
							ServerAPI::request()->api->block->scheduleBlockUpdate(new Position($b, 0, 0, $this->level), 40, BLOCK_UPDATE_NORMAL);
						}
					}
				}
			}
		}else{
			//Extend Remove for Left Lavas
			for($side = 2; $side <= 5; ++$side){
				$sb = $this->getSide($side);
				if($sb instanceof LavaBlock){
					$tlevel = $sb->meta & 0x07;
					if($tlevel != 0x00){
						$this->level->setBlock($sb, new AirBlock(), false, false, true);
					}
				}
				$b = $this->getSide(0)->getSide($side);
				if($b instanceof LavaBlock){
					$tlevel = $b->meta & 0x07;
					if($tlevel != 0x00){
						$this->level->setBlock($b, new AirBlock(), false, false, true);
					}
				}
				//ServerAPI::request()->api->block->scheduleBlockUpdate(new Position($b, 0, 0, $this->level), 10, BLOCK_UPDATE_NORMAL);
			}

			$this->level->setBlock($this, new AirBlock(), false, false, true);
		}
		return false;
	}	
	
}
