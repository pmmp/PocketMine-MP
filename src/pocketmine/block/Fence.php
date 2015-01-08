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

class Fence extends Transparent{

	protected $id = self::FENCE;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function getHardness(){
		return 15;
	}


	public function getName(){
		static $names = [
			0 => "Oak Fence",
			1 => "Spruce Fence",
			2 => "Birch Fence",
			3 => "Jungle Fence",
			4 => "Acacia Fence",
			5 => "Jungle Fence",
			"",
			""
		];
		return $names[$this->meta & 0x07];
	}

	protected function recalculateBoundingBox(){

		$flag = $this->canConnect($this->getSide(2));
		$flag1 = $this->canConnect($this->getSide(3));
		$flag2 = $this->canConnect($this->getSide(4));
		$flag3 = $this->canConnect($this->getSide(5));

		$f = $flag2 ? 0 : 0.375;
		$f1 = $flag3 ? 1 : 0.625;
		$f2 = $flag ? 0 : 0.375;
		$f3 = $flag1 ? 1 : 0.625;

		return new AxisAlignedBB(
			$this->x + $f,
			$this->y,
			$this->z + $f2,
			$this->x + $f1,
			$this->y + 1.5,
			$this->z + $f3
		);
	}

	public function canConnect(Block $block){
		return (!($block instanceof Fence) and !($block instanceof FenceGate)) ? $block->isSolid() : true;
	}

}