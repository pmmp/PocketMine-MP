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
use pocketmine\item\Fertilizer;
use pocketmine\item\Hoe;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\Shovel;
use pocketmine\level\generator\object\TallGrass as TallGrassObject;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\utils\Random;
use function mt_rand;

class Grass extends Solid{

	public function getHardness() : float{
		return 0.6;
	}

	public function getToolType() : int{
		return BlockToolType::TYPE_SHOVEL;
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
		$lightAbove = $this->level->getFullLightAt($this->x, $this->y + 1, $this->z);
		if($lightAbove < 4 and $this->level->getBlockAt($this->x, $this->y + 1, $this->z)->getLightFilter() >= 2){
			//grass dies
			$ev = new BlockSpreadEvent($this, $this, BlockFactory::get(Block::DIRT));
			$ev->call();
			if(!$ev->isCancelled()){
				$this->level->setBlock($this, $ev->getNewState(), false);
			}
		}elseif($lightAbove >= 9){
			//try grass spread
			for($i = 0; $i < 4; ++$i){
				$x = mt_rand($this->x - 1, $this->x + 1);
				$y = mt_rand($this->y - 3, $this->y + 1);
				$z = mt_rand($this->z - 1, $this->z + 1);

				$b = $this->level->getBlockAt($x, $y, $z);
				if(
					!($b instanceof Dirt) or
					$b instanceof CoarseDirt or
					$this->level->getFullLightAt($x, $y + 1, $z) < 4 or
					$this->level->getBlockAt($x, $y + 1, $z)->getLightFilter() >= 2
				){
					continue;
				}

				$ev = new BlockSpreadEvent($b, $this, BlockFactory::get(Block::GRASS));
				$ev->call();
				if(!$ev->isCancelled()){
					$this->level->setBlock($b, $ev->getNewState(), false);
				}
			}
		}
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($face !== Facing::UP){
			return false;
		}
		if($item instanceof Fertilizer){
			$item->pop();
			TallGrassObject::growGrass($this->getLevel(), $this, new Random(mt_rand()), 8, 2);

			return true;
		}elseif($item instanceof Hoe){
			$item->applyDamage(1);
			$this->getLevel()->setBlock($this, BlockFactory::get(Block::FARMLAND));

			return true;
		}elseif($item instanceof Shovel and $this->getSide(Facing::UP)->getId() === Block::AIR){
			$item->applyDamage(1);
			$this->getLevel()->setBlock($this, BlockFactory::get(Block::GRASS_PATH));

			return true;
		}

		return false;
	}
}
