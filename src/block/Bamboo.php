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

use pocketmine\block\utils\BlockDataSerializer;
use pocketmine\item\Bamboo as ItemBamboo;
use pocketmine\item\Fertilizer;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use function count;
use function gmp_add;
use function gmp_and;
use function gmp_intval;
use function gmp_mul;
use function gmp_xor;
use function min;
use function mt_rand;
use const PHP_INT_MAX;

class Bamboo extends Transparent{

	public const NO_LEAVES = 0;
	public const SMALL_LEAVES = 1;
	public const LARGE_LEAVES = 2;

	protected bool $thick = false; //age in PC, but this is 0/1
	protected bool $ready = false;
	protected int $leafSize = self::NO_LEAVES;

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->thick = ($stateMeta & BlockLegacyMetadata::BAMBOO_FLAG_THICK) !== 0;
		$this->leafSize = BlockDataSerializer::readBoundedInt("leafSize", ($stateMeta >> BlockLegacyMetadata::BAMBOO_LEAF_SIZE_SHIFT) & BlockLegacyMetadata::BAMBOO_LEAF_SIZE_MASK, self::NO_LEAVES, self::LARGE_LEAVES);
		$this->ready = ($stateMeta & BlockLegacyMetadata::BAMBOO_FLAG_READY) !== 0;
	}

	public function writeStateToMeta() : int{
		return ($this->thick ? BlockLegacyMetadata::BAMBOO_FLAG_THICK : 0) | ($this->leafSize << BlockLegacyMetadata::BAMBOO_LEAF_SIZE_SHIFT) | ($this->ready ? BlockLegacyMetadata::BAMBOO_FLAG_READY : 0);
	}

	public function getStateBitmask() : int{
		return 0b1111;
	}

	public function isThick() : bool{ return $this->thick; }

	/** @return $this */
	public function setThick(bool $thick) : self{
		$this->thick = $thick;
		return $this;
	}

	public function isReady() : bool{ return $this->ready; }

	/** @return $this */
	public function setReady(bool $ready) : self{
		$this->ready = $ready;
		return $this;
	}

	public function getLeafSize() : int{ return $this->leafSize; }

	/** @return $this */
	public function setLeafSize(int $leafSize) : self{
		$this->leafSize = $leafSize;
		return $this;
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array{
		//this places the BB at the northwest corner, not the center
		$inset = 1 - (($this->thick ? 3 : 2) / 16);
		return [AxisAlignedBB::one()->trim(Facing::SOUTH, $inset)->trim(Facing::EAST, $inset)];
	}

	private static function getOffsetSeed(int $x, int $y, int $z) : int{
		$p1 = gmp_mul($z, 0x6ebfff5);
		$p2 = gmp_mul($x, 0x2fc20f);
		$p3 = $y;

		$xord = gmp_xor(gmp_xor($p1, $p2), $p3);

		$fullResult = gmp_mul(gmp_add(gmp_mul($xord, 0x285b825), 0xb), $xord);
		return gmp_intval(gmp_and($fullResult, 0xffffffff));
	}

	private static function getMaxHeight(int $x, int $z) : int{
		return 12 + (self::getOffsetSeed($x, 0, $z) % 5);
	}

	public function getPosOffset() : ?Vector3{
		$seed = self::getOffsetSeed($this->pos->getFloorX(), 0, $this->pos->getFloorZ());
		$retX = (($seed % 12) + 1) / 16;
		$retZ = ((($seed >> 8) % 12) + 1) / 16;
		return new Vector3($retX, 0, $retZ);
	}

	private function canBeSupportedBy(Block $block) : bool{
		//TODO: tags would be better for this
		return
			$block instanceof Dirt ||
			$block instanceof Grass ||
			$block instanceof Gravel ||
			$block instanceof Sand ||
			$block instanceof Mycelium ||
			$block instanceof Podzol;
	}

	private function seekToTop() : Bamboo{
		$world = $this->pos->getWorld();
		$top = $this;
		while(($next = $world->getBlock($top->pos->up())) instanceof Bamboo && $next->isSameType($this)){
			$top = $next;
		}
		return $top;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($item instanceof Fertilizer){
			$top = $this->seekToTop();
			if($top->grow(self::getMaxHeight($top->pos->getFloorX(), $top->pos->getFloorZ()), mt_rand(1, 2))){
				$item->pop();
				return true;
			}
		}elseif($item instanceof ItemBamboo){
			if($this->seekToTop()->grow(PHP_INT_MAX, 1)){
				$item->pop();
				return true;
			}
		}
		return false;
	}

	public function onNearbyBlockChange() : void{
		$below = $this->pos->getWorld()->getBlock($this->pos->down());
		if(!$this->canBeSupportedBy($below) and !$below->isSameType($this)){
			$this->pos->getWorld()->useBreakOn($this->pos);
		}
	}

	private function grow(int $maxHeight, int $growAmount) : bool{
		$world = $this->pos->getWorld();
		if(!$world->getBlock($this->pos->up())->canBeReplaced()){
			return false;
		}

		$height = 1;
		while($world->getBlock($this->pos->subtract(0, $height, 0))->isSameType($this)){
			if(++$height >= $maxHeight){
				return false;
			}
		}

		$newHeight = $height + $growAmount;

		$stemBlock = (clone $this)->setReady(false)->setLeafSize(self::NO_LEAVES);
		if($newHeight >= 4 && !$stemBlock->isThick()){ //don't change it to false if height is less, because it might have been chopped
			$stemBlock = $stemBlock->setThick(true);
		}
		$smallLeavesBlock = (clone $stemBlock)->setLeafSize(self::SMALL_LEAVES);
		$bigLeavesBlock = (clone $stemBlock)->setLeafSize(self::LARGE_LEAVES);

		$newBlocks = [];
		if($newHeight === 2){
			$newBlocks[] = $smallLeavesBlock;
		}elseif($newHeight === 3){
			$newBlocks[] = $smallLeavesBlock;
			$newBlocks[] = $smallLeavesBlock;
		}elseif($newHeight === 4){
			$newBlocks[] = $bigLeavesBlock;
			$newBlocks[] = $smallLeavesBlock;
			$newBlocks[] = $stemBlock;
			$newBlocks[] = $stemBlock;
		}elseif($newHeight > 4){
			$newBlocks[] = $bigLeavesBlock;
			$newBlocks[] = $bigLeavesBlock;
			$newBlocks[] = $smallLeavesBlock;
			for($i = 0, $max = min($growAmount, $newHeight - count($newBlocks)); $i < $max; ++$i){
				$newBlocks[] = $stemBlock; //to replace the bottom blocks that currently have leaves
			}
		}

		$tx = new BlockTransaction($this->pos->getWorld());
		foreach($newBlocks as $idx => $newBlock){
			$tx->addBlock($this->pos->subtract(0, $idx - $growAmount, 0), $newBlock);
		}
		return $tx->apply();
	}

	public function ticksRandomly() : bool{
		return true;
	}

	public function onRandomTick() : void{
		$world = $this->pos->getWorld();
		if($this->ready){
			$this->ready = false;
			if($world->getFullLight($this->pos) < 9 || !$this->grow(self::getMaxHeight($this->pos->getFloorX(), $this->pos->getFloorZ()), 1)){
				$world->setBlock($this->pos, $this);
			}
		}elseif($world->getBlock($this->pos->up())->canBeReplaced()){
			$this->ready = true;
			$world->setBlock($this->pos, $this);
		}
	}
}
