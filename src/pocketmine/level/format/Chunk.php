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

namespace pocketmine\level\format;

use pocketmine\entity\Entity;
use pocketmine\level\format\generic\SubChunk;
use pocketmine\tile\Tile;

interface Chunk{
	const MAX_SUBCHUNKS = 16;

	const DATA_ORDER_XZY = 0;
	const DATA_ORDER_YZX = 1;

	/**
	 * @return int
	 */
	public function getX() : int;

	/**
	 * @return int
	 */
	public function getZ() : int;

	public function setX(int $x);

	public function setZ(int $z);

	/**
	 * @return LevelProvider|null
	 */
	public function getProvider();

	/**
	 * @param LevelProvider $provider
	 */
	public function setProvider(LevelProvider $provider);

	/**
	 * Returns the chunk height in subchunks
	 *
	 * @return int
	 */
	public function getHeight() : int;

	/**
	 * Gets block and meta in one go
	 *
	 * @param int $x 0-15
	 * @param int $y 0-15
	 * @param int $z 0-15
	 *
	 * @return int bitmap, (id << 4) | data
	 */
	public function getFullBlock(int $x, int $y, int $z) : int;

	/**
	 * @param int $x       0-15
	 * @param int $y       0-255
	 * @param int $z       0-15
	 * @param int $blockId 0-255, if null, do not change
	 * @param int $meta    0-15, if null, do not change
	 *
	 * @return bool
	 */
	public function setBlock(int $x, int $y, int $z, $blockId = null, $meta = null) : bool;

	/**
	 * @param int $x 0-15
	 * @param int $y 0-255
	 * @param int $z 0-15
	 *
	 * @return int 0-255
	 */
	public function getBlockId(int $x, int $y, int $z) : int;

	/**
	 * @param int $x  0-15
	 * @param int $y  0-255
	 * @param int $z  0-15
	 * @param int $id 0-255
	 */
	public function setBlockId(int $x, int $y, int $z, int $id);

	/**
	 * @param int $x 0-15
	 * @param int $y 0-255
	 * @param int $z 0-15
	 *
	 * @return int 0-15
	 */
	public function getBlockData(int $x, int $y, int $z) : int;

	/**
	 * @param int $x    0-15
	 * @param int $y    0-255
	 * @param int $z    0-15
	 * @param int $data 0-15
	 */
	public function setBlockData(int $x, int $y, int $z, int $data);

	/**
	 * @param int $x 0-15
	 * @param int $y 0-255
	 * @param int $z 0-15
	 *
	 * @return int (16-bit)
	 */
	public function getBlockExtraData(int $x, int $y, int $z) : int;

	/**
	 * @param int $x    0-15
	 * @param int $y    0-255
	 * @param int $z    0-15
	 * @param int $data (16-bit)
	 */
	public function setBlockExtraData(int $x, int $y, int $z, int $data);

	/**
	 * @param int $x 0-15
	 * @param int $y 0-255
	 * @param int $z 0-15
	 *
	 * @return int 0-15
	 */
	public function getBlockSkyLight(int $x, int $y, int $z) : int;

	/**
	 * @param int $x     0-15
	 * @param int $y     0-255
	 * @param int $z     0-15
	 * @param int $level 0-15
	 */
	public function setBlockSkyLight(int $x, int $y, int $z, int $level);

	/**
	 * @param int $x 0-15
	 * @param int $y 0-255
	 * @param int $z 0-15
	 *
	 * @return int 0-15
	 */
	public function getBlockLight(int $x, int $y, int $z) : int;

	/**
	 * @param int $x     0-15
	 * @param int $y     0-255
	 * @param int $z     0-15
	 * @param int $level 0-15
	 */
	public function setBlockLight(int $x, int $y, int $z, int $level);

	/**
	 * @param int $x 0-15
	 * @param int $z 0-15
	 *
	 * @return int 0-255
	 */
	public function getHighestBlockAt(int $x, int $z) : int;

	/**
	 * @param int $x 0-15
	 * @param int $z 0-15
	 *
	 * @return int 0-255
	 */
	public function getHeightMap(int $x, int $z) : int;

	/**
	 * @param int $x     0-15
	 * @param int $z     0-15
	 * @param     $value 0-255
	 */
	public function setHeightMap(int $x, int $z, int $value);

	public function recalculateHeightMap();

