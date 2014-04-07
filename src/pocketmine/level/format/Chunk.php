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

interface Chunk{

	/**
	 * @return int
	 */
	public function getX();

	/**
	 * @return int
	 */
	public function getZ();

	/**
	 * @return \pocketmine\level\Level
	 */
	public function getLevel();


	/**
	 * @param int $x 0-15
	 * @param int $y 0-127
	 * @param int $z 0-15
	 *
	 * @return \pocketmine\block\Block
	 */
	public function getBlock($x, $y, $z);

	/**
	 * Thread-safe read-only chunk
	 *
	 * @return ChunkSnapShot
	 */
	public function getChunkSnapshot();

	/**
	 * @return \pocketmine\entity\Entity[]
	 */
	public function getEntities();

	/**
	 * @return \pocketmine\tile\Tile[]
	 */
	public function getTiles();

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

}