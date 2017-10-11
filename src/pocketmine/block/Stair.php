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

use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\Player;

abstract class Stair extends Transparent{

	/*
	public function collidesWithBB(AxisAlignedBB $bb, &$list = []){
		$damage = $this->getDamage();
		$j = $damage & 0x03;

		$f = 0;
		$f1 = 0.5;
		$f2 = 0.5;
		$f3 = 1;

		if(($damage & 0x04) > 0){
			$f = 0.5;
			$f1 = 1;
			$f2 = 0;
			$f3 = 0.5;
		}

		if($bb->intersectsWith($bb2 = AxisAlignedBB::getBoundingBoxFromPool(
			$this->x,
			$this->y + $f,
			$this->z,
			$this->x + 1,
			$this->y + $f1,
			$this->z + 1
		))){
			$list[] = $bb2;
		}

		if($j === 0){
			if($bb->intersectsWith($bb2 = AxisAlignedBB::getBoundingBoxFromPool(
				$this->x + 0.5,
				$this->y + $f2,
				$this->z,
				$this->x + 1,
				$this->y + $f3,
				$this->z + 1
			))){
				$list[] = $bb2;
			}
		}elseif($j === 1){
			if($bb->intersectsWith($bb2 = AxisAlignedBB::getBoundingBoxFromPool(
				$this->x,
				$this->y + $f2,
				$this->z,
				$this->x + 0.5,
				$this->y + $f3,
				$this->z + 1
			))){
				$list[] = $bb2;
			}
		}elseif($j === 2){
			if($bb->intersectsWith($bb2 = AxisAlignedBB::getBoundingBoxFromPool(
				$this->x,
				$this->y + $f2,
				$this->z + 0.5,
				$this->x + 1,
				$this->y + $f3,
				$this->z + 1
			))){
				$list[] = $bb2;
			}
		}elseif($j === 3){
			if($bb->intersectsWith($bb2 = AxisAlignedBB::getBoundingBoxFromPool(
				$this->x,
				$this->y + $f2,
				$this->z,
				$this->x + 1,
				$this->y + $f3,
				$this->z + 0.5
			))){
				$list[] = $bb2;
			}
		}
	}
	*/

	protected function recalculateBoundingBox() : ?AxisAlignedBB{

		if(($this->getDamage() & 0x04) > 0){
			return new AxisAlignedBB(
				$this->x,
				$this->y + 0.5,
				$this->z,
				$this->x + 1,
				$this->y + 1,
				$this->z + 1
			);
		}else{
			return new AxisAlignedBB(
				$this->x,
				$this->y,
				$this->z,
				$this->x + 1,
				$this->y + 0.5,
				$this->z + 1
			);
		}
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $facePos, Player $player = null) : bool{
		$faces = [
			0 => 0,
			1 => 2,
			2 => 1,
			3 => 3
		];
		$this->meta = $faces[$player->getDirection()] & 0x03;
		if(($facePos->y > 0.5 and $face !== Vector3::SIDE_UP) or $face === Vector3::SIDE_DOWN){
			$this->meta |= 0x04; //Upside-down stairs
		}
		$this->getLevel()->setBlock($blockReplace, $this, true, true);

		return true;
	}

	public function getVariantBitmask() : int{
		return 0;
	}

	public function getDrops(Item $item) : array{
		if($item->isPickaxe() >= Tool::TIER_WOODEN){
			return parent::getDrops($item);
		}

		return [];
	}
}
