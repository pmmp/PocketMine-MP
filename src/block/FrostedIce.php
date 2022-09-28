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

use pocketmine\block\utils\BlockDataSerializer;
use pocketmine\event\block\BlockMeltEvent;
use function mt_rand;

class FrostedIce extends Ice{
	public const MAX_AGE = 3;

	protected int $age = 0;

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->age = BlockDataSerializer::readBoundedInt("age", $stateMeta, 0, self::MAX_AGE);
	}

	protected function writeStateToMeta() : int{
		return $this->age;
	}

	public function getStateBitmask() : int{
		return 0b11;
	}

	public function getAge() : int{ return $this->age; }

	/** @return $this */
	public function setAge(int $age) : self{
		if($age < 0 || $age > self::MAX_AGE){
			throw new \InvalidArgumentException("Age must be in range 0 ... " . self::MAX_AGE);
		}
		$this->age = $age;
		return $this;
	}

	public function onNearbyBlockChange() : void{
		$world = $this->position->getWorld();
		if(!$this->checkAdjacentBlocks(2)){
			$world->useBreakOn($this->position);
		}else{
			$world->scheduleDelayedBlockUpdate($this->position, mt_rand(20, 40));
		}
	}

	public function onRandomTick() : void{
		$world = $this->position->getWorld();
		if((!$this->checkAdjacentBlocks(4) || mt_rand(0, 2) === 0) &&
			$world->getHighestAdjacentFullLightAt($this->position->x, $this->position->y, $this->position->z) >= 12 - $this->age){
			if($this->tryMelt()){
				foreach($this->getAllSides() as $block){
					if($block instanceof FrostedIce){
						$block->tryMelt();
					}
				}
			}
		}else{
			$world->scheduleDelayedBlockUpdate($this->position, mt_rand(20, 40));
		}
	}

	public function onScheduledUpdate() : void{
		$this->onRandomTick();
	}

	private function checkAdjacentBlocks(int $requirement) : bool{
		$found = 0;
		for($x = -1; $x <= 1; ++$x){
			for($z = -1; $z <= 1; ++$z){
				if($x === 0 && $z === 0){
					continue;
				}
				if(
					$this->position->getWorld()->getBlockAt($this->position->x + $x, $this->position->y, $this->position->z + $z) instanceof FrostedIce &&
					++$found >= $requirement
				){
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Updates the age of the ice, destroying it if appropriate.
	 *
	 * @return bool Whether the ice was destroyed.
	 */
	private function tryMelt() : bool{
		$world = $this->position->getWorld();
		if($this->age >= self::MAX_AGE){
			$ev = new BlockMeltEvent($this, VanillaBlocks::WATER());
			$ev->call();
			if(!$ev->isCancelled()){
				$world->setBlock($this->position, $ev->getNewState());
			}
			return true;
		}

		$this->age++;
		$world->setBlock($this->position, $this);
		$world->scheduleDelayedBlockUpdate($this->position, mt_rand(20, 40));
		return false;
	}
}
