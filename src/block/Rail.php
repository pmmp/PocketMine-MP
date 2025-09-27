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

use pocketmine\block\utils\RailConnectionInfo;
use pocketmine\block\utils\RailShape;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\math\Facing;

class Rail extends BaseRail{

	private RailShape $railShape = RailShape::FLAT_AXIS_Z;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		$w->enum($this->railShape);
	}

	protected function setShapeFromConnections(array $connections) : void{
		$railShape = self::searchState($connections, RailConnectionInfo::CONNECTIONS) ?? self::searchState($connections, RailConnectionInfo::CURVE_CONNECTIONS);
		if($railShape === null){
			throw new \InvalidArgumentException("No rail shape matches these connections");
		}
		$this->railShape = RailShape::from($railShape);
	}

	protected function getCurrentShapeConnections() : array{
		return RailConnectionInfo::CURVE_CONNECTIONS[$this->railShape->value] ?? RailConnectionInfo::CONNECTIONS[$this->railShape->value];
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
				if($constraint !== $d->value){
					$possible[$d->value] = true;
				}
			}
		}

		return $possible;
	}

	public function getShape() : RailShape{ return $this->railShape; }

	/** @return $this */
	public function setShape(RailShape $shape) : self{
		$this->railShape = $shape;
		return $this;
	}
}
