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
use function mt_rand;

class FrostedIce extends Ice{

	/** @var int */
	protected $age = 0;

	public function __construct(BlockIdentifier $idInfo, string $name, ?BlockBreakInfo $breakInfo = null){
		parent::__construct($idInfo, $name, $breakInfo ?? new BlockBreakInfo(2.5, BlockToolType::PICKAXE));
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->age = BlockDataSerializer::readBoundedInt("age", $stateMeta, 0, 3);
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
		if($age < 0 || $age > 3){
			throw new \InvalidArgumentException("Age must be in range 0-3");
		}
		$this->age = $age;
		return $this;
	}

	public function onNearbyBlockChange() : void{
		if(!$this->checkAdjacentBlocks(2)){
			$this->pos->getWorld()->useBreakOn($this->pos);
		}else{
			$this->pos->getWorld()->scheduleDelayedBlockUpdate($this->pos, mt_rand(20, 40));
		}
	}

	public function onRandomTick() : void{
		if((!$this->checkAdjacentBlocks(4) or mt_rand(0, 2) === 0) and
			$this->pos->getWorld()->getHighestAdjacentFullLightAt($this->pos->x, $this->pos->y, $this->pos->z) >= 12 - $this->age){
			if($this->tryMelt()){
				foreach($this->getAllSides() as $block){
					if($block instanceof FrostedIce){
						$block->tryMelt();
					}
				}
			}
		}else{
			$this->pos->getWorld()->scheduleDelayedBlockUpdate($this->pos, mt_rand(20, 40));
		}
	}

	public function onScheduledUpdate() : void{
		$this->onRandomTick();
	}

	private function checkAdjacentBlocks(int $requirement) : bool{
		$found = 0;
		for($x = -1; $x <= 1; ++$x){
			for($z = -1; $z <= 1; ++$z){
				if($x === 0 and $z === 0){
					continue;
				}
				if(
					$this->pos->getWorld()->getBlockAt($this->pos->x + $x, $this->pos->y, $this->pos->z + $z) instanceof FrostedIce and
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
		if($this->age >= 3){
			$this->pos->getWorld()->useBreakOn($this->pos);
			return true;
		}

		$this->age++;
		$this->pos->getWorld()->setBlock($this->pos, $this);
		$this->pos->getWorld()->scheduleDelayedBlockUpdate($this->pos, mt_rand(20, 40));
		return false;
	}
}
