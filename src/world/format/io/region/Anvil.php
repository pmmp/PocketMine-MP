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

namespace pocketmine\world\format\io\region;

use pocketmine\block\BlockLegacyIds;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\format\io\SubChunkConverter;
use pocketmine\world\format\SubChunk;

class Anvil extends RegionWorldProvider{
	use LegacyAnvilChunkTrait;

	protected function serializeSubChunk(SubChunk $subChunk) : CompoundTag{
		throw new \RuntimeException("Unsupported");
	}

	protected function deserializeSubChunk(CompoundTag $subChunk) : SubChunk{
		return new SubChunk(BlockLegacyIds::AIR << 4, [SubChunkConverter::convertSubChunkYZX($subChunk->getByteArray("Blocks"), $subChunk->getByteArray("Data"))]);
		//ignore legacy light information
	}

	protected static function getRegionFileExtension() : string{
		return "mca";
	}

	protected static function getPcWorldFormatVersion() : int{
		return 19133;
	}

	public function getWorldHeight() : int{
		//TODO: add world height options
		return 256;
	}
}
