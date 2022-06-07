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

namespace pocketmine\world\light;

use pocketmine\world\format\LightArray;
use pocketmine\world\format\SubChunk;
use pocketmine\world\utils\SubChunkExplorer;
use pocketmine\world\utils\SubChunkExplorerStatus;
use function max;

class BlockLightUpdate extends LightUpdate{
	/**
	 * @param \SplFixedArray|int[] $lightFilters
	 * @param \SplFixedArray|int[] $lightEmitters
	 * @phpstan-param \SplFixedArray<int> $lightFilters
	 * @phpstan-param \SplFixedArray<int> $lightEmitters
	 */
	public function __construct(
		SubChunkExplorer $subChunkExplorer,
		\SplFixedArray $lightFilters,
		private \SplFixedArray $lightEmitters
	){
		parent::__construct($subChunkExplorer, $lightFilters);
	}

	protected function getCurrentLightArray() : LightArray{
		return $this->subChunkExplorer->currentSubChunk->getBlockLightArray();
	}

	public function recalculateNode(int $x, int $y, int $z) : void{
		if($this->subChunkExplorer->moveTo($x, $y, $z) !== SubChunkExplorerStatus::INVALID){
			$block = $this->subChunkExplorer->currentSubChunk->getFullBlock($x & SubChunk::COORD_MASK, $y & SubChunk::COORD_MASK, $z & SubChunk::COORD_MASK);
			$this->setAndUpdateLight($x, $y, $z, max($this->lightEmitters[$block], $this->getHighestAdjacentLight($x, $y, $z) - $this->lightFilters[$block]));
		}
	}

	public function recalculateChunk(int $chunkX, int $chunkZ) : int{
		if($this->subChunkExplorer->moveToChunk($chunkX, 0, $chunkZ) === SubChunkExplorerStatus::INVALID){
			throw new \InvalidArgumentException("Chunk $chunkX $chunkZ does not exist");
		}
		$chunk = $this->subChunkExplorer->currentChunk;

		$lightSources = 0;
		foreach($chunk->getSubChunks() as $subChunkY => $subChunk){
			$subChunk->setBlockLightArray(LightArray::fill(0));

			foreach($subChunk->getBlockLayers() as $layer){
				foreach($layer->getPalette() as $state){
					if($this->lightEmitters[$state] > 0){
						$lightSources += $this->scanForLightEmittingBlocks($subChunk, $chunkX << SubChunk::COORD_BIT_SIZE, $subChunkY << SubChunk::COORD_BIT_SIZE, $chunkZ << SubChunk::COORD_BIT_SIZE);
						break 2;
					}
				}
			}
		}

		return $lightSources;
	}

	private function scanForLightEmittingBlocks(SubChunk $subChunk, int $baseX, int $baseY, int $baseZ) : int{
		$lightSources = 0;
		for($x = 0; $x < SubChunk::EDGE_LENGTH; ++$x){
			for($z = 0; $z < SubChunk::EDGE_LENGTH; ++$z){
				for($y = 0; $y < SubChunk::EDGE_LENGTH; ++$y){
					$light = $this->lightEmitters[$subChunk->getFullBlock($x, $y, $z)];
					if($light > 0){
						$this->setAndUpdateLight(
							$baseX + $x,
							$baseY + $y,
							$baseZ + $z,
							$light
						);
						$lightSources++;
					}
				}
			}
		}
		return $lightSources;
	}
}
