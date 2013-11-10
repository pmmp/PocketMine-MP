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

class WaterBlock extends LiquidBlock{
	public function __construct($meta = 0){
		parent::__construct(WATER, $meta, "Water");
		$this->hardness = 500;
	}
	
	public function place(Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		$ret = $this->level->setBlock($this, $this, true, false, true);
		ServerAPI::request()->api->block->scheduleBlockUpdate(clone $this, 10, BLOCK_UPDATE_NORMAL);
		return $ret;
	}
	
	public function getSourceCount()
	{
		$count = 0;
		for($side = 2; $side <= 5; ++$side)
		{
			if( $this->getSide($side) instanceof WaterBlock )
			{
				$b = $this->getSide($side);
				$level = $b->meta & 0x07;
				if($level == 0x00)
				{
					$count++;
				}
			}
		}
		return $count;
	}
	
	public function getFrom()
	{
		for($side = 0; $side <= 5; ++$side)
		{
			$b = $this->getSide($side);
			if($b instanceof WaterBlock)
			{
				$tlevel = $b->meta & 0x07;
				$level = $this->meta & 0x07;
				if( ($tlevel + 1) == $level || ($side == 0x01 && $level == 0x01 ) )
				{
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
		
		$falling = $this->meta >> 3;
		$down = $this->getSide(0);
		
		$from = $this->getFrom();
		//출처가 있거나 이 자체가 출처이면
		if($from !== null || $level == 0x00)
		{
			if($level !== 0x07)
			{
				if($down instanceof AirBlock || $down instanceof WaterBlock)
				{
					$this->level->setBlock($down, new WaterBlock(0x01), false, false, true);
					ServerAPI::request()->api->block->scheduleBlockUpdate(new Position($down, 0, 0, $this->level), 10, BLOCK_UPDATE_NORMAL);
				}
				else
				{
					for($side = 2; $side <= 5; ++$side)
					{
						$b = $this->getSide($side);
						if($b instanceof WaterBlock)
						{
							if( $this->getSourceCount() >= 2)
							{
								$this->level->setBlock($this, new WaterBlock(0), false, false, true);
							}
						}
						else if($b->isFlowable === true)
						{
							$this->level->setBlock($b, new WaterBlock($level + 1), false, false, true);
							ServerAPI::request()->api->block->scheduleBlockUpdate(new Position($b, 0, 0, $this->level), 10, BLOCK_UPDATE_NORMAL);
						}
					}
				}
			}
		}
		else
		{
			//Extend Remove for Left Waters
			for($side = 2; $side <= 5; ++$side)
			{
				$sb = $this->getSide($side);
				if($sb instanceof WaterBlock)
				{
					$tlevel = $sb->meta & 0x07;
					if($tlevel != 0x00)
					{
						$this->level->setBlock($sb, new AirBlock(), false, false, true);
					}
				}
				$b = $this->getSide(0)->getSide($side);
				if($b instanceof WaterBlock)
				{
					$tlevel = $b->meta & 0x07;
					if($tlevel != 0x00)
					{
						$this->level->setBlock($b, new AirBlock(), false, false, true);
					}
				}
				//ServerAPI::request()->api->block->scheduleBlockUpdate(new Position($b, 0, 0, $this->level), 10, BLOCK_UPDATE_NORMAL);
			}
			//출처가 제거된 경우 이 블록 제거	
			$this->level->setBlock($this, new AirBlock(), false, false, true);
		}
		
		/*
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
		*/
		return false;
	}	
}