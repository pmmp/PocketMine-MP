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

use pocketmine\block\utils\BlockDataValidator;
use function max;
use function mt_rand;

class FrostedIce extends Ice{

	/** @var int */
	protected $age = 0;

	public function __construct(BlockIdentifier $idInfo, string $name, ?BlockBreakInfo $breakInfo = null){
		parent::__construct($idInfo, $name, $breakInfo ?? new BlockBreakInfo(2.5, BlockToolType::TYPE_PICKAXE));
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->age = BlockDataValidator::readBoundedInt("age", $stateMeta, 0, 3);
	}

	protected function writeStateToMeta() : int{
		return $this->age;
	}

	public function getStateBitmask() : int{
		return 0b11;
	}

	public function onNearbyBlockChange() : void{
		if(!$this->checkAdjacentBlocks(2)){
			$this->world->useBreakOn($this);
		}else{
			$this->world->scheduleDelayedBlockUpdate($this, mt_rand(20, 40));
		}
	}

	public function onRandomTick() : void{
		if((!$this->checkAdjacentBlocks(4) or mt_rand(0, 2) === 0) and
			max( //TODO: move this to World
				$this->world->getHighestAdjacentBlockLight($this->x, $this->y, $this->z),
				$this->world->getHighestAdjacentBlockSkyLight($this->x, $this->y, $this->z) - $this->world->getSkyLightReduction()
			) >= 12 - $this->age){
			if($this->tryMelt()){
				foreach($this->getAllSides() as $block){
					if($block instanceof FrostedIce){
						$block->tryMelt();
					}
				}
			}
		}else{
			$this->world->scheduleDelayedBlockUpdate($this, mt_rand(20, 40));
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
					$this->world->getBlockAt($this->x + $x, $this->y, $this->z + $z) instanceof FrostedIce and
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
			$this->world->useBreakOn($this);
			return true;
		}

		$this->age++;
		$this->world->setBlock($this, $this);
		$this->world->scheduleDelayedBlockUpdate($this, mt_rand(20, 40));
		return false;
	}
}
