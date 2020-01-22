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

namespace pocketmine\level\format;

interface SubChunkInterface{

	public function isEmpty(bool $checkLight = true) : bool;

	public function getBlockId(int $x, int $y, int $z) : int;

	public function setBlockId(int $x, int $y, int $z, int $id) : bool;

	public function getBlockData(int $x, int $y, int $z) : int;

	public function setBlockData(int $x, int $y, int $z, int $data) : bool;

	public function getFullBlock(int $x, int $y, int $z) : int;

	public function setBlock(int $x, int $y, int $z, ?int $id = null, ?int $data = null) : bool;

	public function getBlockLight(int $x, int $y, int $z) : int;

	public function setBlockLight(int $x, int $y, int $z, int $level) : bool;

	public function getBlockSkyLight(int $x, int $y, int $z) : int;

	public function setBlockSkyLight(int $x, int $y, int $z, int $level) : bool;

	public function getHighestBlockAt(int $x, int $z) : int;

	public function getBlockIdColumn(int $x, int $z) : string;

	public function getBlockDataColumn(int $x, int $z) : string;

	public function getBlockLightColumn(int $x, int $z) : string;

	public function getBlockSkyLightColumn(int $x, int $z) : string;

	public function getBlockIdArray() : string;

	public function getBlockDataArray() : string;

	public function getBlockSkyLightArray() : string;

	/**
	 * @return void
	 */
	public function setBlockSkyLightArray(string $data);

	public function getBlockLightArray() : string;

	/**
	 * @return void
	 */
	public function setBlockLightArray(string $data);

	public function networkSerialize() : string;
}
