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

use pocketmine\block\utils\AgeableTrait;
use pocketmine\block\utils\BlockEventHelper;
use pocketmine\block\utils\StaticSupportTrait;
use pocketmine\item\Fertilizer;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use function mt_rand;

abstract class Crops extends Flowable{
	use AgeableTrait;
	use StaticSupportTrait;

	public const MAX_AGE = 7;

	private function canBeSupportedAt(Block $block) : bool{
		return $block->getSide(Facing::DOWN)->getTypeId() === BlockTypeIds::FARMLAND;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if($this->age < self::MAX_AGE && $item instanceof Fertilizer){
			$block = clone $this;
			$tempAge = $block->age + mt_rand(2, 5);
			if($tempAge > self::MAX_AGE){
				$tempAge = self::MAX_AGE;
			}
			$block->age = $tempAge;
			if(BlockEventHelper::grow($this, $block, $player)){
				$item->pop();
			}

			return true;
		}

		return false;
	}

	public function ticksRandomly() : bool{
		return $this->age < self::MAX_AGE;
	}

	public function onRandomTick() : void{
		$above = $this->getSide(Facing::UP);

		if ($above->blocksDirectSkyLight() || $this->getPosition()->getWorld()->getFullLight($above->getPosition()) < 9) {
			return;
		}

		$points = 2;
		$below = $this->getSide(Facing::DOWN);

		if (!$below instanceof Farmland) return;
		$points += $below->getWetness() > 0 ? 4 : 2;

		$adjacent = [];
		$corners = [];
		foreach ([Facing::NORTH, Facing::EAST, Facing::SOUTH, Facing::WEST] as $side) {
			$adjacent[$side] = $below->getSide($side);
		}

		foreach ([Facing::EAST, Facing::WEST] as $side) {
			$corners[Facing::NORTH][$side] = $adjacent[Facing::NORTH]->getSide($side);
			$corners[Facing::SOUTH][$side] = $adjacent[Facing::SOUTH]->getSide($side);
		}

		$nS = $eW = false;
		foreach ($adjacent as $side => $block) {
			if ($block instanceof Farmland) {
				$points += $block->getWetness() > 0 ? 3 : 1;

				$cropAbove = $block->getSide(Facing::UP);
				if ($cropAbove instanceof Crops && $cropAbove->getTypeId() === $this->getTypeId()) {
					if (in_array($side, [Facing::NORTH, Facing::SOUTH])) $nS = true;
					if (in_array($side, [Facing::EAST, Facing::WEST])) $eW = true;
				}
			}
		}

		$cE = $cW = false;
		foreach ($corners as $sA) {
			foreach ($sA as $side => $block) {
				if ($block instanceof Farmland) {
					$points += $block->getWetness() > 0 ? 0.75 : 0.25;

					$cropAbove = $block->getSide(Facing::UP);
					if ($cropAbove instanceof Crops && $cropAbove->getTypeId() === $this->getTypeId()) {
						if ($side === Facing::EAST) $cE = true;
						if ($side === Facing::WEST) $cW = true;
					}
				}
			}
		}

		if ($nS && $eW && ($cE || $cW)) $points /= 2;

		$percent = 1 / (floor(25 / $points) + 1);

		if($this->age < self::MAX_AGE && $percent >= lcg_value()){
			$block = clone $this;
			++$block->age;
			BlockEventHelper::grow($this, $block, null);
		}
	}
}
