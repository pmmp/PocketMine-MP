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

use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Player;

class Rail extends Flowable{

	public const STRAIGHT_NORTH_SOUTH = 0;
	public const STRAIGHT_EAST_WEST = 1;
	public const ASCENDING_EAST = 2;
	public const ASCENDING_WEST = 3;
	public const ASCENDING_NORTH = 4;
	public const ASCENDING_SOUTH = 5;
	public const CURVE_SOUTHEAST = 6;
	public const CURVE_SOUTHWEST = 7;
	public const CURVE_NORTHWEST = 8;
	public const CURVE_NORTHEAST = 9;

	private const ASCENDING_SIDES = [
		self::ASCENDING_NORTH => Vector3::SIDE_NORTH,
		self::ASCENDING_EAST => Vector3::SIDE_EAST,
		self::ASCENDING_SOUTH => Vector3::SIDE_SOUTH,
		self::ASCENDING_WEST => Vector3::SIDE_WEST
	];

	private const FLAG_ASCEND = 1 << 24; //used to indicate direction-up

	private const CONNECTIONS = [
		//straights
		self::STRAIGHT_NORTH_SOUTH => [
			Vector3::SIDE_NORTH,
			Vector3::SIDE_SOUTH
		],
		self::STRAIGHT_EAST_WEST => [
			Vector3::SIDE_EAST,
			Vector3::SIDE_WEST
		],

		//ascending
		self::ASCENDING_EAST => [
			Vector3::SIDE_WEST,
			Vector3::SIDE_EAST | self::FLAG_ASCEND
		],
		self::ASCENDING_WEST => [
			Vector3::SIDE_EAST,
			Vector3::SIDE_WEST | self::FLAG_ASCEND
		],
		self::ASCENDING_NORTH => [
			Vector3::SIDE_SOUTH,
			Vector3::SIDE_NORTH | self::FLAG_ASCEND
		],
		self::ASCENDING_SOUTH => [
			Vector3::SIDE_NORTH,
			Vector3::SIDE_SOUTH | self::FLAG_ASCEND
		],

		//curves
		self::CURVE_SOUTHEAST => [
			Vector3::SIDE_SOUTH,
			Vector3::SIDE_EAST
		],
		self::CURVE_SOUTHWEST => [
			Vector3::SIDE_SOUTH,
			Vector3::SIDE_WEST
		],
		self::CURVE_NORTHWEST => [
			Vector3::SIDE_NORTH,
			Vector3::SIDE_WEST
		],
		self::CURVE_NORTHEAST => [
			Vector3::SIDE_NORTH,
			Vector3::SIDE_EAST
		]
	];

	protected $id = self::RAIL;

	public function __construct(int $meta = 0){
		$this->meta = $meta;
	}

	public function getName() : string{
		return "Rail";
	}

	public function getHardness() : float{
		return 0.7;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
		if(!$blockReplace->getSide(Vector3::SIDE_DOWN)->isTransparent() and $this->getLevel()->setBlock($blockReplace, $this, true, true)){
			$this->tryReconnect();
			return true;
		}

		return false;
	}

	public function canCurve() : bool{
		return true;
	}

	/**
	 * Returns all the directions this rail is already connected in.
	 *
	 * @return int[]
	 */
	public function getConnectedDirections() : array{
		/** @var int[] $connections */
		$connections = [];

		/** @var int $connection */
		foreach(self::CONNECTIONS[$this->meta] as $connection){
			$other = $this->getSide($connection & ~self::FLAG_ASCEND);
			$otherConnection = Vector3::getOppositeSide($connection & ~self::FLAG_ASCEND);

			if(($connection & self::FLAG_ASCEND) !== 0){
				$other = $other->getSide(Vector3::SIDE_UP);

			}elseif(!($other instanceof Rail)){ //check for rail sloping up to meet this one
				$other = $other->getSide(Vector3::SIDE_DOWN);
				$otherConnection |= self::FLAG_ASCEND;
			}

			if(
				$other instanceof Rail and
				in_array($otherConnection, self::CONNECTIONS[$other->meta], true)
			){
				$connections[] = $connection;
			}
		}

		return $connections;
	}

