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

use pocketmine\block\utils\CarpetVineGrowth;
use pocketmine\block\utils\StaticSupportTrait;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\math\Facing;

abstract class BaseCarpetWithVine extends Flowable{
	use StaticSupportTrait;

	/** @var CarpetVineGrowth[] */
	protected array $sides = [];

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		$w->carpetVineSides($this->sides);
	}

	public function getVineGrowth(int $face) : CarpetVineGrowth{
		return $this->sides[$face] ?? CarpetVineGrowth::NONE;
	}

	public function setVineGrowth(int $face, CarpetVineGrowth $value) : self{
		if($value === CarpetVineGrowth::NONE){
			unset($this->sides[$face]);
			return $this;
		}
		$this->sides[$face] = $value;
		return $this;
	}

	protected function hasFaces() : bool{
		foreach(Facing::HORIZONTAL as $f){
			if($this->getVineGrowth($f) !== CarpetVineGrowth::NONE){
				return true;
			}
		}
		return false;
	}

	protected function recalculateConnections() : bool{
		$changed = 0;

		foreach(Facing::HORIZONTAL as $f){
			$lastSide = $this->getVineGrowth($f);
			$side = CarpetVineGrowth::NONE;

			if($this->getAdjacentSupportType($f)->hasEdgeSupport()){
				$side = !$this->isCarpetPart() ? $this->getVineGrowth($f) : CarpetVineGrowth::HALF;

				if($side === CarpetVineGrowth::HALF){
					$above = $this->getSide(Facing::UP);
					if($above instanceof BaseCarpetWithVine && !$above->isCarpetPart() && $above->getVineGrowth($f) !== CarpetVineGrowth::NONE){
						$side = CarpetVineGrowth::FULL;
					}

					if(!$this->isCarpetPart()){
						$below = $this->getSide(Facing::DOWN);
						if($below instanceof BaseCarpetWithVine && $below->getVineGrowth($f) === CarpetVineGrowth::NONE){
							$side = CarpetVineGrowth::NONE;
						}
					}
				}
			}

			if($lastSide !== $side){
				$this->setVineGrowth($f, $side);
				$changed++;
			}
		}

		return $changed > 0;
	}

	abstract protected function isCarpetPart() : bool;

	public function onNearbyBlockChange() : void{
		$world = $this->position->getWorld();
		if(!$this->canBeSupportedAt($this)){
			$world->useBreakOn($this->position);
			return;
		}
		$updated = $this->recalculateConnections();
		if(!$this->isCarpetPart() && !$this->hasFaces()){
			$world->useBreakOn($this->position);
			return;
		}
		if($updated){
			$world->setBlock($this->position, $this);
		}
	}

	protected function canBeSupportedAt(Block $block) : bool{
		$below = $block->getSide(Facing::DOWN);
		if($this->isCarpetPart()){
			return $below->getTypeId() !== BlockTypeIds::AIR;
		}
		return $below instanceof BaseCarpetWithVine && $below->isCarpetPart();
	}

	public function getFlameEncouragement() : int{
		return 15;
	}

	public function getFlammability() : int{
		return 100;
	}
}
