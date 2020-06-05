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

use pocketmine\event\block\BlockSpreadEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\math\Vector3;
use function mt_rand;

class Mycelium extends Solid{

	protected $id = self::MYCELIUM;

	public function __construct(int $meta = 0){
		$this->meta = $meta;
	}

	public function getName() : string{
		return "Mycelium";
	}

	public function getToolType() : int{
		return BlockToolType::TYPE_SHOVEL;
	}

	public function getHardness() : float{
		return 0.6;
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [
			ItemFactory::get(Item::DIRT)
		];
	}

	public function ticksRandomly() : bool{
		return true;
	}

	public function onRandomTick() : void{
		//TODO: light levels
		$x = mt_rand($this->x - 1, $this->x + 1);
		$y = mt_rand($this->y - 2, $this->y + 2);
		$z = mt_rand($this->z - 1, $this->z + 1);
		$block = $this->getLevelNonNull()->getBlockAt($x, $y, $z);
		if($block->getId() === Block::DIRT){
			if($block->getSide(Vector3::SIDE_UP) instanceof Transparent){
				$ev = new BlockSpreadEvent($block, $this, BlockFactory::get(Block::MYCELIUM));
				$ev->call();
				if(!$ev->isCancelled()){
					$this->getLevelNonNull()->setBlock($block, $ev->getNewState());
				}
			}
		}
	}
}
