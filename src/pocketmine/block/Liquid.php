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

namespace pocketmine\block;


use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\math\Vector3;

abstract class Liquid extends Transparent{

	/** @var Vector3 */
	private $temporalVector = null;

	public function hasEntityCollision(){
		return true;
	}

	public function isBreakable(Item $item){
		return false;
	}

	public function canBeReplaced(){
		return true;
	}

	public function isSolid(){
		return false;
	}

	public $adjacentSources = 0;
	public $isOptimalFlowDirection = [0, 0, 0, 0];
	public $flowCost = [0, 0, 0, 0];

	public function getFluidHeightPercent(){
		$d = $this->meta;
		if($d >= 8){
			$d = 0;
		}

		return ($d + 1) / 9;
	}

	protected function getFlowDecay(Vector3 $pos){
		if(!($pos instanceof Block)){
			$pos = $this->getLevel()->getBlock($pos);
		}

		if($pos->getId() !== $this->getId()){
			return -1;
		}else{
			return $pos->getDamage();
		}
	}

	protected function getEffectiveFlowDecay(Vector3 $pos){
		if(!($pos instanceof Block)){
			$pos = $this->getLevel()->getBlock($pos);
		}

		if($pos->getId() !== $this->getId()){
			return -1;
		}

		$decay = $pos->getDamage();

		if($decay >= 8){
			$decay = 0;
		}

		return $decay;
	}

	public function getFlowVector(){
		$vector = new Vector3(0, 0, 0);

		if($this->temporalVector === null){
			$this->temporalVector = new Vector3(0, 0, 0);
		}

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
			$sideBlock = $this->getLevel()->getBlock($this->temporalVector->setComponents($x, $y, $z));
			$blockDecay = $this->getEffectiveFlowDecay($sideBlock);

			if($blockDecay < 0){
				if(!$sideBlock->canBeFlowedInto()){
					continue;
				}

				$blockDecay = $this->getEffectiveFlowDecay($this->getLevel()->getBlock($this->temporalVector->setComponents($x, $y - 1, $z)));

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
			$falling = false;

			if(!$this->getLevel()->getBlock($this->temporalVector->setComponents($this->x, $this->y, $this->z - 1))->canBeFlowedInto()){
				$falling = true;
			}elseif(!$this->getLevel()->getBlock($this->temporalVector->setComponents($this->x, $this->y, $this->z + 1))->canBeFlowedInto()){
				$falling = true;
			}elseif(!$this->getLevel()->getBlock($this->temporalVector->setComponents($this->x - 1, $this->y, $this->z))->canBeFlowedInto()){
				$falling = true;
			}elseif(!$this->getLevel()->getBlock($this->temporalVector->setComponents($this->x + 1, $this->y, $this->z))->canBeFlowedInto()){
				$falling = true;
			}elseif(!$this->getLevel()->getBlock($this->temporalVector->setComponents($this->x, $this->y + 1, $this->z - 1))->canBeFlowedInto()){
				$falling = true;
			}elseif(!$this->getLevel()->getBlock($this->temporalVector->setComponents($this->x, $this->y + 1, $this->z + 1))->canBeFlowedInto()){
				$falling = true;
			}elseif(!$this->getLevel()->getBlock($this->temporalVector->setComponents($this->x - 1, $this->y + 1, $this->z))->canBeFlowedInto()){
				$falling = true;
			}elseif(!$this->getLevel()->getBlock($this->temporalVector->setComponents($this->x + 1, $this->y + 1, $this->z))->canBeFlowedInto()){
				$falling = true;
			}

			if($falling){
				$vector = $vector->normalize()->add(0, -6, 0);
			}
		}

		return $vector->normalize();
	}

	public function addVelocityToEntity(Entity $entity, Vector3 $vector){
		$flow = $this->getFlowVector();
		$vector->x += $flow->x;
		$vector->y += $flow->y;
		$vector->z += $flow->z;
	}

	public function tickRate(){
		if($this instanceof Water){
			return 5;
		}elseif($this instanceof Lava){
			return 30;
		}

		return 0;
	}

