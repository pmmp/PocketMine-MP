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

use pocketmine\world\Position;
use function mt_rand;

class PaleMossBlock extends MossBlock{

	public function getFlameEncouragement() : int{
		return 15;
	}

	public function getFlammability() : int{
		return 100;
	}

	protected function generateVegetation(Position $pos) : void{
		$world = $pos->getWorld();
		if(!$world->isInWorld($pos->x, $pos->y, $pos->z)){
			return;
		}
		$rand = mt_rand(1, 10000);
		if($rand <= 5882){
			$world->setBlock($pos,VanillaBlocks::TALL_GRASS());
		}elseif($rand <= 5882 + 2941){
			$world->setBlock($pos, VanillaBlocks::PALE_MOSS_CARPET());
		}else{
			if($world->isInWorld($pos->x, $pos->y + 1, $pos->z)){
				$world->setBlock($pos, VanillaBlocks::DOUBLE_TALLGRASS());
				$world->setBlock($pos->up(), VanillaBlocks::DOUBLE_TALLGRASS()->setTop(true));
			}
		}
	}
}
