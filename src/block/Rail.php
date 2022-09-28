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
use pocketmine\math\Facing;
use function array_keys;
use function implode;

class Rail extends BaseRail{

	private int $railShape = BlockLegacyMetadata::RAIL_STRAIGHT_NORTH_SOUTH;

	public function readStateFromData(int $id, int $stateMeta) : void{
		if(!isset(RailConnectionInfo::CONNECTIONS[$stateMeta]) && !isset(RailConnectionInfo::CURVE_CONNECTIONS[$stateMeta])){
			throw new InvalidBlockStateException("No rail shape matches metadata $stateMeta");
		}
		$this->railShape = $stateMeta;
	}

	protected function writeStateToMeta() : int{
		//TODO: railShape won't be plain metadata in future
		return $this->railShape;
	}

	public function getStateBitmask() : int{
		return 0b1111;
	}

	protected function setShapeFromConnections(array $connections) : void{
		$railShape = self::searchState($connections, RailConnectionInfo::CONNECTIONS) ?? self::searchState($connections, RailConnectionInfo::CURVE_CONNECTIONS);
		if($railShape === null){
			throw new \InvalidArgumentException("No rail shape matches these connections");
		}
		$this->railShape = $railShape;
	}

	protected function getCurrentShapeConnections() : array{
		return RailConnectionInfo::CURVE_CONNECTIONS[$this->railShape] ?? RailConnectionInfo::CONNECTIONS[$this->railShape];
	}

	protected function getPossibleConnectionDirectionsOneConstraint(int $constraint) : array{
		$possible = parent::getPossibleConnectionDirectionsOneConstraint($constraint);

		if(($constraint & RailConnectionInfo::FLAG_ASCEND) === 0){
			foreach([
				Facing::NORTH,
				Facing::SOUTH,
				Facing::WEST,
				Facing::EAST
			] as $d){
				if($constraint !== $d){
					$possible[$d] = true;
				}
			}
		}

		return $possible;
	}

	public function getShape() : int{ return $this->railShape; }

	/** @return $this */
	public function setShape(int $shape) : self{
		if(!isset(RailConnectionInfo::CONNECTIONS[$shape]) && !isset(RailConnectionInfo::CURVE_CONNECTIONS[$shape])){
			throw new \InvalidArgumentException("Invalid shape, must be one of " . implode(", ", [...array_keys(RailConnectionInfo::CONNECTIONS), ...array_keys(RailConnectionInfo::CURVE_CONNECTIONS)]));
		}
		$this->railShape = $shape;
		return $this;
	}
}
