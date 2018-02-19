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
use pocketmine\math\Vector3;

interface LevelProvider{

	/**
	 * @param string $path
	 */
	public function __construct(string $path);

	/**
	 * Returns the full provider name, like "anvil" or "mcregion", will be used to find the correct format.
	 *
	 * @return string
	 */
	public static function getProviderName() : string;

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
	 * Generate the needed files in the path given
	 *
	 * @param string  $path
	 * @param string  $name
	 * @param int     $seed
	 * @param string  $generator
	 * @param array[] $options
	 */
	public static function generate(string $path, string $name, int $seed, string $generator, array $options = []);

	/**
	 * Returns the generator name
	 *
	 * @return string
	 */
	public function getGenerator() : string;

	/**
	 * @return array
	 */
	public function getGeneratorOptions() : array;

	/**
	 * Saves a chunk (usually to disk).
	 *
	 * @param Chunk $chunk
	 */
	public function saveChunk(Chunk $chunk) : void;

	/**
	 * Loads a chunk (usually from disk storage) and returns it. If the chunk does not exist, null is returned, or an
	 * empty Chunk if $create is specified.
	 *
	 * @param int  $chunkX
	 * @param int  $chunkZ
	 * @param bool $create
	 *
	 * @return null|Chunk
	 */
	public function loadChunk(int $chunkX, int $chunkZ, bool $create = false) : ?Chunk;

	/**
	 * @return string
	 */
	public function getName() : string;

	/**
	 * @return int
	 */
	public function getTime() : int;

	/**
	 * @param int
	 */
	public function setTime(int $value);

	/**
	 * @return int
	 */
	public function getSeed() : int;

	/**
	 * @param int
	 */
	public function setSeed(int $value);

	/**
	 * @return Vector3
	 */
	public function getSpawn() : Vector3;

	/**
	 * @param Vector3 $pos
	 */
	public function setSpawn(Vector3 $pos);

	/**
	 * Returns the world difficulty. This will be one of the Level constants.
	 * @return int
	 */
	public function getDifficulty() : int;

	/**
	 * Sets the world difficulty.
	 * @param int $difficulty
	 */
	public function setDifficulty(int $difficulty);

	/**
	 * Performs garbage collection in the level provider, such as cleaning up regions in Region-based worlds.
	 */
	public function doGarbageCollection();

	/**
	 * Performs cleanups necessary when the level provider is closed and no longer needed.
	 */
	public function close();

}
