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

use pocketmine\world\utils\SubChunkExplorer;
use pocketmine\world\World;
use function max;

class SkyLightUpdate extends LightUpdate{

	/**
	 * @var \SplFixedArray|bool[]
	 * @phpstan-var \SplFixedArray<bool>
	 */
	private $directSkyLightBlockers;

	/**
	 * @param \SplFixedArray|int[]  $lightFilters
	 * @param \SplFixedArray|bool[] $directSkyLightBlockers
	 * @phpstan-param \SplFixedArray<int>  $lightFilters
	 * @phpstan-param \SplFixedArray<bool> $directSkyLightBlockers
	 */
	public function __construct(SubChunkExplorer $subChunkExplorer, \SplFixedArray $lightFilters, \SplFixedArray $directSkyLightBlockers){
		parent::__construct($subChunkExplorer, $lightFilters);
		$this->directSkyLightBlockers = $directSkyLightBlockers;
	}

	protected function updateLightArrayRef() : void{
		$this->currentLightArray = $this->subChunkExplorer->currentSubChunk->getBlockSkyLightArray();
	}

	protected function getEffectiveLight(int $x, int $y, int $z) : int{
		if($y >= World::Y_MAX){
			$this->subChunkExplorer->invalidate();
			return 15;
		}
		return parent::getEffectiveLight($x, $y, $z);
	}

	public function recalculateNode(int $x, int $y, int $z) : void{
		if(!$this->subChunkExplorer->moveTo($x, $y, $z, false)){
			return;
		}
		$chunk = $this->subChunkExplorer->currentChunk;

		$oldHeightMap = $chunk->getHeightMap($x & 0xf, $z & 0xf);
		$source = $this->subChunkExplorer->currentSubChunk->getFullBlock($x & 0xf, $y & 0xf, $z & 0xf);

		$yPlusOne = $y + 1;

		if($yPlusOne === $oldHeightMap){ //Block changed directly beneath the heightmap. Check if a block was removed or changed to a different light-filter.
			$newHeightMap = $chunk->recalculateHeightMapColumn($x & 0x0f, $z & 0x0f, $this->directSkyLightBlockers);
		}elseif($yPlusOne > $oldHeightMap){ //Block changed above the heightmap.
			if($this->directSkyLightBlockers[$source]){
				$chunk->setHeightMap($x & 0xf, $z & 0xf, $yPlusOne);
				$newHeightMap = $yPlusOne;
			}else{ //Block changed which has no effect on direct sky light, for example placing or removing glass.
				return;
			}
		}else{ //Block changed below heightmap
			$newHeightMap = $oldHeightMap;
		}

		if($newHeightMap > $oldHeightMap){ //Heightmap increase, block placed, remove sky light
			for($i = $y; $i >= $oldHeightMap; --$i){
				$this->setAndUpdateLight($x, $i, $z, 0); //Remove all light beneath, adjacent recalculation will handle the rest.
			}
		}elseif($newHeightMap < $oldHeightMap){ //Heightmap decrease, block changed or removed, add sky light
			for($i = $y; $i >= $newHeightMap; --$i){
				$this->setAndUpdateLight($x, $i, $z, 15);
			}
		}else{ //No heightmap change, block changed "underground"
			$this->setAndUpdateLight($x, $y, $z, max(0, $this->getHighestAdjacentLight($x, $y, $z) - $this->lightFilters[$source]));
		}
	}
}
