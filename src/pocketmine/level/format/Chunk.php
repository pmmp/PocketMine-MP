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
	const SECTION_COUNT = 8;

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
	 * Modifies $blockId and $meta
	 *
	 * @param int $x 0-15
	 * @param int $y 0-127
	 * @param int $z 0-15
	 * @param int &$blockId
	 * @param int &$meta
	 */
	public function getBlock($x, $y, $z, &$blockId, &$meta = null);

	/**
	 * @param int $x 0-15
	 * @param int $y 0-127
	 * @param int $z 0-15
	 * @param int $blockId, if null, do not change
	 * @param int $meta 0-15, if null, do not change
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
	 * @param int $x 0-15
	 * @param int $y 0-127
	 * @param int $z 0-15
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
	 * @param int $x 0-15
	 * @param int $y 0-127
	 * @param int $z 0-15
	 * @param int $data 0-15
	 */
	public function setBlockData($x, $y, $z, $data);

	/**
	 * @param int $x 0-15
	 * @param int $y 0-127
	 * @param int $z 0-15
	 *
	 * @return int 0-15
	 */
	public function getBlockSkyLight($x, $y, $z);

	/**
	 * @param int $x 0-15
	 * @param int $y 0-127
	 * @param int $z 0-15
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
	 * @param int $x 0-15
	 * @param int $y 0-127
	 * @param int $z 0-15
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
	 * Thread-safe read-only chunk
	 *
	 * @return ChunkSnapshot
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

	/**
	 * Tests whether a section (mini-chunk) is empty
	 *
	 * @param $fY 0-7, (Y / 16)
	 *
	 * @return bool
	 */
	public function isSectionEmpty($fY);

	/**
	 * @param int $fY 0-7
	 *
	 * @return ChunkSection
	 */
	public function getSection($fY);

	/**
	 * @param int          $fY 0-7
	 * @param ChunkSection $section
	 *
	 * @return boolean
	 */
	public function setSection($fY, ChunkSection $section);

}