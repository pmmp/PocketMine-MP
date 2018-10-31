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
use pocketmine\item\ItemFactory;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\Player;

class NetherWartPlant extends Flowable{
	protected $id = Block::NETHER_WART_PLANT;

	protected $itemId = Item::NETHER_WART;

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
		return 0b11;
	}

	public function getName() : string{
		return "Nether Wart";
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
		$down = $this->getSide(Facing::DOWN);
		if($down->getId() === Block::SOUL_SAND){
			return parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
		}

		return false;
	}

	public function onNearbyBlockChange() : void{
		if($this->getSide(Facing::DOWN)->getId() !== Block::SOUL_SAND){
			$this->getLevel()->useBreakOn($this);
		}
	}

	public function ticksRandomly() : bool{
		return true;
	}

	public function onRandomTick() : void{
		if($this->age < 3 and mt_rand(0, 10) === 0){ //Still growing
			$block = clone $this;
			$block->age++;
			$ev = new BlockGrowEvent($this, $block);
			$ev->call();
			if(!$ev->isCancelled()){
				$this->getLevel()->setBlock($this, $ev->getNewState());
			}
		}
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [
			ItemFactory::get($this->getItemId(), 0, ($this->age === 3 ? mt_rand(2, 4) : 1))
		];
	}

	public function isAffectedBySilkTouch() : bool{
		return false;
	}
}
