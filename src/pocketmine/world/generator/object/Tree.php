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

namespace pocketmine\world\generator\object;

use pocketmine\block\Block;
use pocketmine\block\Leaves;
use pocketmine\block\Sapling;
use pocketmine\block\utils\TreeType;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;
use function abs;

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

	/**
	 * @param ChunkManager  $world
	 * @param int           $x
	 * @param int           $y
	 * @param int           $z
	 * @param Random        $random
	 * @param TreeType|null $type default oak
	 *
	 * @throws \InvalidArgumentException
	 */
	public static function growTree(ChunkManager $world, int $x, int $y, int $z, Random $random, ?TreeType $type = null) : void{
		/** @var null|Tree $tree */
		$tree = null;
		$type = $type ?? TreeType::OAK();
		if($type->equals(TreeType::SPRUCE())){
			$tree = new SpruceTree();
		}elseif($type->equals(TreeType::BIRCH())){
			if($random->nextBoundedInt(39) === 0){
				$tree = new BirchTree(true);
			}else{
				$tree = new BirchTree();
			}
		}elseif($type->equals(TreeType::JUNGLE())){
			$tree = new JungleTree();
		}elseif($type->equals(TreeType::OAK())){ //default
			$tree = new OakTree();
			/*if($random->nextRange(0, 9) === 0){
				$tree = new BigTree();
			}else{*/

			//}
		}

		if($tree !== null and $tree->canPlaceObject($world, $x, $y, $z, $random)){
			$tree->placeObject($world, $x, $y, $z, $random);
		}
	}


	public function canPlaceObject(ChunkManager $world, int $x, int $y, int $z, Random $random) : bool{
		$radiusToCheck = 0;
		for($yy = 0; $yy < $this->treeHeight + 3; ++$yy){
			if($yy === 1 or $yy === $this->treeHeight){
				++$radiusToCheck;
			}
			for($xx = -$radiusToCheck; $xx < ($radiusToCheck + 1); ++$xx){
				for($zz = -$radiusToCheck; $zz < ($radiusToCheck + 1); ++$zz){
					if(!$this->canOverride($world->getBlockAt($x + $xx, $y + $yy, $z + $zz))){
						return false;
					}
				}
			}
		}

		return true;
	}

	public function placeObject(ChunkManager $world, int $x, int $y, int $z, Random $random) : void{
		$transaction = new BlockTransaction($world);
		$this->placeTrunk($x, $y, $z, $random, $this->generateChunkHeight($random), $transaction);
		$this->placeCanopy($x, $y, $z, $random, $transaction);

		$transaction->apply(); //TODO: handle return value on failure
	}

	protected function generateChunkHeight(Random $random) : int{
		return $this->treeHeight - 1;
	}

	protected function placeTrunk(int $x, int $y, int $z, Random $random, int $trunkHeight, BlockTransaction $transaction) : void{
		// The base dirt block
		$transaction->addBlockAt($x, $y - 1, $z, VanillaBlocks::DIRT());

		for($yy = 0; $yy < $trunkHeight; ++$yy){
			if($this->canOverride($transaction->fetchBlockAt($x, $y + $yy, $z))){
				$transaction->addBlockAt($x, $y + $yy, $z, $this->trunkBlock);
			}
		}
	}

	protected function placeCanopy(int $x, int $y, int $z, Random $random, BlockTransaction $transaction) : void{
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
					if(!$transaction->fetchBlockAt($xx, $yy, $zz)->isSolid()){
						$transaction->addBlockAt($xx, $yy, $zz, $this->leafBlock);
					}
				}
			}
		}
	}

	protected function canOverride(Block $block) : bool{
		return $block->canBeReplaced() or $block instanceof Sapling or $block instanceof Leaves;
	}
}
