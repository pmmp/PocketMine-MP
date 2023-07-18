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

use pocketmine\block\VanillaBlocks;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;
use pocketmine\world\BlockTransaction;
use function abs;
use function array_rand;

final class AcaciaTree extends Tree{
	private const MIN_HEIGHT = 5;

	private ?Vector3 $mainBranchTip = null;
	private ?Vector3 $secondBranchTip = null;

	public function __construct(){
		parent::__construct(
			VanillaBlocks::ACACIA_LOG(),
			VanillaBlocks::ACACIA_LEAVES(),
			0 //we don't use this anyway - everything is overridden
		);
	}

	protected function generateTrunkHeight(Random $random) : int{
		//50% chance of 2 extra blocks, 33% chance 1 or 3, 17% chance 0 or 4
		return self::MIN_HEIGHT + $random->nextRange(0, 2) + $random->nextRange(0, 2);
	}

	protected function placeTrunk(int $x, int $y, int $z, Random $random, int $trunkHeight, BlockTransaction $transaction) : void{
		// The base dirt block
		$transaction->addBlockAt($x, $y - 1, $z, VanillaBlocks::DIRT());

		$firstBranchHeight = $trunkHeight - 1 - $random->nextRange(0, 3);

		for($yy = 0; $yy <= $firstBranchHeight; ++$yy){
			$transaction->addBlockAt($x, $y + $yy, $z, $this->trunkBlock);
		}

		$mainBranchFacing = Facing::HORIZONTAL[array_rand(Facing::HORIZONTAL)];

		//this branch may grow a second trunk if the diagonal length is less than the max length
		$this->mainBranchTip = $this->placeBranch(
			$transaction,
			new Vector3($x, $y + $firstBranchHeight, $z),
			$mainBranchFacing,
			$random->nextRange(1, 3),
			$trunkHeight - $firstBranchHeight
		);

		$secondBranchFacing = Facing::HORIZONTAL[array_rand(Facing::HORIZONTAL)];
		if($secondBranchFacing !== $mainBranchFacing){
			$secondBranchLength = $random->nextRange(1, 3);
			$this->secondBranchTip = $this->placeBranch(
				$transaction,
				new Vector3($x, $y + ($firstBranchHeight - $random->nextRange(0, 1)), $z),
				$secondBranchFacing,
				$secondBranchLength,
				$secondBranchLength //the secondary branch may not form a second trunk
			);
		}
	}

	protected function placeBranch(BlockTransaction $transaction, Vector3 $start, int $branchFacing, int $maxDiagonal, int $length) : Vector3{
		$diagonalPlaced = 0;

		$nextBlockPos = $start;
		for($yy = 0; $yy < $length; $yy++){
			$nextBlockPos = $nextBlockPos->up();
			if($diagonalPlaced < $maxDiagonal){
				$nextBlockPos = $nextBlockPos->getSide($branchFacing);
				$diagonalPlaced++;
			}
			$transaction->addBlock($nextBlockPos, $this->trunkBlock);
		}

		return $nextBlockPos;
	}

	protected function placeCanopyLayer(BlockTransaction $transaction, Vector3 $center, int $radius, int $maxTaxicabDistance) : void{
		$centerX = $center->getFloorX();
		$centerY = $center->getFloorY();
		$centerZ = $center->getFloorZ();

		for($x = $centerX - $radius; $x <= $centerX + $radius; ++$x){
			for($z = $centerZ - $radius; $z <= $centerZ + $radius; ++$z){
				if(
					abs($x - $centerX) + abs($z - $centerZ) <= $maxTaxicabDistance &&
					$transaction->fetchBlockAt($x, $centerY, $z)->canBeReplaced()
				){
					$transaction->addBlockAt($x, $centerY, $z, $this->leafBlock);
				}
			}
		}
	}

	protected function placeCanopy(int $x, int $y, int $z, Random $random, BlockTransaction $transaction) : void{
		$mainBranchTip = $this->mainBranchTip;
		if($mainBranchTip !== null){
			$this->placeCanopyLayer($transaction, $mainBranchTip, radius: 3, maxTaxicabDistance: 5);
			$this->placeCanopyLayer($transaction, $mainBranchTip->up(), radius: 2, maxTaxicabDistance: 2);
		}
		$secondBranchTip = $this->secondBranchTip;
		if($secondBranchTip !== null){
			$this->placeCanopyLayer($transaction, $secondBranchTip, radius: 2, maxTaxicabDistance: 3);
			$this->placeCanopyLayer($transaction, $secondBranchTip->up(), radius: 1, maxTaxicabDistance: 2);
		}
	}
}
