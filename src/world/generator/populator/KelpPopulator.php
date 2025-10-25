<?php

declare(strict_types=1);

namespace pocketmine\world\generator\populator;

use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\data\bedrock\BiomeIds;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\block\Liquid;

class KelpPopulator implements Populator{
	private int $baseAmount = 4; // attempts per chunk

	public function setBaseAmount(int $amount) : void{
		$this->baseAmount = $amount;
	}

	public function populate(ChunkManager $world, int $chunkX, int $chunkZ, Random $random) : void{
		$chunk = $world->getChunk($chunkX, $chunkZ);
		if($chunk === null) return;

		// increase attempts in warm oceans
		for($i = 0; $i < $this->baseAmount; $i++){
			$x = $random->nextBoundedInt(16);
			$z = $random->nextBoundedInt(16);

			$chunkBiome = $chunk->getBiomeId($x, 0, $z);
			$attempts = $this->baseAmount;
			switch($chunkBiome){
				case BiomeIds::WARM_OCEAN:
				case BiomeIds::DEEP_WARM_OCEAN:
					$attempts = max(6, $this->baseAmount);
					break;
				case BiomeIds::LUKEWARM_OCEAN:
				case BiomeIds::DEEP_LUKEWARM_OCEAN:
					$attempts = max(4, $this->baseAmount);
					break;
				case BiomeIds::COLD_OCEAN:
				case BiomeIds::DEEP_COLD_OCEAN:
					$attempts = max(2, $this->baseAmount);
					break;
			}

			for($a = 0; $a < $attempts; $a++){
				for($y = 127; $y > 0; --$y){
					$stateId = $chunk->getBlockStateId($x, $y, $z);
					$b = RuntimeBlockStateRegistry::getInstance()->fromStateId($stateId);
					if($b->isTransparent() === false){
						// must be water to grow kelp here
						if($b instanceof Liquid && $b->canBeFlowedInto()){
							// determine kelp height by depth and randomness
							$maxHeight = 2 + (int)min(6, max(0, (62 - $y) / 4));
							$height = 1 + $random->nextBoundedInt($maxHeight);
							for($h = 0; $h < $height; $h++){
								$py = $y + $h;
								if($py > 127) break;
								$aboveState = RuntimeBlockStateRegistry::getInstance()->fromStateId($chunk->getBlockStateId($x, $py, $z));
								if($aboveState->getTypeId() === \pocketmine\block\BlockTypeIds::AIR || ($aboveState instanceof Liquid && $aboveState->canBeFlowedInto())){
									// place kelp plant block (use KELP() which represents plant)
									$chunk->setBlockStateId($x, $py, $z, VanillaBlocks::KELP()->getStateId());
								}else{
									break;
								}
							}
						}
						break;
					}
				}
			}
		}
	}
}
