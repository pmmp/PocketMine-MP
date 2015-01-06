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

use pocketmine\level\Level;
use pocketmine\math\Vector3;

interface LevelProvider{

	const ORDER_YZX = 0;
	const ORDER_ZXY = 1;

	/**
	 * @param Level  $level
	 * @param string $path
	 */
	public function __construct(Level $level, $path);

	/**
	 * Returns the full provider name, like "anvil" or "mcregion", will be used to find the correct format.
	 *
	 * @return string
	 */
	public static function getProviderName();

	/**
	 * @return int
	 */
	public static function getProviderOrder();

	/**
	 * @return bool
	 */
	public static function usesChunkSection();

	/**
	 * Requests a MC: PE network chunk to be sent
	 *
	 * @param int $x
	 * @param int $z
	 *
	 * @return \pocketmine\scheduler\AsyncTask|null
	 */
	public function requestChunkTask($x, $z);

	/** @return string */
	public function getPath();

	/**
	 * Tells if the path is a valid level.
	 * This must tell if the current format supports opening the files in the directory
	 *
	 * @param string $path
	 *
	 * @return true
	 */
	public static function isValid($path);

	/**
	 * Generate the needed files in the path given
	 *
	 * @param string  $path
	 * @param string  $name
	 * @param int     $seed
	 * @param string  $generator
	 * @param array[] $options
	 */
	public static function generate($path, $name, $seed, $generator, array $options = []);

	/**
	 * Returns the generator name
	 *
	 * @return string
	 */
	public function getGenerator();

	/**
	 * @return array
	 */
	public function getGeneratorOptions();

	/**
	 * Gets the Chunk object
	 * This method must be implemented by all the level formats.
	 *
	 * @param int  $X      absolute Chunk X value
	 * @param int  $Z      absolute Chunk Z value
	 * @param bool $create Whether to generate the chunk if it does not exist
	 *
	 * @return FullChunk|Chunk
	 */
	public function getChunk($X, $Z, $create = false);

	/**
	 * @param $Y 0-7
	 *
	 * @return ChunkSection
	 */
	public static function createChunkSection($Y);

	public function saveChunks();

	/**
	 * @param int $X
	 * @param int $Z
	 */
	public function saveChunk($X, $Z);

	public function unloadChunks();

	/**
	 * @param int  $X
	 * @param int  $Z
	 * @param bool $create
	 *
	 * @return bool
	 */
	public function loadChunk($X, $Z, $create = false);

	/**
	 * @param int  $X
	 * @param int  $Z
	 * @param bool $safe
	 *
	 * @return bool
	 */
	public function unloadChunk($X, $Z, $safe = true);

	/**
	 * @param int $X
	 * @param int $Z
	 *
	 * @return bool
	 */
	public function isChunkGenerated($X, $Z);

	/**
	 * @param int $X
	 * @param int $Z
	 *
	 * @return bool
	 */
	public function isChunkPopulated($X, $Z);

	/**
	 * @param int $X
	 * @param int $Z
	 *
	 * @return bool
	 */
	public function isChunkLoaded($X, $Z);

	/**
	 * @param int       $chunkX
	 * @param int       $chunkZ
	 * @param FullChunk $chunk
	 *
	 * @return mixed
	 */
	public function setChunk($chunkX, $chunkZ, FullChunk $chunk);

	/**
	 * @return string
	 */
	public function getName();

	/**
	 * @return int
	 */
	public function getTime();

	/**
	 * @param int $value
	 */
	public function setTime($value);

	/**
	 * @return int
	 */
	public function getSeed();

	/**
	 * @param int $value
	 */
	public function setSeed($value);

	/**
	 * @return Vector3
	 */
	public function getSpawn();

	/**
	 * @param Vector3 $pos
	 */
	public function setSpawn(Vector3 $pos);

	/**
	 * @return FullChunk|Chunk[]
	 */
	public function getLoadedChunks();

	public function doGarbageCollection();

	/**
	 * @return Level
	 */
	public function getLevel();

	public function close();

}