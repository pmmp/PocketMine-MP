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

use pocketmine\block\utils\SupportType;
use pocketmine\math\Axis;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use function count;

/**
 * Thin blocks behave like glass panes. They connect to full-cube blocks horizontally adjacent to them if possible.
 */
class Thin extends Transparent{
	/** @var bool[] facing => dummy */
	protected array $connections = [];

	public function readStateFromWorld() : Block{
		parent::readStateFromWorld();

		$this->collisionBoxes = null;

		foreach(Facing::HORIZONTAL as $facing){
			$side = $this->getSide($facing);
			if($side instanceof Thin || $side instanceof Wall || $side->getSupportType(Facing::opposite($facing)) === SupportType::FULL){
				$this->connections[$facing->value] = true;
			}else{
				unset($this->connections[$facing->value]);
			}
		}

		return $this;
	}

	protected function recalculateCollisionBoxes() : array{
		$inset = 7 / 16;

		$bbs = [];

		if(isset($this->connections[Facing::WEST->value]) || isset($this->connections[Facing::EAST->value])){
			$bb = AxisAlignedBB::one()->squashedCopy(Axis::Z, $inset);

			if(!isset($this->connections[Facing::WEST->value])){
				$bb = $bb->trimmedCopy(Facing::WEST, $inset);
			}elseif(!isset($this->connections[Facing::EAST->value])){
				$bb = $bb->trimmedCopy(Facing::EAST, $inset);
			}
			$bbs[] = $bb;
		}

		if(isset($this->connections[Facing::NORTH->value]) || isset($this->connections[Facing::SOUTH->value])){
			$bb = AxisAlignedBB::one()->squashedCopy(Axis::X, $inset);

			if(!isset($this->connections[Facing::NORTH->value])){
				$bb = $bb->trimmedCopy(Facing::NORTH, $inset);
			}elseif(!isset($this->connections[Facing::SOUTH->value])){
				$bb = $bb->trimmedCopy(Facing::SOUTH, $inset);
			}
			$bbs[] = $bb;
		}

		if(count($bbs) === 0){
			//centre post AABB (only needed if not connected on any axis - other BBs overlapping will do this if any connections are made)
			return [
				AxisAlignedBB::one()->contractedCopy($inset, 0, $inset)
			];
		}

		return $bbs;
	}

	public function getSupportType(Facing $facing) : SupportType{
		return SupportType::NONE;
	}
}
