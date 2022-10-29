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

use pocketmine\world\format\io\ChunkData;
use pocketmine\world\format\io\data\JavaWorldData;
use pocketmine\world\format\io\WritableWorldProvider;
use pocketmine\world\WorldCreationOptions;
use Symfony\Component\Filesystem\Path;
use function file_exists;
use function mkdir;

/**
 * This class implements the stuff needed for general region-based world providers to support saving.
 * While this isn't used at the time of writing, it may come in useful if Java 1.13 Anvil support is ever implemented,
 * or for a custom world format based on the region concept.
 */
abstract class WritableRegionWorldProvider extends RegionWorldProvider implements WritableWorldProvider{

	public static function generate(string $path, string $name, WorldCreationOptions $options) : void{
		if(!file_exists($path)){
			mkdir($path, 0777, true);
		}

		$regionPath = Path::join($path, "region");
		if(!file_exists($regionPath)){
			mkdir($regionPath, 0777);
		}

		JavaWorldData::generate($path, $name, $options, static::getPcWorldFormatVersion());
	}

	abstract protected function serializeChunk(ChunkData $chunk) : string;

	public function saveChunk(int $chunkX, int $chunkZ, ChunkData $chunkData) : void{
		self::getRegionIndex($chunkX, $chunkZ, $regionX, $regionZ);
		$this->loadRegion($regionX, $regionZ)->writeChunk($chunkX & 0x1f, $chunkZ & 0x1f, $this->serializeChunk($chunkData));
	}
}
