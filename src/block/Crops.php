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
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\Player;

abstract class Crops extends Flowable{
	/** @var int */
	protected $age = 0;

	public function __construct(){

	}

	protected function writeStateToMeta() : int{
		return $this->age;
	}

	public function readStateFromMeta(int $meta) : void{
		$this->age = $meta;
	}

	public function getStateBitmask() : int{
		return 0b111;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
		if($blockReplace->getSide(Facing::DOWN)->getId() === Block::FARMLAND){
			return parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
		}

		return false;
	}


	public function onActivate(Item $item, Player $player = null) : bool{
		if($item->getId() === Item::DYE and $item->getDamage() === 0x0F){ //Bonemeal
			$block = clone $this;
			$block->age += mt_rand(2, 5);
			if($block->age > 7){
				$block->age = 7;
			}

			$ev = new BlockGrowEvent($this, $block);
			$ev->call();
			if(!$ev->isCancelled()){
				$this->getLevel()->setBlock($this, $ev->getNewState());
			}

			$item->pop();

			return true;
		}

		return false;
	}

	public function onNearbyBlockChange() : void{
		if($this->getSide(Facing::DOWN)->getId() !== Block::FARMLAND){
			$this->getLevel()->useBreakOn($this);
		}
	}

	public function ticksRandomly() : bool{
		return true;
	}

	public function onRandomTick() : void{
		if($this->age < 7 and mt_rand(0, 2) === 1){
			$block = clone $this;
			++$block->age;
			$ev = new BlockGrowEvent($this, $block);
			$ev->call();
			if(!$ev->isCancelled()){
				$this->getLevel()->setBlock($this, $ev->getNewState());
			}
		}
	}

	public function isAffectedBySilkTouch() : bool{
		return false;
	}
}