	public function populateSkyLight();

	/**
	 * @param int $x 0-15
	 * @param int $z 0-15
	 *
	 * @return int 0-255
	 */
	public function getBiomeId(int $x, int $z) : int;

	/**
	 * @param int $x       0-15
	 * @param int $z       0-15
	 * @param int $biomeId 0-255
	 */
	public function setBiomeId(int $x, int $z, int $biomeId);

	public function getBlockIdColumn(int $x, int $z) : string;

	public function getBlockDataColumn(int $x, int $z) : string;

	public function getBlockSkyLightColumn(int $x, int $z) : string;

	public function getBlockLightColumn(int $x, int $z) : string;

	public function isLightPopulated() : bool;

	public function setLightPopulated(bool $value = true);

	public function isPopulated() : bool;

	public function setPopulated(bool $value = true);

	public function isGenerated() : bool;

	public function setGenerated(bool $value = true);

	/**
	 * @param Entity $entity
	 */
	public function addEntity(Entity $entity);

	/**
	 * @param Entity $entity
	 */
	public function removeEntity(Entity $entity);

	/**
	 * @param Tile $tile
	 */
	public function addTile(Tile $tile);

	/**
	 * @param Tile $tile
	 */
	public function removeTile(Tile $tile);

	/**
	 * @return Entity[]
	 */
	public function getEntities() : array;

	/**
	 * @return Tile[]
	 */
	public function getTiles() : array;

	/**
	 * @param int $x 0-15
	 * @param int $y 0-255
	 * @param int $z 0-15
	 */
	public function getTile(int $x, int $y, int $z);

	/**
	 * @return bool
	 */
	public function isLoaded() : bool;

	/**
	 * Loads the chunk
	 *
	 * @param bool $generate If the chunk does not exist, generate it
	 *
	 * @return bool
	 */
	public function load(bool $generate = true) : bool;

	/**
	 * @param bool $save
	 * @param bool $safe If false, unload the chunk even if players are nearby
	 *
	 * @return bool
	 */
	public function unload(bool $save = true, bool $safe = true) : bool;

	public function initChunk();

	/**
	 * @return string
	 */
	public function getBiomeIdArray() : string;

	/**
	 * @return int[]
	 */
	public function getHeightMapArray() : array;

	public function getBlockExtraDataArray() : array;

	/**
	 * @return bool
	 */
	public function hasChanged() : bool;

	/**
	 * @param bool $changed
	 */
	public function setChanged(bool $changed = true);

	/**
	 * @param int  $fY 0-15
	 * @param bool $generateNew will set and return a modifiable subchunk
	 *
	 * @return SubChunk
	 */
	public function getSubChunk(int $fY, bool $generateNew = false) : SubChunk;

	/**
	 * @param int      $fY 0-15
	 * @param SubChunk $subChunk
	 * @param bool     $allowEmpty
	 *
	 * @return bool
	 */
	public function setSubChunk(int $fY, SubChunk $subChunk = null, bool $allowEmpty = false) : bool;

	/**
	 * @return SubChunk[]
	 */
	public function getSubChunks() : array;

	/**
	 * Returns the index of the highest non-empty subchunk
	 *
	 * @return int
	 */
	public function getHighestSubChunkIndex() : int;

	/**
	 * Returns the number of subchunks that need sending
	 *
	 * @return int
	 */
	public function getSubChunkSendCount() : int;

	/**
	 * Disposes of empty subchunks
	 */
	public function pruneEmptySubChunks();

	/**
	 * Serializes the chunk to network data
	 *
	 * @return string
	 */
	public function networkSerialize() : string;

	/**
	 * Serializes the chunk without compression for use in AsyncTasks.
	 *
	 * @return string
	 */
	public function fastSerialize() : string;

	/**
	 * Deserializes a chunk from fast serialization
	 *
	 * @param string        $data
	 * @param LevelProvider $provider
	 *
	 * @return Chunk|null
	 */
	public static function fastDeserialize(string $data, LevelProvider $provider = null);

	/**
	 * Creates and returns an empty chunk
	 *
	 * @param int           $chunkX
	 * @param int           $chunkZ
	 * @param LevelProvider $provider
	 *
	 * @return Chunk
	 */
	public static function getEmptyChunk(int $chunkX, int $chunkZ, LevelProvider $provider = null) : Chunk;

}