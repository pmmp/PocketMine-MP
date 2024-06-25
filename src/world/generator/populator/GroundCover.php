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

use pocketmine\block\BlockTypeIds;
use pocketmine\block\Liquid;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\utils\Random;
use pocketmine\world\biome\BiomeRegistry;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use function count;
use function min;

class GroundCover implements Populator{

	public function populate(ChunkManager $world, int $chunkX, int $chunkZ, Random $random) : void{
		$chunk = $world->getChunk($chunkX, $chunkZ) ?? throw new \InvalidArgumentException("Chunk $chunkX $chunkZ does not yet exist");
		$factory = RuntimeBlockStateRegistry::getInstance();
		$biomeRegistry = BiomeRegistry::getInstance();
		for($x = 0; $x < Chunk::EDGE_LENGTH; ++$x){
			for($z = 0; $z < Chunk::EDGE_LENGTH; ++$z){
				$biome = $biomeRegistry->getBiome($chunk->getBiomeId($x, 0, $z));
				$cover = $biome->getGroundCover();
				if(count($cover) > 0){
					$diffY = 0;
					if(!$cover[0]->isSolid()){
						$diffY = 1;
					}

					$startY = 127;
					for(; $startY > 0; --$startY){
						if(!$factory->fromStateId($chunk->getBlockStateId($x, $startY, $z))->isTransparent()){
							break;
						}
					}
					$startY = min(127, $startY + $diffY);
					$endY = $startY - count($cover);
					for($y = $startY; $y > $endY && $y >= 0; --$y){
						$b = $cover[$startY - $y];
						$id = $factory->fromStateId($chunk->getBlockStateId($x, $y, $z));
						if($id->getTypeId() === BlockTypeIds::AIR && $b->isSolid()){
							break;
						}
						if($b->canBeFlowedInto() && $id instanceof Liquid){
							continue;
						}

						$chunk->setBlockStateId($x, $y, $z, $b->getStateId());
					}
				}
			}
		}
	}
}
