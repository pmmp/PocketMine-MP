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

use pocketmine\block\BlockFactory;
use function max;

class BlockLightUpdate extends LightUpdate{
	protected function updateLightArrayRef() : void{
		$this->currentLightArray = $this->subChunkHandler->currentSubChunk->getBlockLightArray();
	}

	public function recalculateNode(int $x, int $y, int $z) : void{
		$block = $this->world->getBlockAt($x, $y, $z);
		$this->setAndUpdateLight($x, $y, $z, max($block->getLightLevel(), $this->getHighestAdjacentLight($x, $y, $z) - BlockFactory::$lightFilter[$block->getFullId()]));
	}
}
