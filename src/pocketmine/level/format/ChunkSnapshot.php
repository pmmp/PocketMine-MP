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

interface ChunkSnapshot{

	/**
	 * @return int
	 */
	public function getX();

	/**
	 * @return int
	 */
	public function getZ();

	/**
	 * @return string
	 */
	public function getLevelName();

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
	 *
	 * @return int 0-15
	 */
	public function getBlockData($x, $y, $z);

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
	 *
	 * @return int 0-15
	 */
	public function getBlockLight($x, $y, $z);

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
	 * @return int
	 */
	public function getBiome($x, $z);

}