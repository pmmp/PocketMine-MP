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

namespace PocketMine\Level\Generator\Populator;

use PocketMine;
use PocketMine\Math\Vector3 as Vector3;
use PocketMine\Block\TallGrass as BlockTallGrass;

class TallGrass extends Populator{
	private $level;
	private $randomAmount;
	private $baseAmount;

	public function setRandomAmount($amount){
		$this->randomAmount = $amount;
	}

	public function setBaseAmount($amount){
		$this->baseAmount = $amount;
	}

	public function populate(Level $level, $chunkX, $chunkZ, Random $random){
		$this->level = $level;
		$amount = $random->nextRange(0, $this->randomAmount + 1) + $this->baseAmount;
		for($i = 0; $i < $amount; ++$i){
			$x = $random->nextRange($chunkX << 4, ($chunkX << 4) + 15);
			$z = $random->nextRange($chunkZ << 4, ($chunkZ << 4) + 15);
			for($size = 30; $size > 0; --$size){
				$xx = $x - 7 + $random->nextRange(0, 15);
				$zz = $z - 7 + $random->nextRange(0, 15);
				$yy = $this->getHighestWorkableBlock($xx, $zz);
				$vector = new Vector3($xx, $yy, $zz);
				if($yy !== -1 and $this->canTallGrassStay($this->level->getBlockRaw($vector))){
					$this->level->setBlockRaw($vector, new BlockTallGrass(1));
				}
			}
		}
	}

	private function canTallGrassStay(Block $block){
		return $block->getID() === AIR and $block->getSide(0)->getID() === GRASS;
	}

	private function getHighestWorkableBlock($x, $z){
		for($y = 128; $y > 0; --$y){
			$b = $this->level->getBlockRaw(new Vector3($x, $y, $z));
			if($b->getID() === AIR or $b->getID() === LEAVES){
				if(--$y <= 0){
					return -1;
				}
			} else{
				break;
			}
		}

		return ++$y;
	}
}