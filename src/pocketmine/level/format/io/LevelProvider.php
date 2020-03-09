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
use pocketmine\level\generator\Generator;
use pocketmine\math\Vector3;

interface LevelProvider{

	public function __construct(string $path);

	/**
	 * Returns the full provider name, like "anvil" or "mcregion", will be used to find the correct format.
	 */
	public static function getProviderName() : string;

	/**
	 * Gets the build height limit of this world
	 */
	public function getWorldHeight() : int;

	public function getPath() : string;

	/**
	 * Tells if the path is a valid level.
	 * This must tell if the current format supports opening the files in the directory
	 */
	public static function isValid(string $path) : bool;

	/**
	 * Generate the needed files in the path given
	 *
	 * @param mixed[] $options
	 * @phpstan-param class-string<Generator> $generator
	 * @phpstan-param array<string, mixed>    $options
	 *
	 * @return void
	 */
	public static function generate(string $path, string $name, int $seed, string $generator, array $options = []);

	/**
	 * Returns the generator name
	 */
	public function getGenerator() : string;

	/**
	 * @return mixed[]
	 * @phpstan-return array<string, mixed>
	 */
	public function getGeneratorOptions() : array;

	/**
	 * Saves a chunk (usually to disk).
	 */
	public function saveChunk(Chunk $chunk) : void;

	/**
	 * Loads a chunk (usually from disk storage) and returns it. If the chunk does not exist, null is returned.
	 *
	 * @throws CorruptedChunkException
	 * @throws UnsupportedChunkFormatException
	 */
	public function loadChunk(int $chunkX, int $chunkZ) : ?Chunk;

	public function getName() : string;

	public function getTime() : int;

	/**
	 * @return void
	 */
	public function setTime(int $value);

	public function getSeed() : int;

	/**
	 * @return void
	 */
	public function setSeed(int $value);

	public function getSpawn() : Vector3;

	/**
	 * @return void
	 */
	public function setSpawn(Vector3 $pos);

	/**
	 * Returns the world difficulty. This will be one of the Level constants.
	 */
	public function getDifficulty() : int;

	/**
	 * Sets the world difficulty.
	 *
	 * @return void
	 */
	public function setDifficulty(int $difficulty);

	/**
	 * Performs garbage collection in the level provider, such as cleaning up regions in Region-based worlds.
	 *
	 * @return void
	 */
	public function doGarbageCollection();

	/**
	 * Performs cleanups necessary when the level provider is closed and no longer needed.
	 *
	 * @return void
	 */
	public function close();

}
