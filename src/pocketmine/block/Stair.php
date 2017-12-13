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
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\Player;

abstract class Stair extends Transparent{

	protected function recalculateCollisionBoxes() : array{
		//TODO: handle corners

		$minYSlab = ($this->meta & 0x04) === 0 ? 0 : 0.5;
		$maxYSlab = $minYSlab + 0.5;

		$bbs = [
			new AxisAlignedBB(
				$this->x,
				$this->y + $minYSlab,
				$this->z,
				$this->x + 1,
				$this->y + $maxYSlab,
				$this->z + 1
			)
		];

		$minY = ($this->meta & 0x04) === 0 ? 0.5 : 0;
		$maxY = $minY + 0.5;

		$rotationMeta = $this->meta & 0x03;

		$minX = $minZ = 0;
		$maxX = $maxZ = 1;

		switch($rotationMeta){
			case 0:
				$minX = 0.5;
				break;
			case 1:
				$maxX = 0.5;
				break;
			case 2:
				$minZ = 0.5;
				break;
			case 3:
				$maxZ = 0.5;
				break;
		}

		$bbs[] = new AxisAlignedBB(
			$this->x + $minX,
			$this->y + $minY,
			$this->z + $minZ,
			$this->x + $maxX,
			$this->y + $maxY,
			$this->z + $maxZ
		);

		return $bbs;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
		$faces = [
			0 => 0,
			1 => 2,
			2 => 1,
			3 => 3
		];
		$this->meta = $faces[$player->getDirection()] & 0x03;
		if(($clickVector->y > 0.5 and $face !== Vector3::SIDE_UP) or $face === Vector3::SIDE_DOWN){
			$this->meta |= 0x04; //Upside-down stairs
		}
		$this->getLevel()->setBlock($blockReplace, $this, true, true);

		return true;
	}

	public function getVariantBitmask() : int{
		return 0;
	}
}
