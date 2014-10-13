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


use pocketmine\math\AxisAlignedBB;

class StoneWall extends Transparent{
	public function __construct($meta = 0){
		$meta &= 0x01;
		parent::__construct(self::STONE_WALL, $meta, "Cobblestone Wall");
		if($meta === 1){
			$this->name = "Mossy Cobblestone Wall";
		}
		$this->isFullBlock = false;
		$this->isSolid = false;
		$this->hardness = 30;
	}

	public function getBoundingBox(){
		if($this->boundingBox !== null){
			return $this->boundingBox;
		}

		$flag = $this->canConnect($this->getSide(2));
		$flag1 = $this->canConnect($this->getSide(3));
		$flag2 = $this->canConnect($this->getSide(4));
		$flag3 = $this->canConnect($this->getSide(5));

		$f =  $flag2 ? 0 : 0.25;
		$f1 = $flag3 ? 1 : 0.75;
		$f2 = $flag ? 0 : 0.25;
		$f3 = $flag1 ? 1 : 0.75;
		$f4 = 1;

		if($flag and $flag1 and !$flag2 and !$flag3){
			$f4 = 0.8125;
			$f = 0.3125;
			$f1 = 0.6875;
		}elseif(!$flag and !$flag1 and $flag2 and $flag3){
			$f4 = 0.8125;
			$f2 = 0.3125;
			$f3 = 0.6875;
		}

		return $this->boundingBox = new AxisAlignedBB(
			$this->x + $f,
			$this->y,
			$this->z + $f2,
			$this->x + $f1,
			$this->y + $f4,
			$this->z + $f3
		);
	}

	public function canConnect(Block $block){
		return ($block->getID() !== self::COBBLE_WALL and $block->getID() !== self::FENCE_GATE) ? $block->isSolid : true;
	}

}