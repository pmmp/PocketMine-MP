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

namespace pocketmine\level\format\anvil;

use pocketmine\level\format\LevelProvider;

class RegionLoader extends \pocketmine\level\format\mcregion\RegionLoader{

	public function __construct(LevelProvider $level, $regionX, $regionZ){
		$this->x = $regionX;
		$this->z = $regionZ;
		$this->levelProvider = $level;
		$this->filePath = $this->levelProvider->getPath() . "region/r.$regionX.$regionZ.mca";
		$exists = file_exists($this->filePath);
		touch($this->filePath);
		$this->filePointer = fopen($this->filePath, "r+b");
		stream_set_read_buffer($this->filePointer, 1024 * 16); //16KB
		stream_set_write_buffer($this->filePointer, 1024 * 16); //16KB
		if(!$exists){
			$this->createBlank();
		}else{
			$this->loadLocationTable();
		}

		$this->lastUsed = time();
	}

	protected function unserializeChunk($data){
		return Chunk::fromBinary($data, $this->levelProvider);
	}
}