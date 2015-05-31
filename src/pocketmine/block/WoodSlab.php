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
use pocketmine\math\AxisAlignedBB;
use pocketmine\Player;

class WoodSlab extends Transparent{

	protected $id = self::WOOD_SLAB;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function getHardness(){
		return 2;
	}

	public function getName(){
		static $names = [
			0 => "Oak",
			1 => "Spruce",
			2 => "Birch",
			3 => "Jungle",
			4 => "Acacia",
			5 => "Dark Oak",
			6 => "",
			7 => ""
		];
		return (($this->meta & 0x08) === 0x08 ? "Upper " : "") . $names[$this->meta & 0x07] . " Wooden Slab";
	}

	protected function recalculateBoundingBox(){

		if(($this->meta & 0x08) > 0){
			return new AxisAlignedBB(
				$this->x,
				$this->y + 0.5,
				$this->z,
				$this->x + 1,
				$this->y + 1,
				$this->z + 1
			);
		}else{
			return new AxisAlignedBB(
				$this->x,
				$this->y,
				$this->z,
				$this->x + 1,
				$this->y + 0.5,
				$this->z + 1
			);
		}
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$this->meta &= 0x07;
		if($face === 0){
			if($target->getId() === self::WOOD_SLAB and ($target->getDamage() & 0x08) === 0x08 and ($target->getDamage() & 0x07) === ($this->meta & 0x07)){
				$this->getLevel()->setBlock($target, Block::get(Item::DOUBLE_WOOD_SLAB, $this->meta), true);

				return true;
			}elseif($block->getId() === self::WOOD_SLAB and ($block->getDamage() & 0x07) === ($this->meta & 0x07)){
				$this->getLevel()->setBlock($block, Block::get(Item::DOUBLE_WOOD_SLAB, $this->meta), true);

				return true;
			}else{
				$this->meta |= 0x08;
			}
		}elseif($face === 1){
			if($target->getId() === self::WOOD_SLAB and ($target->getDamage() & 0x08) === 0 and ($target->getDamage() & 0x07) === ($this->meta & 0x07)){
				$this->getLevel()->setBlock($target, Block::get(Item::DOUBLE_WOOD_SLAB, $this->meta), true);

				return true;
			}elseif($block->getId() === self::WOOD_SLAB and ($block->getDamage() & 0x07) === ($this->meta & 0x07)){
				$this->getLevel()->setBlock($block, Block::get(Item::DOUBLE_WOOD_SLAB, $this->meta), true);

				return true;
			}
		}else{ //TODO: collision
			if($block->getId() === self::WOOD_SLAB){
				if(($block->getDamage() & 0x07) === ($this->meta & 0x07)){
					$this->getLevel()->setBlock($block, Block::get(Item::DOUBLE_WOOD_SLAB, $this->meta), true);

					return true;
				}

				return false;
			}else{
				if($fy > 0.5){
					$this->meta |= 0x08;
				}
			}
		}

		if($block->getId() === self::WOOD_SLAB and ($target->getDamage() & 0x07) !== ($this->meta & 0x07)){
			return false;
		}
		$this->getLevel()->setBlock($block, $this, true, true);

		return true;
	}

	public function getDrops(Item $item){
		return [
			[$this->id, $this->meta & 0x07, 1],
		];
	}
}