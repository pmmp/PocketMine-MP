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

use pocketmine\block\utils\PaleMossVineGrowth;
use pocketmine\block\utils\StaticSupportTrait;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\item\Item;
use pocketmine\math\Facing;

class PaleMossVine extends Flowable{
	use StaticSupportTrait;

	/** @var PaleMossVineGrowth[] */
	protected array $sides = [];

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		$w->paleMossVineSides($this->sides);
	}

	public function getVineGrowth(int $face) : PaleMossVineGrowth{
		return $this->sides[$face] ?? PaleMossVineGrowth::NONE;
	}

	public function setVineGrowth(int $face, PaleMossVineGrowth $value) : self{
		if($value === PaleMossVineGrowth::NONE){
			unset($this->sides[$face]);
			return $this;
		}
		$this->sides[$face] = $value;
		return $this;
	}

	protected function hasFaces() : bool{
		foreach(Facing::HORIZONTAL as $f){
			if($this->getVineGrowth($f) !== PaleMossVineGrowth::NONE){
				return true;
			}
		}
		return false;
	}

	protected function recalculateConnections() : bool{
		$changed = 0;

		foreach(Facing::HORIZONTAL as $f){
			$lastSide = $this->getVineGrowth($f);
			$side = PaleMossVineGrowth::NONE;

			if($this->getAdjacentSupportType($f)->hasEdgeSupport()){
				$side = !$this->isCarpetPart() ? $this->getVineGrowth($f) : PaleMossVineGrowth::HALF;

				if($side === PaleMossVineGrowth::HALF){
					$above = $this->getSide(Facing::UP);
					if($above instanceof PaleMossVine && !$above->isCarpetPart() && $above->getVineGrowth($f) !== PaleMossVineGrowth::NONE){
						$side = PaleMossVineGrowth::FULL;
					}

					if(!$this->isCarpetPart()){
						$below = $this->getSide(Facing::DOWN);
						if($below instanceof PaleMossVine && $below->getVineGrowth($f) === PaleMossVineGrowth::NONE){
							$side = PaleMossVineGrowth::NONE;
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

	public function isCarpetPart() : bool{ return false; }

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
		return $below instanceof PaleMossVine && $below->isCarpetPart();
	}

	protected function recalculateCollisionBoxes() : array{
		return [];
	}

	public function getDrops(Item $item) : array{
		return [];
	}

	public function asItem() : Item{
		return VanillaBlocks::PALE_MOSS_CARPET()->asItem();
	}

	public function getFlameEncouragement() : int{
		return 15;
	}

	public function getFlammability() : int{
		return 100;
	}
}
