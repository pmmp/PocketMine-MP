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
use function count;

class Thin extends Transparent{
	/** @var bool[] facing => dummy */
	protected $connections = [];

	public function readStateFromWorld() : void{
		parent::readStateFromWorld();

		foreach(Facing::HORIZONTAL as $facing){
			$side = $this->getSide($facing);
			if($side instanceof Thin or $side->isFullCube()){
				$this->connections[$facing] = true;
			}else{
				unset($this->connections[$facing]);
			}
		}
	}

	protected function recalculateCollisionBoxes() : array{
		$inset = 7 / 16;

		/** @var AxisAlignedBB[] $bbs */
		$bbs = [];

		if(isset($this->connections[Facing::WEST]) or isset($this->connections[Facing::EAST])){
			$bb = AxisAlignedBB::one()->squash(Facing::AXIS_Z, $inset);

			if(!isset($this->connections[Facing::WEST])){
				$bb->trim(Facing::WEST, $inset);
			}elseif(!isset($this->connections[Facing::EAST])){
				$bb->trim(Facing::EAST, $inset);
			}
			$bbs[] = $bb;
		}

		if(isset($this->connections[Facing::NORTH]) or isset($this->connections[Facing::SOUTH])){
			$bb = AxisAlignedBB::one()->squash(Facing::AXIS_X, $inset);

			if(!isset($this->connections[Facing::NORTH])){
				$bb->trim(Facing::NORTH, $inset);
			}elseif(!isset($this->connections[Facing::SOUTH])){
				$bb->trim(Facing::SOUTH, $inset);
			}
			$bbs[] = $bb;
		}

		if(count($bbs) === 0){
			//centre post AABB (only needed if not connected on any axis - other BBs overlapping will do this if any connections are made)
			return [
				AxisAlignedBB::one()->contract($inset, 0, $inset)
			];
		}

		return $bbs;
	}
}
