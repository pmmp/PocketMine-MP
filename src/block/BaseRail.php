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
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use function array_reverse;
use function array_search;
use function array_shift;
use function count;
use function in_array;

abstract class BaseRail extends Flowable{

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if(!$blockReplace->getSide(Facing::DOWN)->isTransparent()){
			return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
		}

		return false;
	}

	public function onPostPlace() : void{
		$this->tryReconnect();
	}

	/**
	 * @param int[]   $connections
	 * @param int[][] $lookup
	 * @phpstan-param array<int, list<int>> $lookup
	 */
	protected static function searchState(array $connections, array $lookup) : ?int{
		$shape = array_search($connections, $lookup, true);
		if($shape === false){
			$shape = array_search(array_reverse($connections), $lookup, true);
		}
		return $shape === false ? null : $shape;
	}

	/**
	 * Sets the rail shape according to the given connections, if a shape matches.
	 *
	 * @param int[] $connections
	 *
	 * @throws \InvalidArgumentException if no shape matches the given connections
	 */
	abstract protected function setShapeFromConnections(array $connections) : void;

	/**
	 * Returns the connection directions of this rail (depending on the current block state)
	 *
	 * @return int[]
	 */
	abstract protected function getCurrentShapeConnections() : array;

	/**
	 * Returns all the directions this rail is already connected in.
	 *
	 * @return int[]
	 */
	private function getConnectedDirections() : array{
		/** @var int[] $connections */
		$connections = [];

		/** @var int $connection */
		foreach($this->getCurrentShapeConnections() as $connection){
			$other = $this->getSide($connection & ~RailConnectionInfo::FLAG_ASCEND);
			$otherConnection = Facing::opposite($connection & ~RailConnectionInfo::FLAG_ASCEND);

			if(($connection & RailConnectionInfo::FLAG_ASCEND) !== 0){
				$other = $other->getSide(Facing::UP);

			}elseif(!($other instanceof BaseRail)){ //check for rail sloping up to meet this one
				$other = $other->getSide(Facing::DOWN);
				$otherConnection |= RailConnectionInfo::FLAG_ASCEND;
			}

			if(
				$other instanceof BaseRail and
				in_array($otherConnection, $other->getCurrentShapeConnections(), true)
			){
				$connections[] = $connection;
			}
		}

		return $connections;
	}

	/**
	 * @param int[] $constraints
	 *
	 * @return true[]
	 * @phpstan-return array<int, true>
	 */
	private function getPossibleConnectionDirections(array $constraints) : array{
		switch(count($constraints)){
			case 0:
				//No constraints, can connect in any direction
				$possible = [
					Facing::NORTH => true,
					Facing::SOUTH => true,
					Facing::WEST => true,
					Facing::EAST => true
				];
				foreach($possible as $p => $_){
					$possible[$p | RailConnectionInfo::FLAG_ASCEND] = true;
				}

				return $possible;
			case 1:
				return $this->getPossibleConnectionDirectionsOneConstraint(array_shift($constraints));
			case 2:
				return [];
			default:
				throw new \InvalidArgumentException("Expected at most 2 constraints, got " . count($constraints));
		}
	}

	/**
	 * @return true[]
	 * @phpstan-return array<int, true>
	 */
	protected function getPossibleConnectionDirectionsOneConstraint(int $constraint) : array{
		$opposite = Facing::opposite($constraint & ~RailConnectionInfo::FLAG_ASCEND);

		$possible = [$opposite => true];

		if(($constraint & RailConnectionInfo::FLAG_ASCEND) === 0){
			//We can slope the other way if this connection isn't already a slope
			$possible[$opposite | RailConnectionInfo::FLAG_ASCEND] = true;
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
				$otherSide = Facing::opposite($thisSide & ~RailConnectionInfo::FLAG_ASCEND);

				$other = $this->getSide($thisSide & ~RailConnectionInfo::FLAG_ASCEND);

				if(($thisSide & RailConnectionInfo::FLAG_ASCEND) !== 0){
					$other = $other->getSide(Facing::UP);

				}elseif(!($other instanceof BaseRail)){ //check if other rails can slope up to meet this one
					$other = $other->getSide(Facing::DOWN);
					$otherSide |= RailConnectionInfo::FLAG_ASCEND;
				}

				if(!($other instanceof BaseRail) or count($otherConnections = $other->getConnectedDirections()) >= 2){
					//we can only connect to a rail that has less than 2 connections
					continue;
				}

				$otherPossible = $other->getPossibleConnectionDirections($otherConnections);

				if(isset($otherPossible[$otherSide])){
					$otherConnections[] = $otherSide;
					$other->setConnections($otherConnections);
					$this->pos->getWorld()->setBlock($other->pos, $other);

					$changed = true;
					$thisConnections[] = $thisSide;
					$continue = count($thisConnections) < 2;

					break; //force recomputing possible directions, since this connection could invalidate others
				}
			}
		}while($continue);

		if($changed){
			$this->setConnections($thisConnections);
			$this->pos->getWorld()->setBlock($this->pos, $this);
		}
	}

	/**
	 * @param int[] $connections
	 */
	private function setConnections(array $connections) : void{
		if(count($connections) === 1){
			$connections[] = Facing::opposite($connections[0] & ~RailConnectionInfo::FLAG_ASCEND);
		}elseif(count($connections) !== 2){
			throw new \InvalidArgumentException("Expected exactly 2 connections, got " . count($connections));
		}

		$this->setShapeFromConnections($connections);
	}

	public function onNearbyBlockChange() : void{
		if($this->getSide(Facing::DOWN)->isTransparent()){
			$this->pos->getWorld()->useBreakOn($this->pos);
		}else{
			foreach($this->getCurrentShapeConnections() as $connection){
				if(($connection & RailConnectionInfo::FLAG_ASCEND) !== 0 and $this->getSide($connection & ~RailConnectionInfo::FLAG_ASCEND)->isTransparent()){
					$this->pos->getWorld()->useBreakOn($this->pos);
					break;
				}
			}
		}
	}
}
