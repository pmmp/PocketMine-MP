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
use pocketmine\utils\Utils;
use function intdiv;

class Farmland extends Transparent{
	public const MAX_WETNESS = 7;

	private const WATER_SEARCH_HORIZONTAL_LENGTH = 9;

	private const WATER_SEARCH_VERTICAL_LENGTH = 2;

	private const WATER_POSITION_INDEX_UNKNOWN = -1;
	/** Total possible options for water X/Z indexes */
	private const WATER_POSITION_INDICES_TOTAL = (self::WATER_SEARCH_HORIZONTAL_LENGTH ** 2) * 2;

	protected int $wetness = 0; //"moisture" blockstate property in PC

	/**
	 * Cached value indicating the relative coordinates of the most recently found water block.
	 *
	 * If this is set to a non-unknown value, the farmland block will check the relative coordinates indicated by
	 * this value for water, before searching the entire 9x2x9 grid around the farmland. This significantly benefits
	 * hydrating or fully hydrated farmland, avoiding the need for costly searches on every random tick.
	 *
	 * If the coordinates indicated don't contain water, the full 9x2x9 volume will be searched as before. A new index
	 * will be recorded if water is found, otherwise it will be set to unknown and future searches will search the full
	 * 9x2x9 volume again.
	 *
	 * This property is not exposed to the API or saved on disk. It is only used by PocketMine-MP at runtime as a cache.
	 */
	private int $waterPositionIndex = self::WATER_POSITION_INDEX_UNKNOWN;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		$w->boundedIntAuto(0, self::MAX_WETNESS, $this->wetness);
		$w->boundedIntAuto(-1, self::WATER_POSITION_INDICES_TOTAL - 1, $this->waterPositionIndex);
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
	public function getWaterPositionIndex() : int{ return $this->waterPositionIndex; }

	/**
	 * @internal
	 */
	public function setWaterPositionIndex(int $waterPositionIndex) : self{
		if($waterPositionIndex < -1 || $waterPositionIndex >= self::WATER_POSITION_INDICES_TOTAL){
			throw new \InvalidArgumentException("Water XZ index must be in range -1 ... " . (self::WATER_POSITION_INDICES_TOTAL - 1));
		}
		$this->waterPositionIndex = $waterPositionIndex;
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
		$oldWaterPositionIndex = $this->waterPositionIndex;
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

		if(!$changed && $oldWaterPositionIndex !== $this->waterPositionIndex){
			//ensure the water square index is saved regardless of whether anything else happened
			$world->setBlock($this->position, $this, false);
		}
	}

	public function onEntityLand(Entity $entity) : ?float{
		if($entity instanceof Living && Utils::getRandomFloat() < $entity->getFallDistance() - 0.5){
			$ev = new EntityTrampleFarmlandEvent($entity, $this);
			$ev->call();
			if(!$ev->isCancelled()){
				$this->position->getWorld()->setBlock($this->position, VanillaBlocks::DIRT());
			}
		}
		return null;
	}

	protected function canHydrate() : bool{
		$world = $this->position->getWorld();

		$startX = $this->position->getFloorX() - (int) (self::WATER_SEARCH_HORIZONTAL_LENGTH / 2);
		$startY = $this->position->getFloorY();
		$startZ = $this->position->getFloorZ() - (int) (self::WATER_SEARCH_HORIZONTAL_LENGTH / 2);

		if($this->waterPositionIndex !== self::WATER_POSITION_INDEX_UNKNOWN){
			$raw = $this->waterPositionIndex;
			$x = $raw % self::WATER_SEARCH_HORIZONTAL_LENGTH;
			$raw = intdiv($raw, self::WATER_SEARCH_HORIZONTAL_LENGTH);
			$z = $raw % self::WATER_SEARCH_HORIZONTAL_LENGTH;
			$raw = intdiv($raw, self::WATER_SEARCH_HORIZONTAL_LENGTH);
			$y = $raw % self::WATER_SEARCH_VERTICAL_LENGTH;

			if($world->getBlockAt($startX + $x, $startY + $y, $startZ + $z) instanceof Water){
				return true;
			}
		}

		//no water found at cached position - search the whole area
		//y will increment after x/z have been exhausted, as usually water will be at the same Y as the farmland
		for($y = 0; $y < self::WATER_SEARCH_VERTICAL_LENGTH; $y++){
			for($x = 0; $x < self::WATER_SEARCH_HORIZONTAL_LENGTH; $x++){
				for($z = 0; $z < self::WATER_SEARCH_HORIZONTAL_LENGTH; $z++){
					if($world->getBlockAt($startX + $x, $startY + $y, $startZ + $z) instanceof Water){
						$this->waterPositionIndex = $x + ($z * self::WATER_SEARCH_HORIZONTAL_LENGTH) + ($y * self::WATER_SEARCH_HORIZONTAL_LENGTH ** 2);
						return true;
					}
				}
			}
		}

		$this->waterPositionIndex = self::WATER_POSITION_INDEX_UNKNOWN;
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
