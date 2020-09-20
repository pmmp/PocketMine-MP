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
use function max;

class BlockLightUpdate extends LightUpdate{

	/**
	 * @var \SplFixedArray|int[]
	 * @phpstan-var \SplFixedArray<int>
	 */
	private $lightEmitters;

	/**
	 * @param \SplFixedArray|int[] $lightFilters
	 * @param \SplFixedArray|int[] $lightEmitters
	 * @phpstan-param \SplFixedArray<int> $lightFilters
	 * @phpstan-param \SplFixedArray<int> $lightEmitters
	 */
	public function __construct(SubChunkExplorer $subChunkExplorer, \SplFixedArray $lightFilters, \SplFixedArray $lightEmitters){
		parent::__construct($subChunkExplorer, $lightFilters);
		$this->lightEmitters = $lightEmitters;
	}

	protected function updateLightArrayRef() : void{
		$this->currentLightArray = $this->subChunkExplorer->currentSubChunk->getBlockLightArray();
	}

	public function recalculateNode(int $x, int $y, int $z) : void{
		if($this->subChunkExplorer->moveTo($x, $y, $z, false)){
			$block = $this->subChunkExplorer->currentSubChunk->getFullBlock($x & 0xf, $y & 0xf, $z & 0xf);
			$this->setAndUpdateLight($x, $y, $z, max($this->lightEmitters[$block], $this->getHighestAdjacentLight($x, $y, $z) - $this->lightFilters[$block]));
		}
	}
}
