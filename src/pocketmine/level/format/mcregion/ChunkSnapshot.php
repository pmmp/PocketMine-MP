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

namespace pocketmine\level\format\mcregion;

use pocketmine\level\format\generic\BaseChunkSnapshot;

class ChunkSnapshot extends BaseChunkSnapshot{

	public function getBlockId($x, $y, $z){
		return ord($this->blockId{
		(($y >> 4) << 12) //get section index
		+ ($y << 8) + ($z << 4) + $x //get block index in section
		});
	}

	public function getBlockData($x, $y, $z){
		$data = ord($this->blockData{
		(($y >> 4) << 11) //get section index
		+ ($y << 7) + ($z << 3) + ($x >> 1) //get block index in section
		});
		if(($y & 1) === 0){
			return $data & 0x0F;
		}else{
			return $data >> 4;
		}
	}

	public function getBlockSkyLight($x, $y, $z){
		$level = ord($this->skyLight{
		(($y >> 4) << 11) //get section index
		+ ($y << 7) + ($z << 3) + ($x >> 1) //get block index in section
		});
		if(($y & 1) === 0){
			return $level & 0x0F;
		}else{
			return $level >> 4;
		}
	}

	public function getBlockLight($x, $y, $z){
		$level = ord($this->light{
		(($y >> 4) << 11) //get section index
		+ ($y << 7) + ($z << 3) + ($x >> 1) //get block index in section
		});
		if(($y & 1) === 0){
			return $level & 0x0F;
		}else{
			return $level >> 4;
		}
	}

	public function getBiome(){
		return 0; //TODO
	}

	public function getHighestBlockAt($x, $z){
		return 127; //TODO
	}
}