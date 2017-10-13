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

abstract class Thin extends Transparent{

	protected function recalculateBoundingBox() : ?AxisAlignedBB{

		$minX = 0.4375;
		$maxX = 0.5625;
		$minZ = 0.4375;
		$maxZ = 0.5625;

		$canConnectNorth = $this->canConnect($this->getSide(Vector3::SIDE_NORTH));
		$canConnectSouth = $this->canConnect($this->getSide(Vector3::SIDE_SOUTH));
		$canConnectWest = $this->canConnect($this->getSide(Vector3::SIDE_WEST));
		$canConnectEast = $this->canConnect($this->getSide(Vector3::SIDE_EAST));

		if((!$canConnectWest or !$canConnectEast) and ($canConnectWest or $canConnectEast or $canConnectNorth or $canConnectSouth)){
			if($canConnectWest and !$canConnectEast){
				$minX = 0;
			}elseif(!$canConnectWest and $canConnectEast){
				$maxX = 1;
			}
		}else{
			$minX = 0;
			$maxX = 1;
		}

		if((!$canConnectNorth or !$canConnectSouth) and ($canConnectWest or $canConnectEast or $canConnectNorth or $canConnectSouth)){
			if($canConnectNorth and !$canConnectSouth){
				$minZ = 0;
			}elseif(!$canConnectNorth and $canConnectSouth){
				$maxZ = 1;
			}
		}else{
			$minZ = 0;
			$maxZ = 1;
		}

		return new AxisAlignedBB(
			$this->x + $minX,
			$this->y,
			$this->z + $minZ,
			$this->x + $maxX,
			$this->y + 1,
			$this->z + $maxZ
		);
	}


	public function canConnect(Block $block){
		return $block->isSolid() or $block->getId() === $this->getId() or $block->getId() === self::GLASS_PANE or $block->getId() === self::GLASS;
	}

}