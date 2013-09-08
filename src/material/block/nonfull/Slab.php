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

class SlabBlock extends TransparentBlock{
	public function __construct($meta = 0){
		parent::__construct(SLAB, $meta, "Slab");
		$names = array(
			0 => "Stone",
			1 => "Sandstone",
			2 => "Wooden",
			3 => "Cobblestone",
			4 => "Brick",
			5 => "Stone Brick",
			6 => "Quartz",
		);
		$this->name = (($this->meta & 0x08) === 0x08 ? "Upper ":"") . $names[$this->meta & 0x07] . " Slab";	
		if(($this->meta & 0x08) === 0x08){
			$this->isFullBlock = true;
		}else{
			$this->isFullBlock = false;
		}		
		$this->hardness = 30;
	}
	
	public function place(Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
			$this->meta &= 0x07;
			if($face === 0){
				if($target->getID() === SLAB and ($target->getMetadata() & 0x08) === 0x08 and ($target->getMetadata() & 0x07) === ($this->meta & 0x07)){
					$this->level->setBlock($target, BlockAPI::get(DOUBLE_SLAB, $this->meta), true, false, true);
					return true;
				}elseif($block->getID() === SLAB and ($block->getMetadata() & 0x07) === ($this->meta & 0x07)){
					$this->level->setBlock($block, BlockAPI::get(DOUBLE_SLAB, $this->meta), true, false, true);
					return true;
				}else{
					$this->meta |= 0x08;
				}
			}elseif($face === 1){
				if($target->getID() === SLAB and ($target->getMetadata() & 0x08) === 0 and ($target->getMetadata() & 0x07) === ($this->meta & 0x07)){
					$this->level->setBlock($target, BlockAPI::get(DOUBLE_SLAB, $this->meta), true, false, true);
					return true;
				}elseif($block->getID() === SLAB and ($block->getMetadata() & 0x07) === ($this->meta & 0x07)){
					$this->level->setBlock($block, BlockAPI::get(DOUBLE_SLAB, $this->meta), true, false, true);
					return true;
				}
			}elseif(!$player->entity->inBlock($block)){
				if($block->getID() === SLAB){
					if(($block->getMetadata() & 0x07) === ($this->meta & 0x07)){
						$this->level->setBlock($block, BlockAPI::get(DOUBLE_SLAB, $this->meta), true, false, true);
						return true;
					}
					return false;
				}else{
					if($fy > 0.5){
						$this->meta |= 0x08;
					}
				}
			}else{
				return false;
			}
			if($block->getID() === SLAB and ($target->getMetadata() & 0x07) !== ($this->meta & 0x07)){
				return false;
			}
			$this->level->setBlock($block, $this, true, false, true);
			return true;
	}

	public function getBreakTime(Item $item, Player $player){
		if(($player->gamemode & 0x01) === 0x01){
			return 0.20;
		}		
		switch($item->isPickaxe()){
			case 5:
				return 0.4;
			case 4:
				return 0.5;
			case 3:
				return 0.75;
			case 2:
				return 0.25;
			case 1:
				return 1.5;
			default:
				return 10;
		}
	}
	
	public function getDrops(Item $item, Player $player){
		if($item->isPickaxe() >= 1){
			return array(
				array($this->id, $this->meta & 0x07, 1),
			);
		}else{
			return array();
		}
	}
}