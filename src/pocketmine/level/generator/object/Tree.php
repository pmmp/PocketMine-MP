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
use pocketmine\block\Leaves;
use pocketmine\block\Sapling;
use pocketmine\block\utils\WoodType;
use pocketmine\block\Wood;
use pocketmine\level\ChunkManager;
use pocketmine\utils\Random;

abstract class Tree{
	/** @var Block */
	protected $trunkBlock;
	/** @var Block */
	protected $leafBlock;

	/** @var int */
	protected $treeHeight;

	public function __construct(Block $trunkBlock, Block $leafBlock, int $treeHeight = 7){
		$this->trunkBlock = $trunkBlock;
		$this->leafBlock = $leafBlock;

		$this->treeHeight = $treeHeight;
	}

	public static function growTree(ChunkManager $level, int $x, int $y, int $z, Random $random, int $type = WoodType::OAK) : void{
		switch($type){
			case WoodType::SPRUCE:
				$tree = new SpruceTree();
				break;
			case WoodType::BIRCH:
				if($random->nextBoundedInt(39) === 0){
					$tree = new BirchTree(true);
				}else{
					$tree = new BirchTree();
				}
				break;
			case WoodType::JUNGLE:
				$tree = new JungleTree();
				break;
			case WoodType::ACACIA:
			case WoodType::DARK_OAK:
				return; //TODO
			default:
				$tree = new OakTree();
				/*if($random->nextRange(0, 9) === 0){
					$tree = new BigTree();
				}else{*/

				//}
				break;
		}
		if($tree->canPlaceObject($level, $x, $y, $z, $random)){
			$tree->placeObject($level, $x, $y, $z, $random);
		}
	}


	public function canPlaceObject(ChunkManager $level, int $x, int $y, int $z, Random $random) : bool{
		$radiusToCheck = 0;
		for($yy = 0; $yy < $this->treeHeight + 3; ++$yy){
			if($yy === 1 or $yy === $this->treeHeight){
				++$radiusToCheck;
			}
			for($xx = -$radiusToCheck; $xx < ($radiusToCheck + 1); ++$xx){
				for($zz = -$radiusToCheck; $zz < ($radiusToCheck + 1); ++$zz){
					if(!$this->canOverride($level->getBlockAt($x + $xx, $y + $yy, $z + $zz))){
						return false;
					}
				}
			}
		}

		return true;
	}

	public function placeObject(ChunkManager $level, int $x, int $y, int $z, Random $random) : void{
		$this->placeTrunk($level, $x, $y, $z, $random, $this->treeHeight - 1);

		for($yy = $y - 3 + $this->treeHeight; $yy <= $y + $this->treeHeight; ++$yy){
			$yOff = $yy - ($y + $this->treeHeight);
			$mid = (int) (1 - $yOff / 2);
			for($xx = $x - $mid; $xx <= $x + $mid; ++$xx){
				$xOff = abs($xx - $x);
				for($zz = $z - $mid; $zz <= $z + $mid; ++$zz){
					$zOff = abs($zz - $z);
					if($xOff === $mid and $zOff === $mid and ($yOff === 0 or $random->nextBoundedInt(2) === 0)){
						continue;
					}
					if(!$level->getBlockAt($xx, $yy, $zz)->isSolid()){
						$level->setBlockAt($xx, $yy, $zz, $this->leafBlock);
					}
				}
			}
		}
	}

	protected function placeTrunk(ChunkManager $level, int $x, int $y, int $z, Random $random, int $trunkHeight) : void{
		// The base dirt block
		$level->setBlockAt($x, $y - 1, $z, BlockFactory::get(Block::DIRT));

		for($yy = 0; $yy < $trunkHeight; ++$yy){
			if($this->canOverride($level->getBlockAt($x, $y + $yy, $z))){
				$level->setBlockAt($x, $y + $yy, $z, $this->trunkBlock);
			}
		}
	}

	protected function canOverride(Block $block) : bool{
		return $block->canBeReplaced() or $block instanceof Wood or $block instanceof Sapling or $block instanceof Leaves;
	}
}
