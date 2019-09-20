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
use pocketmine\math\Vector3;
use function mt_rand;

class MelonStem extends Crops{

	protected $id = self::MELON_STEM;

	public function getName() : string{
		return "Melon Stem";
	}

	public function __construct(int $meta = 0){
		$this->meta = $meta;
	}

	public function onRandomTick() : void{
		if(mt_rand(0, 2) === 1){
			if($this->meta < 0x07){
				$block = clone $this;
				++$block->meta;
				$ev = new BlockGrowEvent($this, $block);
				$ev->call();
				if(!$ev->isCancelled()){
					$this->getLevel()->setBlock($this, $ev->getNewState(), true);
				}
			}else{
				for($side = 2; $side <= 5; ++$side){
					$b = $this->getSide($side);
					if($b->getId() === self::MELON_BLOCK){
						return;
					}
				}
				$side = $this->getSide(mt_rand(2, 5));
				$d = $side->getSide(Vector3::SIDE_DOWN);
				if($side->getId() === self::AIR and ($d->getId() === self::FARMLAND or $d->getId() === self::GRASS or $d->getId() === self::DIRT)){
					$ev = new BlockGrowEvent($side, BlockFactory::get(Block::MELON_BLOCK));
					$ev->call();
					if(!$ev->isCancelled()){
						$this->getLevel()->setBlock($side, $ev->getNewState(), true);
					}
				}
			}
		}
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [
			ItemFactory::get(Item::MELON_SEEDS, 0, mt_rand(0, 2))
		];
	}

	public function getPickedItem() : Item{
		return ItemFactory::get(Item::MELON_SEEDS);
	}
}