	public function onUpdate($type){
		if($type === Level::BLOCK_UPDATE_NORMAL){
			$this->checkForHarden();
			$this->getLevel()->scheduleUpdate($this, $this->tickRate());
		}elseif($type === Level::BLOCK_UPDATE_SCHEDULED){
			if($this->temporalVector === null){
				$this->temporalVector = new Vector3(0, 0, 0);
			}

			$decay = $this->getFlowDecay($this);
			$multiplier = $this instanceof Lava ? 2 : 1;

			$flag = true;

			if($decay > 0){
				$smallestFlowDecay = -100;
				$this->adjacentSources = 0;
				$smallestFlowDecay = $this->getSmallestFlowDecay($this->level->getBlock($this->temporalVector->setComponents($this->x, $this->y, $this->z - 1)), $smallestFlowDecay);
				$smallestFlowDecay = $this->getSmallestFlowDecay($this->level->getBlock($this->temporalVector->setComponents($this->x, $this->y, $this->z + 1)), $smallestFlowDecay);
				$smallestFlowDecay = $this->getSmallestFlowDecay($this->level->getBlock($this->temporalVector->setComponents($this->x - 1, $this->y, $this->z)), $smallestFlowDecay);
				$smallestFlowDecay = $this->getSmallestFlowDecay($this->level->getBlock($this->temporalVector->setComponents($this->x + 1, $this->y, $this->z)), $smallestFlowDecay);

				$k = $smallestFlowDecay + $multiplier;

				if($k >= 8 or $smallestFlowDecay < 0){
					$k = -1;
				}

				if(($topFlowDecay = $this->getFlowDecay($this->level->getBlock($this->level->getBlock($this->temporalVector->setComponents($this->x, $this->y + 1, $this->z))))) >= 0){
					if($topFlowDecay >= 8){
						$k = $topFlowDecay;
					}else{
						$k = $topFlowDecay | 0x08;
					}
				}

				if($this->adjacentSources >= 2 and $this instanceof Water){
					$bottomBlock = $this->level->getBlock($this->level->getBlock($this->temporalVector->setComponents($this->x, $this->y - 1, $this->z)));
					if($bottomBlock->isSolid()){
						$k = 0;
					}elseif($bottomBlock instanceof Water and $bottomBlock->getDamage() === 0){
						$k = 0;
					}
				}

				if($this instanceof Lava and $decay < 8 and $k < 8 and $k > 1 and mt_rand(0, 4) !== 0){
					$k = $decay;
					$flag = false;
				}

				if($k !== $decay){
					$decay = $k;
					if($decay < 0){
						$this->getLevel()->setBlock($this, new Air(), true);
					}else{
						$this->getLevel()->setBlock($this, Block::get($this->id, $decay), true);
						$this->getLevel()->scheduleUpdate($this, $this->tickRate());
					}
				}elseif($flag){
					//$this->getLevel()->scheduleUpdate($this, $this->tickRate());
					//$this->updateFlow();
				}
			}else{
				//$this->updateFlow();
			}

			$bottomBlock = $this->level->getBlock($this->temporalVector->setComponents($this->x, $this->y - 1, $this->z));

			if($bottomBlock->canBeFlowedInto() or $bottomBlock instanceof Liquid){
				if($this instanceof Lava and $bottomBlock instanceof Water){
					$this->getLevel()->setBlock($bottomBlock, Block::get(Item::STONE), true);
					return;
				}

				if($decay >= 8){
					$this->getLevel()->setBlock($bottomBlock, Block::get($this->id, $decay), true);
					$this->getLevel()->scheduleUpdate($bottomBlock, $this->tickRate());
				}else{
					$this->getLevel()->setBlock($bottomBlock, Block::get($this->id, $decay + 8), true);
					$this->getLevel()->scheduleUpdate($bottomBlock, $this->tickRate());
				}
			}elseif($decay >= 0 and ($decay === 0 or !$bottomBlock->canBeFlowedInto())){
				$flags = $this->getOptimalFlowDirections();

				$l = $decay + $multiplier;

				if($decay >= 8){
					$l = 1;
				}

				if($l >= 8){
					$this->checkForHarden();
					return;
				}

				if($flags[0]){
					$this->flowIntoBlock($this->level->getBlock($this->temporalVector->setComponents($this->x - 1, $this->y, $this->z)), $l);
				}

				if($flags[1]){
					$this->flowIntoBlock($this->level->getBlock($this->temporalVector->setComponents($this->x + 1, $this->y, $this->z)), $l);
				}

				if($flags[2]){
					$this->flowIntoBlock($this->level->getBlock($this->temporalVector->setComponents($this->x, $this->y, $this->z - 1)), $l);
				}

				if($flags[3]){
					$this->flowIntoBlock($this->level->getBlock($this->temporalVector->setComponents($this->x, $this->y, $this->z + 1)), $l);
				}
			}

			$this->checkForHarden();

		}
	}

