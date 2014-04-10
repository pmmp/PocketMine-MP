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

use pocketmine\utils\Binary;

class RegionLoader{
	const COMPRESSION_GZIP = 1;
	const COMPRESSION_ZLIB = 2;

	protected $x;
	protected $z;
	protected $filePath;
	protected $filePointer;
	protected $locationTable = array();

	public function __construct($path, $regionX, $regionZ){
		$this->filePath = $path . "r.$regionX.$regionZ.mca";
		touch($this->filePath);
		$this->filePointer = fopen($this->filePath, "r+b");
		flock($this->filePointer, LOCK_EX);
		stream_set_read_buffer($this->filePointer, 4096);
		stream_set_write_buffer($this->filePointer, 1024 * 16); //16KB
		if(!file_exists($this->filePath)){
			$this->createBlank();
		}else{
			$this->loadLocationTable();
		}
	}

	public function __destruct(){
		if(is_resource($this->filePointer)){
			$this->cleanGarbage();
			$this->writeLocationTable();
			flock($this->filePointer, LOCK_UN);
			fclose($this->filePointer);
		}
	}

	private function cleanGarbage(){
		$sectors = array();
		foreach($this->locationTable as $index => $data){ //Calculate file usage
			if($data[0] === 0 or $data[1] === 0){
				$this->locationTable[$index] = array(0, 0);
				continue;
			}
			for($i = 0; $i < $data[1]; ++$i){
				$sectors[$data[0]] = $index;
			}
		}

		ksort($sectors);
		$shift = 0;
		$lastSector = 1; //First chunk - 1

		fseek($this->filePointer, 8192);
		$sector = 2;
		foreach($sectors as $sector => $index){
			if(($sector - $lastSector) > 1){
				$shift += $sector - $lastSector - 1;
			}
			if($shift > 0){
				fseek($this->filePointer, $sector << 12);
				$old = fread($this->filePointer, 4096);
				fseek($this->filePointer, ($sector - $shift) << 12);
				fwrite($this->filePointer, $old, 4096);
			}
			$this->locationTable[$index][0] -= $shift;
		}
		ftruncate($this->filePointer, ($sector + 1) << 12); //Truncate to the end of file written

		return $shift;
	}

	private function loadLocationTable(){
		fseek($this->filePointer, 0);
		for($i = 0; $i < 1024; ++$i){
			$index = Binary::readInt(fread($this->filePointer, 4));
			$this->locationTable[$i] = array(($index & ~0xff) >> 8, $index & 0xff);
		}
	}

	private function writeLocationTable(){
		fseek($this->filePointer, 0);
		for($i = 0; $i < 1024; ++$i){
			fwrite($this->filePointer, Binary::writeInt(($this->locationTable[$i][0] << 8) | $this->locationTable[$i][1]));
		}
	}

	private function writeLocationIndex($index){
		fseek($this->filePointer, $index << 2);
		fwrite($this->filePointer, Binary::writeInt(($this->locationTable[$index][0] << 8) | $this->locationTable[$index][1]));
	}

	private function createBlank(){
		fseek($this->filePointer, 0);
		ftruncate($this->filePointer, 0);
		for($i = 0; $i < 1024; ++$i){
			$this->locationTable[$i] = array(0, 0);
			fwrite($this->filePointer, Binary::writeInt(0));
		}
		for($i = 0; $i < 1024; ++$i){
			fwrite($this->filePointer, Binary::writeInt(0));
		}
	}

	public function getX(){
		return $this->x;
	}

	public function getZ(){
		return $this->z;
	}

}