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

namespace pocketmine\world;

use pocketmine\promise\Promise;
use pocketmine\world\format\Chunk;

/**
 * @phpstan-import-type ChunkPosHash from World
 */
interface ChunkGenerator{
	/**
	 * Attempts to initiate asynchronous generation/population of the target chunk, if it's currently reasonable to do
	 * so (and if it isn't already generated/populated).
	 * If the generator is busy, the request will be put into a queue and delayed until a better time.
	 *
	 * A ChunkLoader can be associated with the generation request to ensure that the generation request is cancelled if
	 * no loaders are attached to the target chunk. If no loader is provided, one will be assigned (and automatically
	 * removed when the generation request completes).
	 *
	 * @phpstan-return Promise<Chunk>
	 */
	public function requestChunkPopulation(World $world, int $chunkX, int $chunkZ, ?ChunkLoader $associatedChunkLoader) : Promise;

	/**
	 * Initiates asynchronous generation/population of the target chunk, if it's not already generated/populated.
	 * If generation has already been requested for the target chunk, the promise for the already active request will be
	 * returned directly.
	 *
	 * If the chunk is currently locked (for example due to another chunk using it for async generation), the request
	 * will be queued and executed at the earliest opportunity.
	 *
	 * @phpstan-return Promise<Chunk>
	 */
	public function orderChunkPopulation(World $world, int $chunkX, int $chunkZ, ?ChunkLoader $associatedChunkLoader) : Promise;

	/**
	 * Called when the World needs to cancel a previously-requested population request.
	 * This is typically due to the chunk being unloaded.
	 */
	public function cancelChunkPopulation(World $world, int $chunkX, int $chunkZ) : void;

	public function shutdown(World $world) : void;
}
