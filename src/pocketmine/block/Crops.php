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

declare(strict_types=1);

namespace pocketmine\block;

use pocketmine\event\block\BlockGrowEvent;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Player;
use function mt_rand;

abstract class Crops extends Flowable{

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
		if($blockReplace->getSide(Vector3::SIDE_DOWN)->getId() === Block::FARMLAND){
			$this->getLevel()->setBlock($blockReplace, $this, true, true);

			return true;
		}

		return false;
	}


	public function onActivate(Item $item, Player $player = null) : bool{
		if($item->getId() === Item::DYE and $item->getDamage() === 0x0F){ //Bonemeal
			$block = clone $this;
			$block->meta += mt_rand(2, 5);
			if($block->meta > 7){
				$block->meta = 7;
			}

			$ev = new BlockGrowEvent($this, $block);
			$ev->call();
			if(!$ev->isCancelled()){
				$this->getLevel()->setBlock($this, $ev->getNewState(), true, true);
			}

			$item->count--;

			return true;
		}

		return false;
	}

	public function onNearbyBlockChange() : void{
		if($this->getSide(Vector3::SIDE_DOWN)->getId() !== Block::FARMLAND){
			$this->getLevel()->useBreakOn($this);
		}
	}

	public function ticksRandomly() : bool{
		return true;
	}

	public function onRandomTick() : void{
		if(mt_rand(0, 2) === 1){
			if($this->meta < 0x07){
				$block = clone $this;
				++$block->meta;
				$ev = new BlockGrowEvent($this, $block);
				$ev->call();
				if(!$ev->isCancelled()){
					$this->getLevel()->setBlock($this, $ev->getNewState(), true, true);
				}
			}
		}
	}

	public function isAffectedBySilkTouch() : bool{
		return false;
	}
}
