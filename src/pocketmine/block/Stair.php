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
use pocketmine\math\Bearing;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\Player;

abstract class Stair extends Transparent{
	/** @var int */
	protected $facing = Facing::NORTH;
	/** @var bool */
	protected $upsideDown = false;

	protected function writeStateToMeta() : int{
		return (5 - $this->facing) | ($this->upsideDown ? 0x04 : 0);
	}

	public function readStateFromMeta(int $meta) : void{
		$this->facing = 5 - ($meta & 0x03);
		$this->upsideDown = ($meta & 0x04) !== 0;
	}

	protected function recalculateCollisionBoxes() : array{
		//TODO: handle corners

		$minYSlab = $this->upsideDown ? 0 : 0.5;
		$maxYSlab = $minYSlab + 0.5;

		$bbs = [
			new AxisAlignedBB(0, $minYSlab, 0, 1, $maxYSlab, 1)
		];

		$minY = $this->upsideDown ? 0.5 : 0;
		$maxY = $minY + 0.5;

		$rotationMeta = $this->facing;

		$minX = $minZ = 0;
		$maxX = $maxZ = 1;

		switch($rotationMeta){
			case Facing::EAST:
				$minX = 0.5;
				break;
			case Facing::WEST:
				$maxX = 0.5;
				break;
			case Facing::SOUTH:
				$minZ = 0.5;
				break;
			case Facing::NORTH:
				$maxZ = 0.5;
				break;
		}

		$bbs[] = new AxisAlignedBB($minX, $minY, $minZ, $maxX, $maxY, $maxZ);

		return $bbs;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
		if($player !== null){
			$this->facing = Bearing::toFacing($player->getDirection());
		}
		$this->upsideDown = (($clickVector->y > 0.5 and $face !== Facing::UP) or $face === Facing::DOWN);

		return parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}
}