	private function getPossibleConnectionDirections(array $constraints) : array{
		if(count($constraints) >= 2){
			return [];
		}
		$possible = [
			Vector3::SIDE_NORTH => true,
			Vector3::SIDE_SOUTH => true,
			Vector3::SIDE_WEST => true,
			Vector3::SIDE_EAST => true
		];
		foreach($possible as $p => $_){
			$possible[$p | self::FLAG_ASCEND] = true;
		}

		//Based on existing connections, constrain candidates for reconnecting.
		foreach($constraints as $constraint){
			$opposite = Vector3::getOppositeSide($constraint & ~self::FLAG_ASCEND);

			//Ascending rails and non-curvable rails can only connect to the opposite direction of existing connections.
			if(($constraint & self::FLAG_ASCEND) !== 0 or !$this->canCurve()){
				if(($constraint & self::FLAG_ASCEND) === 0){
					//We can slope the other way if this connection isn't already a slope
					$possible = [$opposite => true, $opposite | self::FLAG_ASCEND => true];
				}else{
					$possible = [$opposite => true];
				}
				break;
			}

			//Not possible to connect in an already-connected direction
			unset($possible[$constraint], $possible[$constraint | self::FLAG_ASCEND]);

			//Not possible to slope in a direction not directly opposite to this connection - we can't slope and curve at the same time
			foreach($possible as $p => $_){
				if(($p & self::FLAG_ASCEND) !== 0 and $p !== ($opposite | self::FLAG_ASCEND)){
					unset($possible[$p]);
				}
			}
		}

		return $possible;
	}

	private function tryReconnect() : void{
		$thisConnections = $this->getConnectedDirections();
		$changed = false;

		do{
			$possible = $this->getPossibleConnectionDirections($thisConnections);
			$continue = false;

			foreach($possible as $thisSide => $_){
				$otherSide = Vector3::getOppositeSide($thisSide & ~self::FLAG_ASCEND);

				$other = $this->getSide($thisSide & ~self::FLAG_ASCEND);

				if(($thisSide & self::FLAG_ASCEND) !== 0){
					$other = $other->getSide(Vector3::SIDE_UP);

				}elseif(!($other instanceof Rail)){ //check if other rails can slope up to meet this one
					$other = $other->getSide(Vector3::SIDE_DOWN);
					$otherSide |= self::FLAG_ASCEND;
				}

				if(!($other instanceof Rail) or count($otherConnections = $other->getConnectedDirections()) >= 2){
					//we can only connect to a rail that has less than 2 connections
					continue;
				}

				$otherPossible = $other->getPossibleConnectionDirections($otherConnections);

				if(isset($otherPossible[$otherSide])){
					$otherConnections[] = $otherSide;
					$other->updateState($otherConnections);

					$changed = true;
					$thisConnections[] = $thisSide;
					$continue = count($thisConnections) < 2;

					break; //force recomputing possible directions, since this connection could invalidate others
				}
			}
		}while($continue);

		if($changed){
			$this->updateState($thisConnections);
		}
	}

	private function updateState(array $connections) : void{
		if(count($connections) < 2){
			$connections[] = Vector3::getOppositeSide($connections[0] & ~self::FLAG_ASCEND);
		}

		$meta = array_search($connections, self::CONNECTIONS, true);
		if($meta === false){
			$meta = array_search(array_reverse($connections), self::CONNECTIONS, true);
		}
		if($meta === false){
			throw new \InvalidStateException("Failed to bruteforce rail state for " . implode(", ", $connections));
		}

		$this->meta = $meta;
		$this->level->setBlock($this, $this, false, false); //avoid recursion
	}

	public function onNearbyBlockChange() : void{
		if($this->getSide(Vector3::SIDE_DOWN)->isTransparent() or (
			isset(self::ASCENDING_SIDES[$this->meta]) and
			$this->getSide(self::ASCENDING_SIDES[$this->meta])->isTransparent()
		)){
			$this->getLevel()->useBreakOn($this);
		}
	}

	public function getVariantBitmask() : int{
		return 0;
	}
}
