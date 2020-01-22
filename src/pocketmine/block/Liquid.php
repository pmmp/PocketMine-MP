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

use pocketmine\entity\Entity;
use pocketmine\event\block\BlockFormEvent;
use pocketmine\event\block\BlockSpreadEvent;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\level\sound\FizzSound;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use function array_fill;
use function intdiv;
use function lcg_value;
use function min;

abstract class Liquid extends Transparent{

	/** @var int */
	public $adjacentSources = 0;

	/** @var Vector3|null */
	protected $flowVector = null;

	/** @var int[] */
	private $flowCostVisited = [];

	private const CAN_FLOW_DOWN = 1;
	private const CAN_FLOW = 0;
	private const BLOCKED = -1;

	public function hasEntityCollision() : bool{
		return true;
	}

	public function isBreakable(Item $item) : bool{
		return false;
	}

	public function canBeReplaced() : bool{
		return true;
	}

	public function canBeFlowedInto() : bool{
		return true;
	}

	public function isSolid() : bool{
		return false;
	}

	public function getHardness() : float{
		return 100;
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB{
		return null;
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [];
	}

	abstract public function getStillForm() : Block;

	abstract public function getFlowingForm() : Block;

	abstract public function getBucketFillSound() : int;

	abstract public function getBucketEmptySound() : int;

	/**
	 * @return float
	 */
	public function getFluidHeightPercent(){
		$d = $this->meta;
		if($d >= 8){
			$d = 0;
		}

		return ($d + 1) / 9;
	}

	protected function getFlowDecay(Block $block) : int{
		if($block->getId() !== $this->getId()){
			return -1;
		}

		return $block->getDamage();
	}

	protected function getEffectiveFlowDecay(Block $block) : int{
		if($block->getId() !== $this->getId()){
			return -1;
		}

		$decay = $block->getDamage();

		if($decay >= 8){
			$decay = 0;
		}

		return $decay;
	}

	public function clearCaches() : void{
		parent::clearCaches();
		$this->flowVector = null;
	}

	public function getFlowVector() : Vector3{
		if($this->flowVector !== null){
			return $this->flowVector;
		}

		$vector = new Vector3(0, 0, 0);

		$decay = $this->getEffectiveFlowDecay($this);

		for($j = 0; $j < 4; ++$j){

			$x = $this->x;
			$y = $this->y;
			$z = $this->z;

			if($j === 0){
				--$x;
			}elseif($j === 1){
				++$x;
			}elseif($j === 2){
				--$z;
			}elseif($j === 3){
				++$z;
			}
			$sideBlock = $this->level->getBlockAt($x, $y, $z);
			$blockDecay = $this->getEffectiveFlowDecay($sideBlock);

			if($blockDecay < 0){
				if(!$sideBlock->canBeFlowedInto()){
					continue;
				}

				$blockDecay = $this->getEffectiveFlowDecay($this->level->getBlockAt($x, $y - 1, $z));

				if($blockDecay >= 0){
					$realDecay = $blockDecay - ($decay - 8);
					$vector->x += ($sideBlock->x - $this->x) * $realDecay;
					$vector->y += ($sideBlock->y - $this->y) * $realDecay;
					$vector->z += ($sideBlock->z - $this->z) * $realDecay;
				}

				continue;
			}else{
				$realDecay = $blockDecay - $decay;
				$vector->x += ($sideBlock->x - $this->x) * $realDecay;
				$vector->y += ($sideBlock->y - $this->y) * $realDecay;
				$vector->z += ($sideBlock->z - $this->z) * $realDecay;
			}
		}

		if($this->getDamage() >= 8){
			if(
				!$this->canFlowInto($this->level->getBlockAt($this->x, $this->y, $this->z - 1)) or
				!$this->canFlowInto($this->level->getBlockAt($this->x, $this->y, $this->z + 1)) or
				!$this->canFlowInto($this->level->getBlockAt($this->x - 1, $this->y, $this->z)) or
				!$this->canFlowInto($this->level->getBlockAt($this->x + 1, $this->y, $this->z)) or
				!$this->canFlowInto($this->level->getBlockAt($this->x, $this->y + 1, $this->z - 1)) or
				!$this->canFlowInto($this->level->getBlockAt($this->x, $this->y + 1, $this->z + 1)) or
				!$this->canFlowInto($this->level->getBlockAt($this->x - 1, $this->y + 1, $this->z)) or
				!$this->canFlowInto($this->level->getBlockAt($this->x + 1, $this->y + 1, $this->z))
			){
				$vector = $vector->normalize()->add(0, -6, 0);
			}
		}

		return $this->flowVector = $vector->normalize();
	}

	public function addVelocityToEntity(Entity $entity, Vector3 $vector) : void{
		if($entity->canBeMovedByCurrents()){
			$flow = $this->getFlowVector();
			$vector->x += $flow->x;
			$vector->y += $flow->y;
			$vector->z += $flow->z;
		}
	}

	abstract public function tickRate() : int;

	/**
	 * Returns how many liquid levels are lost per block flowed horizontally. Affects how far the liquid can flow.
	 */
	public function getFlowDecayPerBlock() : int{
		return 1;
	}

	public function onNearbyBlockChange() : void{
		$this->checkForHarden();
		$this->level->scheduleDelayedBlockUpdate($this, $this->tickRate());
	}

	public function onScheduledUpdate() : void{
		$decay = $this->getFlowDecay($this);
		$multiplier = $this->getFlowDecayPerBlock();

		if($decay > 0){
			$smallestFlowDecay = -100;
			$this->adjacentSources = 0;
			$smallestFlowDecay = $this->getSmallestFlowDecay($this->level->getBlockAt($this->x, $this->y, $this->z - 1), $smallestFlowDecay);
			$smallestFlowDecay = $this->getSmallestFlowDecay($this->level->getBlockAt($this->x, $this->y, $this->z + 1), $smallestFlowDecay);
			$smallestFlowDecay = $this->getSmallestFlowDecay($this->level->getBlockAt($this->x - 1, $this->y, $this->z), $smallestFlowDecay);
			$smallestFlowDecay = $this->getSmallestFlowDecay($this->level->getBlockAt($this->x + 1, $this->y, $this->z), $smallestFlowDecay);

			$newDecay = $smallestFlowDecay + $multiplier;

			if($newDecay >= 8 or $smallestFlowDecay < 0){
				$newDecay = -1;
			}

			if(($topFlowDecay = $this->getFlowDecay($this->level->getBlockAt($this->x, $this->y + 1, $this->z))) >= 0){
				$newDecay = $topFlowDecay | 0x08;
			}

			if($this->adjacentSources >= 2 and $this instanceof Water){
				$bottomBlock = $this->level->getBlockAt($this->x, $this->y - 1, $this->z);
				if($bottomBlock->isSolid()){
					$newDecay = 0;
				}elseif($bottomBlock instanceof Water and $bottomBlock->getDamage() === 0){
					$newDecay = 0;
				}
			}

			if($newDecay !== $decay){
				$decay = $newDecay;
				if($decay < 0){
					$this->level->setBlock($this, BlockFactory::get(Block::AIR), true, true);
				}else{
					$this->level->setBlock($this, BlockFactory::get($this->id, $decay), true, true);
					$this->level->scheduleDelayedBlockUpdate($this, $this->tickRate());
				}
			}
		}

		if($decay >= 0){
			$bottomBlock = $this->level->getBlockAt($this->x, $this->y - 1, $this->z);

			$this->flowIntoBlock($bottomBlock, $decay | 0x08);

			if($decay === 0 or !$bottomBlock->canBeFlowedInto()){
				if($decay >= 8){
					$adjacentDecay = 1;
				}else{
					$adjacentDecay = $decay + $multiplier;
				}

				if($adjacentDecay < 8){
					$flags = $this->getOptimalFlowDirections();

					if($flags[0]){
						$this->flowIntoBlock($this->level->getBlockAt($this->x - 1, $this->y, $this->z), $adjacentDecay);
					}

					if($flags[1]){
						$this->flowIntoBlock($this->level->getBlockAt($this->x + 1, $this->y, $this->z), $adjacentDecay);
					}

					if($flags[2]){
						$this->flowIntoBlock($this->level->getBlockAt($this->x, $this->y, $this->z - 1), $adjacentDecay);
					}

					if($flags[3]){
						$this->flowIntoBlock($this->level->getBlockAt($this->x, $this->y, $this->z + 1), $adjacentDecay);
					}
				}
			}

			$this->checkForHarden();
		}
	}

	protected function flowIntoBlock(Block $block, int $newFlowDecay) : void{
		if($this->canFlowInto($block) and !($block instanceof Liquid)){
			$ev = new BlockSpreadEvent($block, $this, BlockFactory::get($this->getId(), $newFlowDecay));
			$ev->call();
			if(!$ev->isCancelled()){
				if($block->getId() > 0){
					$this->level->useBreakOn($block);
				}

				$this->level->setBlock($block, $ev->getNewState(), true, true);
				$this->level->scheduleDelayedBlockUpdate($block, $this->tickRate());
			}
		}
	}

	private function calculateFlowCost(int $blockX, int $blockY, int $blockZ, int $accumulatedCost, int $maxCost, int $originOpposite, int $lastOpposite) : int{
		$cost = 1000;

		for($j = 0; $j < 4; ++$j){
			if($j === $originOpposite or $j === $lastOpposite){
				continue;
			}

			$x = $blockX;
			$y = $blockY;
			$z = $blockZ;

			if($j === 0){
				--$x;
			}elseif($j === 1){
				++$x;
			}elseif($j === 2){
				--$z;
			}elseif($j === 3){
				++$z;
			}

			if(!isset($this->flowCostVisited[$hash = Level::blockHash($x, $y, $z)])){
				$blockSide = $this->level->getBlockAt($x, $y, $z);
				if(!$this->canFlowInto($blockSide)){
					$this->flowCostVisited[$hash] = self::BLOCKED;
				}elseif($this->level->getBlockAt($x, $y - 1, $z)->canBeFlowedInto()){
					$this->flowCostVisited[$hash] = self::CAN_FLOW_DOWN;
				}else{
					$this->flowCostVisited[$hash] = self::CAN_FLOW;
				}
			}

			$status = $this->flowCostVisited[$hash];

			if($status === self::BLOCKED){
				continue;
			}elseif($status === self::CAN_FLOW_DOWN){
				return $accumulatedCost;
			}

			if($accumulatedCost >= $maxCost){
				continue;
			}

			$realCost = $this->calculateFlowCost($x, $y, $z, $accumulatedCost + 1, $maxCost, $originOpposite, $j ^ 0x01);

			if($realCost < $cost){
				$cost = $realCost;
			}
		}

		return $cost;
	}

	/**
	 * @return bool[]
	 */
	private function getOptimalFlowDirections() : array{
		$flowCost = array_fill(0, 4, 1000);
		$maxCost = intdiv(4, $this->getFlowDecayPerBlock());
		for($j = 0; $j < 4; ++$j){
			$x = $this->x;
			$y = $this->y;
			$z = $this->z;

			if($j === 0){
				--$x;
			}elseif($j === 1){
				++$x;
			}elseif($j === 2){
				--$z;
			}elseif($j === 3){
				++$z;
			}
			$block = $this->level->getBlockAt($x, $y, $z);

			if(!$this->canFlowInto($block)){
				$this->flowCostVisited[Level::blockHash($x, $y, $z)] = self::BLOCKED;
				continue;
			}elseif($this->level->getBlockAt($x, $y - 1, $z)->canBeFlowedInto()){
				$this->flowCostVisited[Level::blockHash($x, $y, $z)] = self::CAN_FLOW_DOWN;
				$flowCost[$j] = $maxCost = 0;
			}elseif($maxCost > 0){
				$this->flowCostVisited[Level::blockHash($x, $y, $z)] = self::CAN_FLOW;
				$flowCost[$j] = $this->calculateFlowCost($x, $y, $z, 1, $maxCost, $j ^ 0x01, $j ^ 0x01);
				$maxCost = min($maxCost, $flowCost[$j]);
			}
		}

		$this->flowCostVisited = [];

		$minCost = min($flowCost);

		$isOptimalFlowDirection = [];

		for($i = 0; $i < 4; ++$i){
			$isOptimalFlowDirection[$i] = ($flowCost[$i] === $minCost);
		}

		return $isOptimalFlowDirection;
	}

	private function getSmallestFlowDecay(Block $block, int $decay) : int{
		$blockDecay = $this->getFlowDecay($block);

		if($blockDecay < 0){
			return $decay;
		}elseif($blockDecay === 0){
			++$this->adjacentSources;
		}elseif($blockDecay >= 8){
			$blockDecay = 0;
		}

		return ($decay >= 0 && $blockDecay >= $decay) ? $decay : $blockDecay;
	}

	/**
	 * @return void
	 */
	protected function checkForHarden(){

	}

	protected function liquidCollide(Block $cause, Block $result) : bool{
		$ev = new BlockFormEvent($this, $result);
		$ev->call();
		if(!$ev->isCancelled()){
			$this->level->setBlock($this, $ev->getNewState(), true, true);
			$this->level->addSound(new FizzSound($this->add(0.5, 0.5, 0.5), 2.6 + (lcg_value() - lcg_value()) * 0.8));
		}
		return true;
	}

	protected function canFlowInto(Block $block) : bool{
		return $block->canBeFlowedInto() and !($block instanceof Liquid and $block->meta === 0); //TODO: I think this should only be liquids of the same type
	}
}
