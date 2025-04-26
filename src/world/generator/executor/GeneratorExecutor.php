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

namespace pocketmine\world\generator\executor;

use pocketmine\promise\Promise;
use pocketmine\world\ChunkLoader;
use pocketmine\world\format\Chunk;
use pocketmine\world\World;

/**
 * Decides how and when to invoke the world generator.
 *
 * @phpstan-import-type ChunkPosHash from World
 */
interface GeneratorExecutor{
	/**
	 * Requests generation/population of the target chunk, if it's currently reasonable to do so (and if it isn't
	 * already generated/populated).
	 * The executor may decide not to process this request immediately based on internal conditions (e.g. the number of
	 * concurrently active generation tasks may have reached a limit). If this happens, it will be queued and processed
	 * as soon as possible.
	 *
	 * A ChunkLoader can be associated with the generation request to ensure that the generation request is cancelled if
	 * no loaders are attached to the target chunk. If no loader is provided, one will be assigned (and automatically
	 * removed when the generation request completes).
	 *
	 * @phpstan-return Promise<Chunk>
	 */
	public function requestChunkPopulation(World $world, int $chunkX, int $chunkZ, ?ChunkLoader $associatedChunkLoader) : Promise;

	/**
	 * Initiates generation/population of the target chunk, if it's not already generated/populated.
	 *
	 * This function will begin processing the request immediately, unless any of the adjacent chunks are currently in
	 * use for other generation tasks.
	 *
	 * @see World::isChunkLocked()
	 *
	 * @phpstan-return Promise<Chunk>
	 */
	public function orderChunkPopulation(World $world, int $chunkX, int $chunkZ, ?ChunkLoader $associatedChunkLoader) : Promise;

	/**
	 * Removes the specified chunk from the queue, if population for the chunk hasn't yet been started.
	 * Usually called when a chunk has zero loaders attached and will probably be unloaded, but hasn't yet been.
	 */
	public function cancelChunkPopulation(World $world, int $chunkX, int $chunkZ) : void;

	/**
	 * Removes the specified chunk from the queue, and tells the executor to discard any population results if
	 * population has already been started.
	 * This is typically called on chunk unload.
	 */
	public function abandonChunkPopulation(World $world, int $chunkX, int $chunkZ) : void;

	public function shutdown(World $world) : void;
}
