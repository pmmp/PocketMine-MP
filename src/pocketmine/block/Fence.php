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
use pocketmine\math\Facing;

abstract class Fence extends Transparent{
	/** @var bool[] facing => dummy */
	protected $connections = [];

	public function getThickness() : float{
		return 0.25;
	}

	public function readStateFromWorld() : void{
		parent::readStateFromWorld();

		foreach(Facing::HORIZONTAL as $facing){
			$block = $this->getSide($facing);
			if($block instanceof static or $block instanceof FenceGate or ($block->isSolid() and !$block->isTransparent())){
				$this->connections[$facing] = true;
			}else{
				unset($this->connections[$facing]);
			}
		}
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB{
		$width = 0.5 - $this->getThickness() / 2;

		return new AxisAlignedBB(
			(isset($this->connections[Facing::WEST]) ? 0 : $width),
			0,
			(isset($this->connections[Facing::NORTH]) ? 0 : $width),
			1 - (isset($this->connections[Facing::EAST]) ? 0 : $width),
			1.5,
			1 - (isset($this->connections[Facing::WEST]) ? 0 : $width)
		);
	}

	protected function recalculateCollisionBoxes() : array{
		$inset = 0.5 - $this->getThickness() / 2;

		/** @var AxisAlignedBB[] $bbs */
		$bbs = [];

		$connectWest = isset($this->connections[Facing::WEST]);
		$connectEast = isset($this->connections[Facing::EAST]);

		if($connectWest or $connectEast){
			//X axis (west/east)
			$bbs[] = new AxisAlignedBB(
				($connectWest ? 0 : $inset),
				0,
				$inset,
				1 - ($connectEast ? 0 : $inset),
				1.5,
				1 - $inset
			);
		}

		$connectNorth = isset($this->connections[Facing::NORTH]);
		$connectSouth = isset($this->connections[Facing::SOUTH]);

		if($connectNorth or $connectSouth){
			//Z axis (north/south)
			$bbs[] = new AxisAlignedBB(
				$inset,
				0,
				($connectNorth ? 0 : $inset),
				1 - $inset,
				1.5,
				1 - ($connectSouth ? 0 : $inset)
			);
		}

		if(empty($bbs)){
			//centre post AABB (only needed if not connected on any axis - other BBs overlapping will do this if any connections are made)
			return [
				new AxisAlignedBB(
					$inset,
					0,
					$inset,
					1 - $inset,
					1.5,
					1 - $inset
				)
			];
		}

		return $bbs;
	}
}
