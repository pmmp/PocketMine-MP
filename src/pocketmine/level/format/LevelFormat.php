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

use pocketmine\math\Vector3;
use pocketmine\Server;

/**
 * All Level formats must implement this interface
 */
interface LevelFormat{

	/**
	 * @param Server $server
	 * @param string $path
	 */
	public function __construct(Server $server, $path);

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
	 * Gets the Chunk object
	 * This method must be implemented by all the level formats.
	 *
	 * @param int  $X      absolute Chunk X value
	 * @param int  $Z      absolute Chunk Z value
	 * @param bool $create Whether to generate the chunk if it does not exist
	 *
	 * @return ChunkSnapshot
	 */
	public function getChunk($X, $Z, $create = false);

	/**
	 * @return bool
	 */
	public function saveChunks();

	public function unloadChunks();

	public function loadChunk($X, $Z);

	public function unloadChunk($X, $Z);

	public function isChunkLoaded($X, $Z);

	/**
	 * @return Vector3
	 */
	public function getSpawn();

	public function getName();

	/**
	 * @return ChunkSnapshot
	 */
	public function getLoadedChunks();

}