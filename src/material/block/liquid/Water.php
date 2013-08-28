<?php

/*

           -
         /   \
      /         \
   /   PocketMine  \
/          MP         \
|\     @shoghicp     /|
|.   \           /   .|
| ..     \   /     .. |
|    ..    |    ..    |
|       .. | ..       |
\          |          /
   \       |       /
      \    |    /
         \ | /

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Lesser General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.


*/

class WaterBlock extends LiquidBlock{
	public function __construct($meta = 0){
		parent::__construct(WATER, $meta, "Water");
	}	
	
	public function place(Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		$ret = $this->level->setBlock($this, $this, true, false, true);
		ServerAPI::request()->api->block->scheduleBlockUpdate(clone $this, 10, BLOCK_UPDATE_NORMAL);
		return $ret;
	}

	public function onUpdate($type){
		return false;
		$newId = $this->id;
		$level = $this->meta & 0x07;
		if($type !== BLOCK_UPDATE_NORMAL){
			return false;
		}
		
		$falling = $this->meta >> 3;
		$down = $this->getSide(0);
		
		if($falling === 0){
			$countSources = 0;
			$maxLevel = $level;
			$hasPath = false;
			for($side = 2; $side <= 5; ++$side){
				$b = $this->getSide($side);
				if($b->isFlowable === true and $level < 0x07){
					$d = $b->getSide(0);
					$this->level->setBlock($b, new WaterBlock($level + 1), false, false, true);
					ServerAPI::request()->api->block->scheduleBlockUpdate(new Position($b, 0, 0, $this->level), 10, BLOCK_UPDATE_NORMAL);
				}elseif($b instanceof WaterBlock){
					$oLevel = $b->getMetadata();
					$oFalling = $oLevel >> 3;
					$oLevel &= 0x07;
					if($oFalling === 0){
						if($oLevel === 0){
							++$countSources;
							$maxLevel = 1;
							$hasPath = true;
						}elseif($oLevel < 0x07 and ($oLevel + 1) <= $maxLevel){
							$maxLevel = $oLevel + 1;
							$hasPath = true;
						}elseif(($level + 1) < $oLevel){
							$this->level->setBlock($b, new WaterBlock($level + 1), false, false, true);
							ServerAPI::request()->api->block->scheduleBlockUpdate(new Position($b, 0, 0, $this->level), 10, BLOCK_UPDATE_NORMAL);
						}elseif($level === $oLevel){
							ServerAPI::request()->api->block->scheduleBlockUpdate(new Position($b, 0, 0, $this->level), 10, BLOCK_UPDATE_NORMAL);
						}
					}
				}
			}
			if($countSources >= 2){
				$level = 0; //Source block
			}elseif($maxLevel < $level){
				$level = $maxLevel;
			}elseif($maxLevel === $level and $level > 0 and $hasPath === false){
				if($level < 0x07){
					++$level;
				}else{
					$newId = AIR;
					$level = 0;
				}
			}
		}

		if($down->isFlowable){
			$this->level->setBlock($down, new WaterBlock(0b1001), false, false, true);
			ServerAPI::request()->api->block->scheduleBlockUpdate(new Position($down, 0, 0, $this->level), 5, BLOCK_UPDATE_NORMAL);
			return false;
		}elseif($down instanceof LiquidBlock){
			if($down instanceof WaterBlock and ($down->getMetadata() >> 3) === 0){
				$this->level->setBlock($down, new WaterBlock(0b1000 & min($down->getMetadata(), 1)), false, false, true);
				ServerAPI::request()->api->block->scheduleBlockUpdate(new Position($down, 0, 0, $this->level), 5, BLOCK_UPDATE_NORMAL);
			}
		}else{
			$falling = 0;
		}
		
		$newMeta = ($falling << 0x03) | $level;
		if($newMeta !== $this->meta or $newId !== $this->id){
			$this->id = $newId;
			$this->meta = $newMeta;
			$this->level->setBlock($this, $this, false, false, true);
			ServerAPI::request()->api->block->scheduleBlockUpdate(new Position($this, 0, 0, $this->level), 10, BLOCK_UPDATE_NORMAL);
			return false;
		}
		return false;
	}	
}