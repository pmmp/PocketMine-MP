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
use pocketmine\tile\Tile;

interface FullChunk{

	/**
	 * @return int
	 */
	public function getX();

	/**
	 * @return int
	 */
	public function getZ();

	public function setX($x);

	public function setZ($z);

	/**
	 * @return LevelProvider
	 */
	public function getProvider();

	/**
	 * @param LevelProvider $provider
	 */
	public function setProvider(LevelProvider $provider);

	/**
	 * Gets block and meta in one go
	 *
	 * @param int $x 0-15
	 * @param int $y 0-15
	 * @param int $z 0-15
	 *
	 * @return int bitmap, (id << 4) | data
	 */
	public function getFullBlock($x, $y, $z);

	/**
	 * @param int $x       0-15
	 * @param int $y       0-127
	 * @param int $z       0-15
	 * @param int $blockId , if null, do not change
	 * @param int $meta    0-15, if null, do not change
	 *
	 */
	public function setBlock($x, $y, $z, $blockId = null, $meta = null);

	/**
	 * @param int $x 0-15
	 * @param int $y 0-127
	 * @param int $z 0-15
	 *
	 * @return int 0-255
	 */
	public function getBlockId($x, $y, $z);

	/**
	 * @param int $x  0-15
	 * @param int $y  0-127
	 * @param int $z  0-15
	 * @param int $id 0-255
	 */
	public function setBlockId($x, $y, $z, $id);

	/**
	 * @param int $x 0-15
	 * @param int $y 0-127
	 * @param int $z 0-15
	 *
	 * @return int 0-15
	 */
	public function getBlockData($x, $y, $z);

	/**
	 * @param int $x    0-15
	 * @param int $y    0-127
	 * @param int $z    0-15
	 * @param int $data 0-15
	 */
	public function setBlockData($x, $y, $z, $data);

	/**
	 * @param int $x 0-15
	 * @param int $y 0-127
	 * @param int $z 0-15
	 *
	 * @return int (16-bit)
	 */
	public function getBlockExtraData($x, $y, $z);

	/**
	 * @param int $x    0-15
	 * @param int $y    0-127
	 * @param int $z    0-15
	 * @param int $data (16-bit)
	 */
	public function setBlockExtraData($x, $y, $z, $data);

	/**
	 * @param int $x 0-15
	 * @param int $y 0-127
	 * @param int $z 0-15
	 *
	 * @return int 0-15
	 */
	public function getBlockSkyLight($x, $y, $z);

	/**
	 * @param int $x     0-15
	 * @param int $y     0-127
	 * @param int $z     0-15
	 * @param int $level 0-15
	 */
	public function setBlockSkyLight($x, $y, $z, $level);

	/**
	 * @param int $x 0-15
	 * @param int $y 0-127
	 * @param int $z 0-15
	 *
	 * @return int 0-15
	 */
	public function getBlockLight($x, $y, $z);

	/**
	 * @param int $x     0-15
	 * @param int $y     0-127
	 * @param int $z     0-15
	 * @param int $level 0-15
	 */
	public function setBlockLight($x, $y, $z, $level);

	/**
	 * @param int $x 0-15
	 * @param int $z 0-15
	 *
	 * @return int 0-127
	 */
	public function getHighestBlockAt($x, $z);

	/**
	 * @param int $x 0-15
	 * @param int $z 0-15
	 *
	 * @return int 0-255
	 */
	public function getHeightMap($x, $z);

	/**
	 * @param int $x 0-15
	 * @param int $z 0-15
	 * @param $value 0-255
	 */
	public function setHeightMap($x, $z, $value);

	public function recalculateHeightMap();

	public function populateSkyLight();

	/**
	 * @param int $x 0-15
	 * @param int $z 0-15
	 *
	 * @return int 0-255
	 */
	public function getBiomeId($x, $z);

	/**
	 * @param int $x       0-15
	 * @param int $z       0-15
	 * @param int $biomeId 0-255
	 */
	public function setBiomeId($x, $z, $biomeId);

	/**
	 * @param int $x
	 * @param int $z
	 *
	 * @return int[] RGB bytes
	 */
	public function getBiomeColor($x, $z);

	public function getBlockIdColumn($x, $z);

	public function getBlockDataColumn($x, $z);

	public function getBlockSkyLightColumn($x, $z);

	public function getBlockLightColumn($x, $z);

	/**
	 * @param int $x 0-15
	 * @param int $z 0-15
	 * @param int $R 0-255
	 * @param int $G 0-255
	 * @param int $B 0-255
	 */
	public function setBiomeColor($x, $z, $R, $G, $B);

	public function isLightPopulated();

	public function setLightPopulated($value = 1);

	public function isPopulated();

	public function setPopulated($value = 1);

	public function isGenerated();

	public function setGenerated($value = 1);

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
	 * @return \pocketmine\entity\Entity[]
	 */
	public function getEntities();

	/**
	 * @return \pocketmine\tile\Tile[]
	 */
	public function getTiles();

	/**
	 * @param int $x 0-15
	 * @param int $y 0-127
	 * @param int $z 0-15
	 */
	public function getTile($x, $y, $z);

	/**
	 * @return bool
	 */
	public function isLoaded();

	/**
	 * Loads the chunk
	 *
	 * @param bool $generate If the chunk does not exist, generate it
	 *
	 * @return bool
	 */
	public function load($generate = true);

	/**
	 * @param bool $save
	 * @param bool $safe If false, unload the chunk even if players are nearby
	 *
	 * @return bool
	 */
	public function unload($save = true, $safe = true);

	public function initChunk();

	/**
	 * @return string[]
	 */
	public function getBiomeIdArray();

	/**
	 * @return int[]
	 */
	public function getBiomeColorArray();

	/**
	 * @return int[]
	 */
	public function getHeightMapArray();

	public function getBlockIdArray();

	public function getBlockDataArray();

	public function getBlockExtraDataArray();

	public function getBlockSkyLightArray();

	public function getBlockLightArray();

	public function toBinary();

	public function toFastBinary();

	/**
	 * @return boolean
	 */
	public function hasChanged();

	/**
	 * @param bool $changed
	 */
	public function setChanged($changed = true);

	/**
	 * @param string        $data
	 * @param LevelProvider $provider
	 *
	 * @return FullChunk
	 */
	public static function fromBinary($data, LevelProvider $provider = null);

	/**
	 * @param string        $data
	 * @param LevelProvider $provider
	 *
	 * @return FullChunk
	 */
	public static function fromFastBinary($data, LevelProvider $provider = null);

	/**
	 * @param int           $chunkX
	 * @param int           $chunkZ
	 * @param LevelProvider $provider
	 *
	 * @return FullChunk
	 */
	public static function getEmptyChunk($chunkX, $chunkZ, LevelProvider $provider = null);

}