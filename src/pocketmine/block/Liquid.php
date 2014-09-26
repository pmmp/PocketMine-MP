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

class Liquid extends Transparent{
	public $isLiquid = true;
	public $breakable = false;
	public $isReplaceable = true;
	public $isSolid = false;
	public $isFullBlock = true;

	public $adjacentSources = 0;
	public $isOptimalFlowDirection = [0, 0, 0];
	public $flowCost = [0, 0, 0];

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

		if($pos->getID() !== $this->getID()){
			return -1;
		}else{
			return $pos->getDamage();
		}
	}

	protected function getEffectiveFlowDecay(Vector3 $pos){
		if(!($pos instanceof Block)){
			$pos = $this->getLevel()->getBlock($pos);
		}

		if($pos->getID() !== $this->getID()){
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

		$decay = $this->getEffectiveFlowDecay($this);

		for($side = 2; $side <= 5; ++$side){
			$sideBlock = $this->getSide($side);
			$blockDecay = $this->getEffectiveFlowDecay($sideBlock);

			if($blockDecay < 0){
				if(!$sideBlock->isFlowable){
					continue;
				}

				$blockDecay = $this->getEffectiveFlowDecay($sideBlock->getSide(0));

				if($blockDecay >= 0){
					$realDecay = $blockDecay - ($decay - 8);
					$vector = $vector->add(($sideBlock->x - $this->x) * $realDecay, ($sideBlock->y - $this->y) * $realDecay, ($sideBlock->z - $this->z) * $realDecay);
				}

				continue;
			}else{
				$realDecay = $blockDecay - $decay;
				$vector = $vector->add(($sideBlock->x - $this->x) * $realDecay, ($sideBlock->y - $this->y) * $realDecay, ($sideBlock->z - $this->z) * $realDecay);
			}
		}

		if($this->getDamage() >= 8){
			$falling = false;

			if(!$this->getLevel()->getBlock($this->add(0, 0, -1))->isFlowable){
				$falling = true;
			}elseif(!$this->getLevel()->getBlock($this->add(0, 0, 1))->isFlowable){
				$falling = true;
			}elseif(!$this->getLevel()->getBlock($this->add(-1, 0, 0))->isFlowable){
				$falling = true;
			}elseif(!$this->getLevel()->getBlock($this->add(1, 0, 0))->isFlowable){
				$falling = true;
			}elseif(!$this->getLevel()->getBlock($this->add(0, 1, -1))->isFlowable){
				$falling = true;
			}elseif(!$this->getLevel()->getBlock($this->add(0, 1, 1))->isFlowable){
				$falling = true;
			}elseif(!$this->getLevel()->getBlock($this->add(-1, 1, 0))->isFlowable){
				$falling = true;
			}elseif(!$this->getLevel()->getBlock($this->add(1, 1, 0))->isFlowable){
				$falling = true;
			}

			if($falling){
				$vector = $vector->normalize()->add(0, -6, 0);
			}
		}

		return $vector->normalize();
	}

	public function addVelocityToEntity(Entity $entity, Vector3 $vector){
		$vector->add($this->getFlowVector());
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
		if($type === Level::BLOCK_UPDATE_NORMAL or $type === Level::BLOCK_UPDATE_SCHEDULED){
			$decay = $this->getFlowDecay($this);

			//TODO: If lava and on hell, set this to two
			$multiplier = 1;

			$flag = true;

			if($decay > 0){
				$smallestFlowDecay = -100;
				$this->adjacentSources = 0;
				for($side = 2; $side <= 5; ++$side){
					$smallestFlowDecay = $this->getSmallestFlowDecay($this->getSide($side), $smallestFlowDecay);
				}

				$k = $smallestFlowDecay + $multiplier;

				if($k >= 8 or $smallestFlowDecay < 0){
					$k = -1;
				}

				if(($topFlowDecay = $this->getFlowDecay($this->getSide(1))) >= 0){
					if($topFlowDecay >= 8){
						$k = $topFlowDecay;
					}else{
						$k = $topFlowDecay | 0x08;
					}
				}

				if($this->adjacentSources >= 2 and $this instanceof Water){
					$bottomBlock = $this->getSide(0);
					if($bottomBlock->isSolid){
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
						$this->getLevel()->setBlock($this, Block::get(Item::AIR), true);
					}else{
						$this->getLevel()->setBlock($this, Block::get($this->id, $decay), true);
					}
				}elseif($flag){
					//$this->updateFlow();
				}
			}else{
				//$this->updateFlow();
			}

			$bottomBlock = $this->getSide(0);

			if($bottomBlock instanceof Liquid){
				if($this instanceof Lava and $bottomBlock instanceof Water){
					$this->getLevel()->setBlock($bottomBlock, Block::get(Item::STONE), true);
					return;
				}

				if($decay >= 8){
					$this->getLevel()->setBlock($bottomBlock, Block::get($this->id, $decay));
				}else{
					$this->getLevel()->setBlock($bottomBlock, Block::get($this->id, $decay | 0x80));
				}
			}elseif($decay >= 0 and ($decay === 0 or !$bottomBlock->isFlowable)){
				$flags = $this->getOptimalFlowDirections();

				$l = $decay + $multiplier;

				if($decay >= 8){
					$l = 1;
				}elseif($l >= 8){
					return;
				}

				if($flags[0]){
					$this->flowIntoBlock($this->getSide(2), $l);
				}

				if($flags[1]){
					$this->flowIntoBlock($this->getSide(3), $l);
				}

				if($flags[2]){
					$this->flowIntoBlock($this->getSide(4), $l);
				}

				if($flags[3]){
					$this->flowIntoBlock($this->getSide(5), $l);
				}
			}

		}
	}

	private function flowIntoBlock(Block $block, $newFlowDecay){
		if($block->isFlowable){
			if($block->getID() > 0){
				$this->getLevel()->useBreakOn($block);
			}

			$this->getLevel()->setBlock($this, Block::get($this->id, $newFlowDecay), true);
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
				$blockSide = $block->getSide($j + 2);

				if(!$blockSide->isFlowable or ($blockSide instanceof Liquid and $blockSide->getDamage() === 0)){
					continue;
				}elseif($blockSide->getSide(0)->isFlowable){
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

	private function getOptimalFlowDirections(){
		for($side = 2; $side <= 5; ++$side){
			$this->flowCost[$side - 2] = 1000;

			$block = $this->getSide($side);

			if(!$block->isFlowable or ($block instanceof Liquid and $block->getDamage() === 0)){
				continue;
			}elseif($block->getSide(0)->isFlowable){
				$this->flowCost[$side - 2] = 0;
			}else{
				$this->flowCost[$side - 2] = $this->calculateFlowCost($block, 1, $side - 2);
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
		}elseif($decay === 0){
			++$this->adjacentSources;
		}elseif($blockDecay >= 0){
			$blockDecay = 0;
		}

		return ($decay >= 0 && $blockDecay >= $decay) ? $decay : $blockDecay;
	}

	private function checkForHarden(){
		//TODO
	}
}