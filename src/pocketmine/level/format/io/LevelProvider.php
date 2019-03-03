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

namespace pocketmine\level\format\io;

use pocketmine\level\format\Chunk;
use pocketmine\level\format\io\exception\CorruptedChunkException;
use pocketmine\level\format\io\exception\UnsupportedChunkFormatException;

interface LevelProvider{

	/**
	 * @param string $path
	 */
	public function __construct(string $path);

	/**
	 * Gets the build height limit of this world
	 *
	 * @return int
	 */
	public function getWorldHeight() : int;

	/**
	 * @return string
	 */
	public function getPath() : string;

	/**
	 * Tells if the path is a valid level.
	 * This must tell if the current format supports opening the files in the directory
	 *
	 * @param string $path
	 *
	 * @return bool
	 */
	public static function isValid(string $path) : bool;

	/**
	 * Loads a chunk (usually from disk storage) and returns it. If the chunk does not exist, null is returned.
	 *
	 * @param int $chunkX
	 * @param int $chunkZ
	 *
	 * @return null|Chunk
	 *
	 * @throws CorruptedChunkException
	 * @throws UnsupportedChunkFormatException
	 */
	public function loadChunk(int $chunkX, int $chunkZ) : ?Chunk;

	/**
	 * Performs garbage collection in the level provider, such as cleaning up regions in Region-based worlds.
	 */
	public function doGarbageCollection() : void;

	/**
	 * Returns information about the world
	 *
	 * @return LevelData
	 */
	public function getLevelData() : LevelData;

	/**
	 * Performs cleanups necessary when the level provider is closed and no longer needed.
	 */
	public function close() : void;

	/**
	 * Returns a generator which yields all the chunks in this level.
	 *
	 * @param bool         $skipCorrupted
	 *
	 * @param \Logger|null $logger
	 *
	 * @return \Generator|Chunk[]
	 * @throws CorruptedChunkException
	 */
	public function getAllChunks(bool $skipCorrupted = false, ?\Logger $logger = null) : \Generator;

	/**
	 * Returns the number of chunks in the provider. Used for world conversion time estimations.
	 *
	 * @return int
	 */
	public function calculateChunkCount() : int;
}
