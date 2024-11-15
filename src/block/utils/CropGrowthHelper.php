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

namespace pocketmine\block\utils;

use pocketmine\block\Block;
use pocketmine\block\Farmland;
use function mt_rand;

final class CropGrowthHelper{

	private const ON_HYDRATED_FARMLAND_BONUS = 3;
	private const ON_DRY_FARMLAND_BONUS = 1;
	private const ADJACENT_HYDRATED_FARMLAND_BONUS = 3 / 4;
	private const ADJACENT_DRY_FARMLAND_BONUS = 1 / 4;

	private const IMPROPER_ARRANGEMENT_DIVISOR = 2;

	private const MIN_LIGHT_LEVEL = 9;

	private function __construct(){
		//NOOP
	}

	/**
	 * Returns the speed at which this crop will grow, depending on its surroundings.
	 * The default is once every 26 random ticks.
	 *
	 * Things which influence this include nearby farmland (bonus for hydrated farmland) and the position of other
	 * nearby crops of the same type (nearby crops of the same type will negatively influence growth speed unless
	 * planted in rows and properly spaced apart).
	 */
	public static function calculateMultiplier(Block $block) : float{
		$result = 1;

		$position = $block->getPosition();

		$world = $position->getWorld();
		$baseX = $position->getFloorX();
		$baseY = $position->getFloorY();
		$baseZ = $position->getFloorZ();

		$farmland = $world->getBlockAt($baseX, $baseY - 1, $baseZ);

		if($farmland instanceof Farmland){
			$result += $farmland->getWetness() > 0 ? self::ON_HYDRATED_FARMLAND_BONUS : self::ON_DRY_FARMLAND_BONUS;
		}

		$xRow = false;
		$zRow = false;
		$improperArrangement = false;

		for($x = -1; $x <= 1; $x++){
			for($z = -1; $z <= 1; $z++){
				if($x === 0 && $z === 0){
					continue;
				}
				$nextFarmland = $world->getBlockAt($baseX + $x, $baseY - 1, $baseZ + $z);

				if(!$nextFarmland instanceof Farmland){
					continue;
				}

				$result += $nextFarmland->getWetness() > 0 ? self::ADJACENT_HYDRATED_FARMLAND_BONUS : self::ADJACENT_DRY_FARMLAND_BONUS;

				if(!$improperArrangement){
					$nextCrop = $world->getBlockAt($baseX + $x, $baseY, $baseZ + $z);
					if($nextCrop->hasSameTypeId($block)){
						match(0){
							$x => $zRow ? $improperArrangement = true : $xRow = true,
							$z => $xRow ? $improperArrangement = true : $zRow = true,
							default => $improperArrangement = true,
						};
					}
				}
			}
		}

		//crops can be arranged in rows, but the rows must not cross and must be spaced apart by at least one block
		if($improperArrangement){
			$result /= self::IMPROPER_ARRANGEMENT_DIVISOR;
		}

		return $result;
	}

	public static function hasEnoughLight(Block $block, int $minLevel = self::MIN_LIGHT_LEVEL) : bool{
		$position = $block->getPosition();
		$world = $position->getWorld();

		//crop growth is not affected by time of day since 1.11 or so
		return $world->getPotentialLightAt($position->x, $position->y, $position->z) >= $minLevel;
	}

	public static function canGrow(Block $block) : bool{
		//while it may be tempting to use mt_rand(0, 25) < multiplier, this would make crops grow a bit faster than
		//vanilla in most cases due to the remainder of 25 / multiplier not being discarded
		return mt_rand(0, (int) (25 / self::calculateMultiplier($block))) === 0 && self::hasEnoughLight($block);
	}
}
