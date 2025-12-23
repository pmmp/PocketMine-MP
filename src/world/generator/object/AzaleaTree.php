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
use pocketmine\block\BlockTypeIds;
use pocketmine\block\utils\DirtType;
use pocketmine\block\VanillaBlocks;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;
use pocketmine\world\World;
use function count;
use function max;
use function min;

final class AzaleaTree extends Tree{
	/** @var Vector3[] */
	private array $foliageAttachments = [];

	private const TREE_HEIGHT_BASE = 4;
	private const TREE_HEIGHT_RANDOM = 3;
	private const MIN_HEIGHT_FOR_LEAVES = 3;

	private const LEADUP_SMALL = 2;
	private const LEADUP_LARGE = 3;
	private const SIDE_UP_STEPS = 2;

	public function __construct(){
		parent::__construct(VanillaBlocks::OAK_LOG(), VanillaBlocks::AZALEA_LEAVES(), 0);
	}

	public function getBlockTransaction(ChunkManager $world, int $x, int $y, int $z, Random $random) : ?BlockTransaction{
		$this->treeHeight = $random->nextBoundedInt(self::TREE_HEIGHT_RANDOM) + self::TREE_HEIGHT_BASE;
		$this->foliageAttachments = [];
		return parent::getBlockTransaction($world, $x, $y, $z, $random);
	}

	protected function generateTrunkHeight(Random $random) : int{
		return min(self::TREE_HEIGHT_BASE + $random->nextBoundedInt(2), 5);
	}

	protected function placeTrunk(int $x, int $y, int $z, Random $random, int $trunkHeight, BlockTransaction $transaction) : void{
		$transaction->addBlockAt($x, $y - 1, $z, VanillaBlocks::DIRT()->setDirtType(DirtType::ROOTED));

		$direction = Facing::HORIZONTAL[$random->nextRange(0, count(Facing::HORIZONTAL) - 1)];
		$cx = $x;
		$cy = $y;
		$cz = $z;

		$sideUpCount = min(self::SIDE_UP_STEPS, max(0, $trunkHeight - self::LEADUP_SMALL));
		$leadUpCount = min($trunkHeight - $sideUpCount, self::LEADUP_LARGE);
		$total = $leadUpCount + $sideUpCount + 1;
		if($total < $trunkHeight){
			$leadUpCount += ($trunkHeight - $total);
		}

		for($i = 0; $i < $total; ++$i){
			$isLeadUp = $i < $leadUpCount;
			$isSideUp = $i < $leadUpCount + $sideUpCount - 1;

			if(!$isLeadUp){
				$cx += Facing::OFFSET[$direction][0];
				$cz += Facing::OFFSET[$direction][2];
			}

			if($this->canOverride($transaction->fetchBlockAt($cx, $cy, $cz))){
				$transaction->addBlockAt($cx, $cy, $cz, $this->trunkBlock);
			}

			if($i >= self::MIN_HEIGHT_FOR_LEAVES){
				$this->foliageAttachments[] = new Vector3($cx, $cy, $cz);
			}

			if($isLeadUp || $isSideUp){
				$cy++;
			}
		}
	}

	protected function placeCanopy(int $x, int $y, int $z, Random $random, BlockTransaction $transaction) : void{
		$radius = 3;
		$foliageHeight = 2;
		$attempts = 50;

		$visited = [];
		foreach($this->foliageAttachments as $attachment){
			$centerX = $attachment->getFloorX();
			$centerY = $attachment->getFloorY();
			$centerZ = $attachment->getFloorZ();

			for($a = 0; $a < $attempts; ++$a){
				$dx = $random->nextBoundedInt($radius) - $random->nextBoundedInt($radius);
				$dy = $random->nextBoundedInt($foliageHeight) - $random->nextBoundedInt($foliageHeight);
				$dz = $random->nextBoundedInt($radius) - $random->nextBoundedInt($radius);

				$xx = $centerX + $dx;
				$yy = $centerY + $dy;
				$zz = $centerZ + $dz;

				$hash = World::blockHash($xx, $yy, $zz);
				if(isset($visited[$hash])){
					continue;
				}
				$visited[$hash] = true;

				$existing = $transaction->fetchBlockAt($xx, $yy, $zz);
				if($existing->isTransparent()){
					$leafBlock = ($random->nextBoundedInt(4) === 0) ? VanillaBlocks::FLOWERING_AZALEA_LEAVES() : VanillaBlocks::AZALEA_LEAVES();
					$transaction->addBlockAt($xx, $yy, $zz, $leafBlock);
				}
			}
		}
	}

	protected function canOverride(Block $block) : bool{
		return parent::canOverride($block)
			|| $block->getTypeId() === BlockTypeIds::AZALEA
			|| $block->getTypeId() === BlockTypeIds::FLOWERING_AZALEA;
	}
}
