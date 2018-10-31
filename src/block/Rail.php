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

use pocketmine\math\Facing;

class Rail extends BaseRail{

	/* extended meta values for regular rails, to allow curving */
	public const CURVE_SOUTHEAST = 6;
	public const CURVE_SOUTHWEST = 7;
	public const CURVE_NORTHWEST = 8;
	public const CURVE_NORTHEAST = 9;

	private const CURVE_CONNECTIONS = [
		self::CURVE_SOUTHEAST => [
			Facing::SOUTH,
			Facing::EAST
		],
		self::CURVE_SOUTHWEST => [
			Facing::SOUTH,
			Facing::WEST
		],
		self::CURVE_NORTHWEST => [
			Facing::NORTH,
			Facing::WEST
		],
		self::CURVE_NORTHEAST => [
			Facing::NORTH,
			Facing::EAST
		]
	];

	protected $id = self::RAIL;

	public function getName() : string{
		return "Rail";
	}

	protected function getMetaForState(array $connections) : int{
		try{
			return self::searchState($connections, self::CURVE_CONNECTIONS);
		}catch(\InvalidArgumentException $e){
			return parent::getMetaForState($connections);
		}
	}

	protected function getConnectionsFromMeta(int $meta) : array{
		return self::CURVE_CONNECTIONS[$meta] ?? self::CONNECTIONS[$meta] ?? [];
	}

	protected function getPossibleConnectionDirectionsOneConstraint(int $constraint) : array{
		static $horizontal = [
			Facing::NORTH,
			Facing::SOUTH,
			Facing::WEST,
			Facing::EAST
		];

		$possible = parent::getPossibleConnectionDirectionsOneConstraint($constraint);

		if(($constraint & self::FLAG_ASCEND) === 0){
			foreach($horizontal as $d){
				if($constraint !== $d){
					$possible[$d] = true;
				}
			}
		}

		return $possible;
	}
}
