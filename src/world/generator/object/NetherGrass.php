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

namespace pocketmine\world\generator\object;

use pocketmine\block\BlockTypeIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use function count;

class NetherGrass{
	public static function growGrass(ChunkManager $world, Vector3 $pos, Random $random, int $count = 8, int $radius = 5) : void{
		$nyliumType = $world->getBlockAt($pos->x, $pos->y, $pos->z)->getTypeId();
		$blocksToGrow = [];

		//Todo: Add more blocks to grow on nether nylium

		if ($nyliumType === BlockTypeIds::WARPED_NYLIUM) {
			$blocksToGrow = [
				VanillaBlocks::CRIMSON_ROOTS(), // Rare
				VanillaBlocks::WARPED_ROOTS(),
				//VanillaBlocks::NETHER_SPROUTS(),
				//VanillaBlocks::WARPED_FUNGUS(),
				//VanillaBlocks::CRIMSON_FUNGUS() // Rare
			];
		} elseif ($nyliumType === BlockTypeIds::CRIMSON_NYLIUM) {
			$blocksToGrow = [
				VanillaBlocks::CRIMSON_ROOTS(),
				//VanillaBlocks::CRIMSON_FUNGUS(),
				//VanillaBlocks::WARPED_FUNGUS()
			];
		}

		$blocksCount = count($blocksToGrow) - 1;
		if ($blocksCount >= 0) {
			for ($c = 0; $c < $count; ++$c) {
				$x = $random->nextRange($pos->x - $radius, $pos->x + $radius);
				$z = $random->nextRange($pos->z - $radius, $pos->z + $radius);
				$blockBelow = $world->getBlockAt($x, $pos->y, $z);
				if ($world->getBlockAt($x, $pos->y + 1, $z)->getTypeId() === BlockTypeIds::AIR && $blockBelow->getTypeId() !== BlockTypeIds::AIR) {
					$blockToGrow = $blocksToGrow[$random->nextRange(0, $blocksCount)];
					if ($blockToGrow === VanillaBlocks::CRIMSON_ROOTS()) {
						if ($random->nextFloat() < 0.1) { // 10% chance for rare blocks
							$world->setBlockAt($x, $pos->y + 1, $z, $blockToGrow);
						}
					} else {
						$world->setBlockAt($x, $pos->y + 1, $z, $blockToGrow);
					}
				}
			}
		}
	}
}
