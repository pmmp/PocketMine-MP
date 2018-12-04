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

namespace pocketmine\level\generator\object;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\Wood;
use pocketmine\level\ChunkManager;
use pocketmine\utils\Random;

class SpruceTree extends Tree{

	public function __construct(){
		parent::__construct(BlockFactory::get(Block::LOG, Wood::SPRUCE), BlockFactory::get(Block::LEAVES, Wood::SPRUCE), 10);
	}

	public function placeObject(ChunkManager $level, int $x, int $y, int $z, Random $random) : void{
		$this->treeHeight = $random->nextBoundedInt(4) + 6;

		$topSize = $this->treeHeight - (1 + $random->nextBoundedInt(2));
		$lRadius = 2 + $random->nextBoundedInt(2);

		$this->placeTrunk($level, $x, $y, $z, $random, $this->treeHeight - $random->nextBoundedInt(3));

		$radius = $random->nextBoundedInt(2);
		$maxR = 1;
		$minR = 0;

		for($yy = 0; $yy <= $topSize; ++$yy){
			$yyy = $y + $this->treeHeight - $yy;

			for($xx = $x - $radius; $xx <= $x + $radius; ++$xx){
				$xOff = abs($xx - $x);
				for($zz = $z - $radius; $zz <= $z + $radius; ++$zz){
					$zOff = abs($zz - $z);
					if($xOff === $radius and $zOff === $radius and $radius > 0){
						continue;
					}

					if(!$level->getBlockAt($xx, $yyy, $zz)->isSolid()){
						$level->setBlockAt($xx, $yyy, $zz, $this->leafBlock);
					}
				}
			}

			if($radius >= $maxR){
				$radius = $minR;
				$minR = 1;
				if(++$maxR > $lRadius){
					$maxR = $lRadius;
				}
			}else{
				++$radius;
			}
		}
	}
}
