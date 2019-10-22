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

namespace pocketmine\world\format;

interface SubChunkInterface{

	/**
	 * Returns whether this subchunk contains any non-air blocks.
	 * This function will do a slow check, usually by garbage collecting first.
	 * This is typically useful for disk saving.
	 *
	 * @return bool
	 */
	public function isEmptyAuthoritative() : bool;

	/**
	 * Returns a non-authoritative bool to indicate whether the chunk contains any blocks.
	 * This is a fast check, but may be inaccurate if the chunk has been modified and not garbage-collected.
	 *
	 * @return bool
	 */
	public function isEmptyFast() : bool;

	/**
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 *
	 * @return int
	 */
	public function getFullBlock(int $x, int $y, int $z) : int;

	/**
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 * @param int $block
	 */
	public function setFullBlock(int $x, int $y, int $z, int $block) : void;

	/**
	 * @return PalettedBlockArray[]
	 */
	public function getBlockLayers() : array;

	/**
	 * @param int $x
	 * @param int $z
	 *
	 * @return int
	 */
	public function getHighestBlockAt(int $x, int $z) : int;

	/**
	 * @return LightArray
	 */
	public function getBlockSkyLightArray() : LightArray;

	/**
	 * @param LightArray $data
	 */
	public function setBlockSkyLightArray(LightArray $data) : void;

	/**
	 * @return LightArray
	 */
	public function getBlockLightArray() : LightArray;

	/**
	 * @param LightArray $data
	 */
	public function setBlockLightArray(LightArray $data) : void;
}
