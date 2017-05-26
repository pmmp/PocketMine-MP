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


use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\Player;

class EndRod extends Flowable{

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		if($face === Vector3::SIDE_UP or $face === Vector3::SIDE_DOWN){
			$this->meta = $face;
		}else{
			$this->meta = $face ^ 0x01;
		}
		if($target instanceof EndRod and $target->getDamage() === $this->meta){
			$this->meta ^= 0x01;
		}

		return $this->level->setBlock($block, $this, true, true);
	}

	public function isSolid(){
		return true;
	}

	public function getLightLevel(){
		return 14;
	}

	protected function recalculateBoundingBox(){
		$m = $this->meta & ~0x01;
		$width = 0.375;

		switch($m){
			case 0x00: //up/down
				return new AxisAlignedBB(
					$this->x + $width,
					$this->y,
					$this->z + $width,
					$this->x + 1 - $width,
					$this->y + 1,
					$this->z + 1 - $width
				);
			case 0x02: //north/south
				return new AxisAlignedBB(
					$this->x,
					$this->y + $width,
					$this->z + $width,
					$this->x + 1,
					$this->y + 1 - $width,
					$this->z + 1 - $width
				);
			case 0x04: //east/west
				return new AxisAlignedBB(
					$this->x + $width,
					$this->y + $width,
					$this->z,
					$this->x + 1 - $width,
					$this->y + 1 - $width,
					$this->z + 1
				);
		}

		return null;
	}

	public function getDrops(Item $item){
		return [
			Item::get($this->getId(), 0, 1)
		];
	}

}