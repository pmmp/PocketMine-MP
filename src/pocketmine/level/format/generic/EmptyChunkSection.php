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

namespace pocketmine\level\format\generic;
use pocketmine\level\format\ChunkSection;

/**
 * Stub used to detect empty chunks
 */
class EmptyChunkSection implements ChunkSection{
	final public function getBlockId($x, $y, $z){
		return 0;
	}

	final public function getBlockIdColumn($x, $z){
		return "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00";
	}

	final public function getBlockDataColumn($x, $z){
		return "\x00\x00\x00\x00\x00\x00\x00\x00";
	}

	final public function setBlockId($x, $y, $z, $id){

	}

	final public function getBlockData($x, $y, $z){
		return 0;
	}

	final public function setBlockData($x, $y, $z, $data){

	}

	final public function getBlockLight($x, $y, $z){
		return 0;
	}

	final public function setBlockLight($x, $y, $z, $level){

	}

	final public function getBlockSkyLight($x, $y, $z){
		return 0;
	}

	final public function setBlockSkyLight($x, $y, $z, $level){

	}
}