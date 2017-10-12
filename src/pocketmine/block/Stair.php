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

	protected function recalculateCollisionBoxes() : array{
		//TODO: handle corners

		$bbs = [$this->recalculateBoundingBox()];

		$yMin = ($this->meta & 0x04) === 0 ? 0.5 : 0;
		$yMax = $yMin + 0.5;

		$rotationMeta = $this->meta & 0x03;

		$xMin = 0;
		$xMax = 1;

		$zMin = 0;
		$zMax = 1;

		switch($rotationMeta){
			case 0:
				$xMin = 0.5;
				break;
			case 1:
				$xMax = 0.5;
				break;
			case 2:
				$zMin = 0.5;
				break;
			case 3:
				$zMax = 0.5;
				break;
		}

		$bbs[] = new AxisAlignedBB(
			$this->x + $xMin,
			$this->y + $yMin,
			$this->z + $zMin,
			$this->x + $xMax,
			$this->y + $yMax,
			$this->z + $zMax
		);

		return $bbs;
	}

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
