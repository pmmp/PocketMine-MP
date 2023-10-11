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

use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\event\block\FarmlandHydrationChangeEvent;
use pocketmine\event\entity\EntityTrampleFarmlandEvent;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use function intdiv;
use function lcg_value;

class Farmland extends Transparent{
	public const MAX_WETNESS = 7;

	private const WATER_SQUARE_INDEX_UNKNOWN = -1;

	private const WATER_SEARCH_HORIZONTAL_LENGTH = 9;
	private const WATER_SEARCH_VERTICAL_LENGTH = 2;

	/**
	 * Known water location is recorded to a square (xyz 3x1x3) in the search area with this edge length.
	 * Smaller values increase precision, but also require more bits in the blockstate data to store.
	 *
	 * Why, you might ask, not just store the exact coordinates of the water block? That would require 8 bits to store,
	 * requiring the standard size of state data to be increased. While this is easily possible, it would reduce
	 * performance in other areas due to less efficient hashtable indexing.
	 *
	 * This value must divide WATER_SEARCH_HORIZONTAL_LENGTH with no remainder.
	 */
	private const WATER_LOCATION_SQUARE_EDGE_LENGTH = 3;
	/** Number of location squares along each horizontal axis of the search area. Do not change this value. */
	private const WATER_SEARCH_SQUARES_PER_AXIS = self::WATER_SEARCH_HORIZONTAL_LENGTH / self::WATER_LOCATION_SQUARE_EDGE_LENGTH;
	/** Total location squares in the search area. Do not change this value. */
	private const WATER_SEARCH_SQUARES_TOTAL = self::WATER_SEARCH_SQUARES_PER_AXIS ** 2 * self::WATER_SEARCH_VERTICAL_LENGTH;

	protected int $wetness = 0; //"moisture" blockstate property in PC

