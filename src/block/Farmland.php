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

	private const WATER_SEARCH_HORIZONTAL_LENGTH = 9;

	private const WATER_SEARCH_VERTICAL_LENGTH = 2;

	private const WATER_XZ_INDEX_UNKNOWN = -1;
	/** Total possible options for water X/Z indexes */
	private const WATER_XZ_INDICES_TOTAL = self::WATER_SEARCH_HORIZONTAL_LENGTH ** 2;

	protected int $wetness = 0; //"moisture" blockstate property in PC

	/**
	 * Cached value indicating the X/Z relative coordinates of the most recently found water block.
	 *
	 * If this is set to a non-unknown value, the block will search for water at the X/Z relative coordinates indicated
	 * by this value before searching the entire 9x2x9 grid around the farmland. If nearby water sources don't change
	 * much (the 99% case for large farms, unless redstone is involved), this reduces the average number of blocks
	 * searched for water from about 41 to 2, which is a significant performance improvement.
	 *
	 * If the X/Z column indicated doesn't contain any water blocks, the full 9x2x9 volume will be searched as before.
	 * A new index will be recorded if water is found, otherwise it will be set to unknown and future searches will
	 * search the full 9x2x9 volume again.
	 *
	 * This property is not exposed to the API or saved on disk. It is only used by PocketMine-MP at runtime as a cache.
	 */
	private int $waterXZIndex = self::WATER_XZ_INDEX_UNKNOWN;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		$w->boundedInt(3, 0, self::MAX_WETNESS, $this->wetness);
		$w->boundedInt(7, -1, self::WATER_XZ_INDICES_TOTAL - 1, $this->waterXZIndex);
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
	public function getWaterXZIndex() : int{ return $this->waterXZIndex; }

	/**
	 * @internal
	 */
	public function setWaterXZIndex(int $waterXZIndex) : self{
		if($waterXZIndex < -1 || $waterXZIndex >= self::WATER_XZ_INDICES_TOTAL){
			throw new \InvalidArgumentException("Water XZ index must be in range -1 ... " . (self::WATER_XZ_INDICES_TOTAL - 1));
		}
		$this->waterXZIndex = $waterXZIndex;
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
		$oldWaterXZIndex = $this->waterXZIndex;
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

		if(!$changed && $oldWaterXZIndex !== $this->waterXZIndex){
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

	protected function canHydrate() : bool{
		$world = $this->position->getWorld();

		$startX = $this->position->getFloorX() - (int) (self::WATER_SEARCH_HORIZONTAL_LENGTH / 2);
		$startY = $this->position->getFloorY();
		$startZ = $this->position->getFloorZ() - (int) (self::WATER_SEARCH_HORIZONTAL_LENGTH / 2);

		if($this->waterXZIndex !== self::WATER_XZ_INDEX_UNKNOWN){
			$x = $this->waterXZIndex % self::WATER_SEARCH_HORIZONTAL_LENGTH;
			$z = intdiv($this->waterXZIndex, self::WATER_SEARCH_HORIZONTAL_LENGTH) % self::WATER_SEARCH_HORIZONTAL_LENGTH;

			for($y = 0; $y < self::WATER_SEARCH_VERTICAL_LENGTH; $y++){
				if($world->getBlockAt($startX + $x, $startY + $y, $startZ + $z) instanceof Water){
					return true;
				}
			}
		}

		//no water found at cached position - search the whole area
		//y will increment after x/z have been exhausted, as usually water will be at the same Y as the farmland
		for($y = 0; $y < self::WATER_SEARCH_VERTICAL_LENGTH; $y++){
			for($x = 0; $x < self::WATER_SEARCH_HORIZONTAL_LENGTH; $x++){
				for($z = 0; $z < self::WATER_SEARCH_HORIZONTAL_LENGTH; $z++){
					if($world->getBlockAt($startX + $x, $startY + $y, $startZ + $z) instanceof Water){
						$this->waterXZIndex = $x + ($z * self::WATER_SEARCH_HORIZONTAL_LENGTH);
						return true;
					}
				}
			}
		}

		$this->waterXZIndex = self::WATER_XZ_INDEX_UNKNOWN;
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
