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

use pocketmine\block\utils\InvalidBlockStateException;
use pocketmine\block\utils\RailConnectionInfo;
use function array_keys;
use function implode;

/**
 * Simple non-curvable rail.
 */
class StraightOnlyRail extends BaseRail{

	private int $railShape = BlockLegacyMetadata::RAIL_STRAIGHT_NORTH_SOUTH;

	public function readStateFromData(int $id, int $stateMeta) : void{
		$railShape = $stateMeta & ~BlockLegacyMetadata::REDSTONE_RAIL_FLAG_POWERED;
		if(!isset(RailConnectionInfo::CONNECTIONS[$railShape])){
			throw new InvalidBlockStateException("No rail shape matches meta $stateMeta");
		}
		$this->railShape = $railShape;
	}

	protected function writeStateToMeta() : int{
		//TODO: railShape won't be plain metadata in the future
		return $this->railShape;
	}

	public function getStateBitmask() : int{
		return 0b111;
	}

	protected function setShapeFromConnections(array $connections) : void{
		$railShape = self::searchState($connections, RailConnectionInfo::CONNECTIONS);
		if($railShape === null){
			throw new \InvalidArgumentException("No rail shape matches these connections");
		}
		$this->railShape = $railShape;
	}

	protected function getCurrentShapeConnections() : array{
		return RailConnectionInfo::CONNECTIONS[$this->railShape];
	}

	public function getShape() : int{ return $this->railShape; }

	/** @return $this */
	public function setShape(int $shape) : self{
		if(!isset(RailConnectionInfo::CONNECTIONS[$shape])){
			throw new \InvalidArgumentException("Invalid rail shape, must be one of " . implode(", ", array_keys(RailConnectionInfo::CONNECTIONS)));
		}
		$this->railShape = $shape;
		return $this;

	}
}
