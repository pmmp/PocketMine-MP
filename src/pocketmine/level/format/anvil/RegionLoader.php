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
			flock($this->filePointer, LOCK_UN);
			fclose($this->filePointer);
		}
	}

	private function loadLocationTable(){
		fseek($this->filePointer, 0);
		for($i = 0; $i < 1024; ++$i){
			$index = Binary::readInt(fread($this->filePointer, 4));
			$this->locationTable[$i] = array(($index & ~0xff) >> 8, $index & 0xff);
		}
	}

	private function createBlank(){
		fseek($this->filePointer, 0);
		ftruncate($this->filePointer, 0);
		for($i = 0; $i < 1024; ++$i){
			$this->locationTable[$i] = array($i, 1);
			fwrite($this->filePointer, Binary::writeInt(($i << 8) | 1)); //Default: 1 sector per chunk
		}

		$data = str_pad(Binary::writeInt(-1) . chr(self::COMPRESSION_ZLIB), 4096, "\x00", STR_PAD_RIGHT);
		for($i = 0; $i < 1024; ++$i){
			fwrite($this->filePointer, $data);
		}
	}

	public function getX(){
		return $this->x;
	}

	public function getZ(){
		return $this->z;
	}

}