	private function flowIntoBlock(Block $block, $newFlowDecay){
		if($block->canBeFlowedInto()){
			if($block->getId() > 0){
				$this->getLevel()->useBreakOn($block);
			}

			$this->getLevel()->setBlock($block, Block::get($this->getId(), $newFlowDecay), true);
			$this->getLevel()->scheduleUpdate($block, $this->tickRate());
		}
	}

	private function calculateFlowCost(Block $block, $accumulatedCost, $previousDirection){
		$cost = 1000;

		for($j = 0; $j < 4; ++$j){
			if(
				($j === 0 and $previousDirection === 1) or
				($j === 1 and $previousDirection === 0) or
				($j === 2 and $previousDirection === 3) or
				($j === 3 and $previousDirection === 2)
			){
				$x = $block->x;
				$y = $block->y;
				$z = $block->z;

				if($j === 0){
					--$x;
				}elseif($j === 1){
					++$x;
				}elseif($j === 2){
					--$z;
				}elseif($j === 3){
					++$z;
				}
				$blockSide = $this->getLevel()->getBlock($this->temporalVector->setComponents($x, $y, $z));

				if(!$blockSide->canBeFlowedInto() and !($blockSide instanceof Liquid)){
					continue;
				}elseif($blockSide instanceof Liquid and $blockSide->getDamage() === 0){
					continue;
				}elseif($this->getLevel()->getBlock($this->temporalVector->setComponents($x, $y - 1, $z))->canBeFlowedInto()){
					return $accumulatedCost;
				}

				if($accumulatedCost >= 4){
					continue;
				}

				$realCost = $this->calculateFlowCost($blockSide, $accumulatedCost + 1, $j);

				if($realCost < $cost){
					$cost = $realCost;
				}
			}
		}

		return $cost;
	}

	public function getHardness(){
		return 100;
	}

	private function getOptimalFlowDirections(){
		if($this->temporalVector === null){
			$this->temporalVector = new Vector3(0, 0, 0);
		}

		for($j = 0; $j < 4; ++$j){
			$this->flowCost[$j] = 1000;

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
			$block = $this->getLevel()->getBlock($this->temporalVector->setComponents($x, $y, $z));

			if(!$block->canBeFlowedInto() and !($block instanceof Liquid)){
				continue;
			}elseif($block instanceof Liquid and $block->getDamage() === 0){
				continue;
			}elseif($this->getLevel()->getBlock($this->temporalVector->setComponents($x, $y - 1, $z))->canBeFlowedInto()){
				$this->flowCost[$j] = 0;
			}else{
				$this->flowCost[$j] = $this->calculateFlowCost($block, 1, $j);
			}
		}

		$minCost = $this->flowCost[0];

		for($i = 1; $i < 4; ++$i){
			if($this->flowCost[$i] < $minCost){
				$minCost = $this->flowCost[$i];
			}
		}

		for($i = 0; $i < 4; ++$i){
			$this->isOptimalFlowDirection[$i] = ($this->flowCost[$i] === $minCost);
		}

		return $this->isOptimalFlowDirection;
	}

	private function getSmallestFlowDecay(Vector3 $pos, $decay){
		$blockDecay = $this->getFlowDecay($pos);

		if($blockDecay < 0){
			return $decay;
		}elseif($blockDecay === 0){
			++$this->adjacentSources;
		}elseif($blockDecay >= 8){
			$blockDecay = 0;
		}

		return ($decay >= 0 && $blockDecay >= $decay) ? $decay : $blockDecay;
	}

	private function checkForHarden(){
		if($this instanceof Lava){
			$colliding = false;
			for($side = 0; $side <= 5 and !$colliding; ++$side){
				$colliding = $this->getSide($side) instanceof Water;
			}

			if($colliding){
				if($this->getDamage() === 0){
					$this->getLevel()->setBlock($this, Block::get(Item::OBSIDIAN), true);
				}elseif($this->getDamage() <= 4){
					$this->getLevel()->setBlock($this, Block::get(Item::COBBLESTONE), true);
				}
			}
		}
	}

	public function getBoundingBox(){
		return null;
	}

	public function getDrops(Item $item){
		return [];
	}
}