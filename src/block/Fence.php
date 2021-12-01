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

use pocketmine\math\Axis;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use function count;

class Fence extends Transparent{
	/** @var bool[] facing => dummy */
	protected array $connections = [];

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

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array{
		$inset = 0.5 - $this->getThickness() / 2;

		/** @var AxisAlignedBB[] $bbs */
		$bbs = [];

		$connectWest = isset($this->connections[Facing::WEST]);
		$connectEast = isset($this->connections[Facing::EAST]);

		if($connectWest or $connectEast){
			//X axis (west/east)
			$bbs[] = AxisAlignedBB::one()
				->squash(Axis::Z, $inset)
				->extend(Facing::UP, 0.5)
				->trim(Facing::WEST, $connectWest ? 0 : $inset)
				->trim(Facing::EAST, $connectEast ? 0 : $inset);
		}

		$connectNorth = isset($this->connections[Facing::NORTH]);
		$connectSouth = isset($this->connections[Facing::SOUTH]);

		if($connectNorth or $connectSouth){
			//Z axis (north/south)
			$bbs[] = AxisAlignedBB::one()
				->squash(Axis::X, $inset)
				->extend(Facing::UP, 0.5)
				->trim(Facing::NORTH, $connectNorth ? 0 : $inset)
				->trim(Facing::SOUTH, $connectSouth ? 0 : $inset);
		}

		if(count($bbs) === 0){
			//centre post AABB (only needed if not connected on any axis - other BBs overlapping will do this if any connections are made)
			return [
				AxisAlignedBB::one()
					->extend(Facing::UP, 0.5)
					->contract($inset, 0, $inset)
			];
		}

		return $bbs;
	}
}