	/**
	 * Approximate location (to an xyz 3x1x3 square) of a known water block found by a previous search.
	 *
	 * If this is set to a non-unknown value, the 3x1x3 square of blocks indicated will be searched for water before
	 * searching the entire 9x2x9 grid around the farmland. If nearby water sources don't change much (the 99% case for
	 * large farms, unless redstone is involved), this reduces the average number of blocks searched for water from
	 * about 41 to 5, which is a significant performance improvement.
	 *
	 * If the 3x1x3 square of blocks indicated doesn't contain any water blocks, the full 9x2x9 volume will be searched
	 * as before. A new square index will be recorded if water is found, otherwise it will be set to unknown and future
	 * searches will search the full 9x2x9 volume again.
	 *
	 * This property is not exposed to the API or saved on disk. It is only used by PocketMine-MP at runtime as a cache.
	 */
	private int $waterSquareIndex = self::WATER_SQUARE_INDEX_UNKNOWN;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		$w->boundedInt(3, 0, self::MAX_WETNESS, $this->wetness);
		$w->boundedInt(5, -1, self::WATER_SEARCH_SQUARES_TOTAL - 1, $this->waterSquareIndex);
	}

	public function getWetness() : int{ return $this->wetness; }

	/** @return $this */
	public function setWetness(int $wetness) : self{
		if($wetness < 0 || $wetness > self::MAX_WETNESS){
			throw new \InvalidArgumentException("Wetness must be in range 0 ... " . self::MAX_WETNESS);
		}
		$this->wetness = $wetness;
		return $this;
	}

	/**
	 * @internal
	 */
	public function getWaterSquareIndex() : int{ return $this->waterSquareIndex; }

	/**
	 * @internal
	 */
	public function setWaterSquareIndex(int $waterSquareIndex) : self{
		if($waterSquareIndex < -1 || $waterSquareIndex >= self::WATER_SEARCH_SQUARES_TOTAL){
			throw new \InvalidArgumentException("Water square index must be in range -1 ... " . (self::WATER_SEARCH_SQUARES_TOTAL - 1));
		}
		$this->waterSquareIndex = $waterSquareIndex;
		return $this;
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array{
		return [AxisAlignedBB::one()->trim(Facing::UP, 1 / 16)];
	}

	public function onNearbyBlockChange() : void{
		if($this->getSide(Facing::UP)->isSolid()){
			$this->position->getWorld()->setBlock($this->position, VanillaBlocks::DIRT());
		}
	}

	public function ticksRandomly() : bool{
		return true;
	}

	public function onRandomTick() : void{
		$world = $this->position->getWorld();

		//this property may be updated by canHydrate() - track this so we know if we need to set the block again
		$oldWaterSquareIndex = $this->waterSquareIndex;
		$changed = false;

		if(!$this->canHydrate()){
			if($this->wetness > 0){
				$event = new FarmlandHydrationChangeEvent($this, $this->wetness, $this->wetness - 1);
				$event->call();
				if(!$event->isCancelled()){
					$this->wetness = $event->getNewHydration();
					$world->setBlock($this->position, $this, false);
					$changed = true;
				}
			}else{
				$world->setBlock($this->position, VanillaBlocks::DIRT());
				$changed = true;
			}
		}elseif($this->wetness < self::MAX_WETNESS){
			$event = new FarmlandHydrationChangeEvent($this, $this->wetness, self::MAX_WETNESS);
			$event->call();
			if(!$event->isCancelled()){
				$this->wetness = $event->getNewHydration();
				$world->setBlock($this->position, $this, false);
				$changed = true;
			}
		}

		if(!$changed && $oldWaterSquareIndex !== $this->waterSquareIndex){
			//ensure the water square index is saved regardless of whether anything else happened
			$world->setBlock($this->position, $this, false);
		}
	}

	public function onEntityLand(Entity $entity) : ?float{
		if($entity instanceof Living && lcg_value() < $entity->getFallDistance() - 0.5){
			$ev = new EntityTrampleFarmlandEvent($entity, $this);
			$ev->call();
			if(!$ev->isCancelled()){
				$this->getPosition()->getWorld()->setBlock($this->getPosition(), VanillaBlocks::DIRT());
			}
		}
		return null;
	}

	private static function coordOffsetFromSquareIndexValue(int $value) : int{
		return ($value * self::WATER_LOCATION_SQUARE_EDGE_LENGTH) - (int) (self::WATER_SEARCH_HORIZONTAL_LENGTH / 2);
	}

	private function findWaterInLocationSquare(int $squareIndex) : bool{
		$raw = $squareIndex;

		$baseX = $this->position->getFloorX() + self::coordOffsetFromSquareIndexValue($raw % self::WATER_SEARCH_SQUARES_PER_AXIS);
		$raw = intdiv($raw, self::WATER_SEARCH_SQUARES_PER_AXIS);

		$baseZ = $this->position->getFloorZ() + self::coordOffsetFromSquareIndexValue($raw % self::WATER_SEARCH_SQUARES_PER_AXIS);
		$raw = intdiv($raw, self::WATER_SEARCH_SQUARES_PER_AXIS);

		$baseY = $this->position->getFloorY() + ($raw % self::WATER_SEARCH_VERTICAL_LENGTH);

		$world = $this->position->getWorld();

		for($x = 0; $x < self::WATER_LOCATION_SQUARE_EDGE_LENGTH; $x++){
			for($z = 0; $z < self::WATER_LOCATION_SQUARE_EDGE_LENGTH; $z++){
				if($world->getBlockAt($baseX + $x, $baseY, $baseZ + $z) instanceof Water){
					return true;
				}
			}
		}

		return false;
	}

	protected function canHydrate() : bool{
		if($this->waterSquareIndex !== self::WATER_SQUARE_INDEX_UNKNOWN){
			if($this->findWaterInLocationSquare($this->waterSquareIndex)){
				return true;
			}
		}

		for($squareIndex = 0; $squareIndex < self::WATER_SEARCH_SQUARES_TOTAL; $squareIndex++){
			if($squareIndex === $this->waterSquareIndex){
				continue;
			}

			if($this->findWaterInLocationSquare($squareIndex)){
				$this->waterSquareIndex = $squareIndex;
				return true;
			}
		}

		$this->waterSquareIndex = self::WATER_SQUARE_INDEX_UNKNOWN;
		return false;
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [
			VanillaBlocks::DIRT()->asItem()
		];
	}

	public function getPickedItem(bool $addUserData = false) : Item{
		return VanillaBlocks::DIRT()->asItem();
	}
}
