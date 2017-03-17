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
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\Player;

class NetherWartPlant extends Flowable{
	protected $id = Block::NETHER_WART_PLANT;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$down = $this->getSide(Vector3::SIDE_DOWN);
		if($down->getId() === Block::SOUL_SAND){
			$this->getLevel()->setBlock($block, $this, false, true);

			return true;
		}

		return false;
	}

	public function onUpdate($type){
		switch($type){
			case Level::BLOCK_UPDATE_RANDOM:
				if($this->meta < 3 and mt_rand(0, 10) === 0){ //Still growing
					$block = clone $this;
					$block->meta++;
					$this->getLevel()->getServer()->getPluginManager()->callEvent($ev = new BlockGrowEvent($this, $block));

					if(!$ev->isCancelled()){
						$this->getLevel()->setBlock($this, $ev->getNewState(), false, true);

						return $type;
					}
				}
				break;
			case Level::BLOCK_UPDATE_NORMAL:
				if($this->getSide(Vector3::SIDE_DOWN)->getId() !== Block::SOUL_SAND){
					$this->getLevel()->useBreakOn($this);
					return $type;
				}
				break;
		}

		return false;
	}

	public function getDrops(Item $item){
		return [[Item::NETHER_WART, 0, ($this->meta === 3 ? mt_rand(2, 4) : 1)]];
	}
}