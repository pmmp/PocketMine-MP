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
use pocketmine\Player;

class WoodSlab extends Transparent{
	public function __construct($meta = 0){
		parent::__construct(self::WOOD_SLAB, $meta, "Wooden Slab");
		$names = array(
			0 => "Oak",
			1 => "Spruce",
			2 => "Birch",
			3 => "Jungle",
			4 => "Acacia",
			5 => "Dark Oak",
		);
		$this->name = (($this->meta & 0x08) === 0x08 ? "Upper " : "") . $names[$this->meta & 0x07] . " Wooden Slab";
		if(($this->meta & 0x08) === 0x08){
			$this->isFullBlock = true;
		}else{
			$this->isFullBlock = false;
		}
		$this->hardness = 15;
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$this->meta &= 0x07;
		if($face === 0){
			if($target->getID() === self::WOOD_SLAB and ($target->getDamage() & 0x08) === 0x08 and ($target->getDamage() & 0x07) === ($this->meta & 0x07)){
				$this->getLevel()->setBlock($target, Block::get(Item::DOUBLE_WOOD_SLAB, $this->meta), true, false, true);

				return true;
			}elseif($block->getID() === self::WOOD_SLAB and ($block->getDamage() & 0x07) === ($this->meta & 0x07)){
				$this->getLevel()->setBlock($block, Block::get(Item::DOUBLE_WOOD_SLAB, $this->meta), true, false, true);

				return true;
			}else{
				$this->meta |= 0x08;
			}
		}elseif($face === 1){
			if($target->getID() === self::WOOD_SLAB and ($target->getDamage() & 0x08) === 0 and ($target->getDamage() & 0x07) === ($this->meta & 0x07)){
				$this->getLevel()->setBlock($target, Block::get(Item::DOUBLE_WOOD_SLAB, $this->meta), true, false, true);

				return true;
			}elseif($block->getID() === self::WOOD_SLAB and ($block->getDamage() & 0x07) === ($this->meta & 0x07)){
				$this->getLevel()->setBlock($block, Block::get(Item::DOUBLE_WOOD_SLAB, $this->meta), true, false, true);

				return true;
			}
		}elseif(!($player instanceof Player)){ //TODO: collision
			if($block->getID() === self::WOOD_SLAB){
				if(($block->getDamage() & 0x07) === ($this->meta & 0x07)){
					$this->getLevel()->setBlock($block, Block::get(Item::DOUBLE_WOOD_SLAB, $this->meta), true, false, true);

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
		if($block->getID() === self::WOOD_SLAB and ($target->getDamage() & 0x07) !== ($this->meta & 0x07)){
			return false;
		}
		$this->getLevel()->setBlock($block, $this, true, false, true);

		return true;
	}

	public function getBreakTime(Item $item){

		switch($item->isAxe()){
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
				return 3;
		}
	}

	public function getDrops(Item $item){
		return array(
			array($this->id, $this->meta & 0x07, 1),
		);
	}
}