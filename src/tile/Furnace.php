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

namespace PocketMine\Tile;

use PocketMine\Block\Block as Block;
use PocketMine\Item\Item as Item;
use PocketMine\Level\Level as Level;
use PocketMine\NBT\Tag\Compound as Compound;
use PocketMine;

class Furnace extends Tile{
	use Container;

	const SLOTS = 3;

	public function __construct(Level $level, Compound $nbt){
		$nbt->id = Tile::FURNACE;
		parent::__construct($level, $nbt);
		if(!isset($this->namedtag->BurnTime) or $this->namedtag->BurnTime < 0){
			$this->namedtag->BurnTime = 0;
		}
		if(!isset($this->namedtag->CookTime) or $this->namedtag->CookTime < 0 or ($this->namedtag->BurnTime === 0 and $this->namedtag->CookTime > 0)){
			$this->namedtag->CookTime = 0;
		}
		if(!isset($this->namedtag->MaxTime)){
			$this->namedtag->MaxTime = $this->namedtag->BurnTime;
			$this->namedtag->BurnTicks = 0;
		}
		if($this->namedtag->BurnTime > 0){
			$this->scheduleUpdate();
		}
	}

	public function onUpdate(){
		if($this->closed === true){
			return false;
		}

		$ret = false;

		$fuel = $this->getSlot(1);
		$raw = $this->getSlot(0);
		$product = $this->getSlot(2);
		$smelt = $raw->getSmeltItem();
		$canSmelt = ($smelt !== false and $raw->getCount() > 0 and (($product->getID() === $smelt->getID() and $product->getMetadata() === $smelt->getMetadata() and $product->getCount() < $product->getMaxStackSize()) or $product->getID() === AIR));
		if($this->namedtag->BurnTime <= 0 and $canSmelt and $fuel->getFuelTime() !== false and $fuel->getCount() > 0){
			$this->lastUpdate = microtime(true);
			$this->namedtag->MaxTime = $this->namedtag->BurnTime = floor($fuel->getFuelTime() * 20);
			$this->namedtag->BurnTicks = 0;
			$fuel->setCount($fuel->getCount() - 1);
			if($fuel->getCount() === 0){
				$fuel = Item::get(AIR, 0, 0);
			}
			$this->setSlot(1, $fuel, false);
			$current = $this->level->getBlock($this);
			if($current->getID() === FURNACE){
				$this->level->setBlock($this, Block::get(BURNING_FURNACE, $current->getMetadata()), true, false, true);
			}
		}
		if($this->namedtag->BurnTime > 0){
			$ticks = (microtime(true) - $this->lastUpdate) * 20;
			$this->namedtag->BurnTime -= $ticks;
			$this->namedtag->BurnTicks = ceil(($this->namedtag->BurnTime / $this->namedtag->MaxTime) * 200);
			if($smelt !== false and $canSmelt){
				$this->namedtag->CookTime += $ticks;
				if($this->namedtag->CookTime >= 200){ //10 seconds
					$product = Item::get($smelt->getID(), $smelt->getMetadata(), $product->getCount() + 1);
					$this->setSlot(2, $product, false);
					$raw->setCount($raw->getCount() - 1);
					if($raw->getCount() === 0){
						$raw = Item::get(AIR, 0, 0);
					}
					$this->setSlot(0, $raw, false);
					$this->namedtag->CookTime -= 200;
				}
			} elseif($this->namedtag->BurnTime <= 0){
				$this->namedtag->BurnTime = 0;
				$this->namedtag->CookTime = 0;
				$this->namedtag->BurnTicks = 0;
			} else{
				$this->namedtag->CookTime = 0;
			}
			$ret = true;
		} else{
			$current = $this->level->getBlock($this);
			if($current->getID() === BURNING_FURNACE){
				$this->level->setBlock($this, Block::get(FURNACE, $current->getMetadata()), true, false, true);
			}
			$this->namedtag->CookTime = 0;
			$this->namedtag->BurnTime = 0;
			$this->namedtag->BurnTicks = 0;
		}


		$this->server->handle("tile.update", $this);
		$this->lastUpdate = microtime(true);

		return $ret;
	}
}