<?php

declare(strict_types=1);

namespace pocketmine\world\generator\populator;

use pocketmine\block\VanillaBlocks;
use pocketmine\data\bedrock\BiomeIds;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;

class SeagrassPopulator implements Populator{
	private int $baseAmount = 8; // baseline per chunk

	public function setBaseAmount(int $amount) : void{
		$this->baseAmount = $amount;
	}

	public function populate(ChunkManager $world, int $chunkX, int $chunkZ, Random $random) : void{
		$chunk = $world->getChunk($chunkX, $chunkZ);
		if($chunk === null) return;

		for($i = 0; $i < $this->baseAmount; $i++){
			$x = $random->nextBoundedInt(16);
			$z = $random->nextBoundedInt(16);

			$biomeId = $chunk->getBiomeId($x, 0, $z);
			// skip frozen oceans
			if($biomeId === BiomeIds::FROZEN_OCEAN || $biomeId === BiomeIds::DEEP_FROZEN_OCEAN){
				continue;
			}

			// adjust attempts by biome (warm oceans get more seagrass)
			$attempts = 1;
			switch($biomeId){
				case BiomeIds::WARM_OCEAN:
				case BiomeIds::DEEP_WARM_OCEAN:
					$attempts = 6;
					break;
				case BiomeIds::LUKEWARM_OCEAN:
				case BiomeIds::DEEP_LUKEWARM_OCEAN:
					$attempts = 4;
					break;
				case BiomeIds::COLD_OCEAN:
				case BiomeIds::DEEP_COLD_OCEAN:
					$attempts = 2;
					break;
				default:
					$attempts = 1;
			}

			for($attempt = 0; $attempt < $attempts; $attempt++){
				// find topmost block (top-down) and ensure it's water; place seagrass inside water at that Y
				for($y = 127; $y > 0; --$y){
					$stateId = $chunk->getBlockStateId($x, $y, $z);
					$b = \pocketmine\block\RuntimeBlockStateRegistry::getInstance()->fromStateId($stateId);
					if($b->isTransparent() === false){
						// if it's water, place seagrass at this position (replace water top block with seagrass blockstate if allowed)
						if($b->canBeFlowedInto()){
							// some clients expect seagrass to be a block occupying water; set the current block to seagrass
							$chunk->setBlockStateId($x, $y, $z, VanillaBlocks::SEAGRASS()->getStateId());
						}
						break;
					}
				}
			}
		}
	}
}
