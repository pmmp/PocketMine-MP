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

namespace pocketmine\world\generator\populator;

use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\Liquid;
use pocketmine\utils\Random;
use pocketmine\world\biome\Biome;
use pocketmine\world\ChunkManager;
use function count;
use function min;

class GroundCover extends Populator{

	public function populate(ChunkManager $world, int $chunkX, int $chunkZ, Random $random) : void{
		$chunk = $world->getChunk($chunkX, $chunkZ);
		$factory = BlockFactory::getInstance();
		for($x = 0; $x < 16; ++$x){
			for($z = 0; $z < 16; ++$z){
				$biome = Biome::getBiome($chunk->getBiomeId($x, $z));
				$cover = $biome->getGroundCover();
				if(count($cover) > 0){
					$diffY = 0;
					if(!$cover[0]->isSolid()){
						$diffY = 1;
					}

					$startY = 127;
					for(; $startY > 0; --$startY){
						if(!$factory->fromFullBlock($chunk->getFullBlock($x, $startY, $z))->isTransparent()){
							break;
						}
					}
					$startY = min(127, $startY + $diffY);
					$endY = $startY - count($cover);
					for($y = $startY; $y > $endY and $y >= 0; --$y){
						$b = $cover[$startY - $y];
						$id = $factory->fromFullBlock($chunk->getFullBlock($x, $y, $z));
						if($id->getId() === BlockLegacyIds::AIR and $b->isSolid()){
							break;
						}
						if($b->canBeFlowedInto() and $id instanceof Liquid){
							continue;
						}

						$chunk->setFullBlock($x, $y, $z, $b->getFullId());
					}
				}
			}
		}
	}
}
