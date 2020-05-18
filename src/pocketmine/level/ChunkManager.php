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

namespace pocketmine\level;

use pocketmine\level\format\Chunk;

interface ChunkManager{
	/**
	 * Gets the raw block id.
	 *
	 * @return int 0-255
	 */
	public function getBlockIdAt(int $x, int $y, int $z) : int;

	/**
	 * Sets the raw block id.
	 *
	 * @param int $id 0-255
	 *
	 * @return void
	 */
	public function setBlockIdAt(int $x, int $y, int $z, int $id);

	/**
	 * Gets the raw block metadata
	 *
	 * @return int 0-15
	 */
	public function getBlockDataAt(int $x, int $y, int $z) : int;

	/**
	 * Sets the raw block metadata.
	 *
	 * @param int $data 0-15
	 *
	 * @return void
	 */
	public function setBlockDataAt(int $x, int $y, int $z, int $data);

	/**
	 * Returns the raw block light level
	 */
	public function getBlockLightAt(int $x, int $y, int $z) : int;

	/**
	 * Sets the raw block light level
	 *
	 * @return void
	 */
	public function setBlockLightAt(int $x, int $y, int $z, int $level);

	/**
	 * Returns the highest amount of sky light can reach the specified coordinates.
	 */
	public function getBlockSkyLightAt(int $x, int $y, int $z) : int;

	/**
	 * Sets the raw block sky light level.
	 *
	 * @return void
	 */
	public function setBlockSkyLightAt(int $x, int $y, int $z, int $level);

	/**
	 * @return Chunk|null
	 */
	public function getChunk(int $chunkX, int $chunkZ);

	/**
	 * @return void
	 */
	public function setChunk(int $chunkX, int $chunkZ, Chunk $chunk = null);

	/**
	 * Gets the level seed
	 */
	public function getSeed() : int;

	/**
	 * Returns the height of the world
	 */
	public function getWorldHeight() : int;

	/**
	 * Returns whether the specified coordinates are within the valid world boundaries, taking world format limitations
	 * into account.
	 */
	public function isInWorld(int $x, int $y, int $z) : bool;
}
