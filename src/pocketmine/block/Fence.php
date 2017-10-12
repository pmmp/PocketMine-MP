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

use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;

abstract class Fence extends Transparent{

	public function __construct(int $meta = 0){
		$this->meta = $meta;
	}

	public function getThickness() : float{
		return 0.25;
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB{
		$width = 0.5 - $this->getThickness() / 2;

		return new AxisAlignedBB(
			$this->x + ($this->canConnect($this->getSide(Vector3::SIDE_WEST)) ? 0 : $width),
			$this->y,
			$this->z + ($this->canConnect($this->getSide(Vector3::SIDE_NORTH)) ? 0 : $width),
			$this->x + 1 - ($this->canConnect($this->getSide(Vector3::SIDE_EAST)) ? 0 : $width),
			$this->y + 1.5,
			$this->z + 1 - ($this->canConnect($this->getSide(Vector3::SIDE_SOUTH)) ? 0 : $width)
		);
	}

	protected function recalculateCollisionBoxes() : array{
		$inset = 0.5 - $this->getThickness() / 2;

		$bbs = [
			new AxisAlignedBB( //centre post AABB
				$this->x + $inset,
				$this->y,
				$this->z + $inset,
				$this->x + 1 - $inset,
				$this->y + 1.5,
				$this->z + 1 - $inset
			)
		];

		if($this->canConnect($this->getSide(Vector3::SIDE_WEST))){
			//western side connected part from start X to post (negative X)
			//
			// -#
			//
			$bbs[] = new AxisAlignedBB(
				$this->x,
				$this->y,
				$this->z + $inset,
				$this->x + $inset, //don't overlap with centre post
				$this->y + 1.5,
				$this->z + 1 - $inset
			);
		}
		if($this->canConnect($this->getSide(Vector3::SIDE_EAST))){
			//eastern side connected part from post to end X (positive X)
			//
			// #-
			//
			$bbs[] = new AxisAlignedBB(
				$this->x + 1 - $inset,
				$this->y,
				$this->z + $inset,
				$this->x + 1,
				$this->y + 1.5,
				$this->z + 1 - $inset
			);
		}
		if($this->canConnect($this->getSide(Vector3::SIDE_NORTH))){
			//northern side connected part from start Z to post (negative Z)
			//  |
			//  #
			//
			$bbs[] = new AxisAlignedBB(
				$this->x + $inset,
				$this->y,
				$this->z,
				$this->x + 1 - $inset,
				$this->y + 1.5,
				$this->z + $inset
			);
		}
		if($this->canConnect($this->getSide(Vector3::SIDE_SOUTH))){
			//southern side connected part from post to end Z (positive Z)
			//
			//  #
			//  |
			$bbs[] = new AxisAlignedBB(
				$this->x + $inset,
				$this->y,
				$this->z + 1 - $inset,
				$this->x + 1 - $inset,
				$this->y + 1.5,
				$this->z + 1
			);
		}

		return $bbs;
	}

	public function canConnect(Block $block){
		return $block instanceof static or $block instanceof FenceGate or ($block->isSolid() and !$block->isTransparent());
	}

}
