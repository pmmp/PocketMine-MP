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

namespace pocketmine\level\format\io\region;

use pocketmine\level\format\ChunkException;
use pocketmine\level\format\io\exception\CorruptedChunkException;
use pocketmine\utils\Binary;
use pocketmine\utils\MainLogger;

class RegionLoader{
	public const VERSION = 1;
	public const COMPRESSION_GZIP = 1;
	public const COMPRESSION_ZLIB = 2;

	public const MAX_SECTOR_LENGTH = 256 << 12; //256 sectors, (1 MiB)
	public const REGION_HEADER_LENGTH = 8192; //4096 location table + 4096 timestamps
	public const MAX_REGION_FILE_SIZE = 32 * 32 * self::MAX_SECTOR_LENGTH + self::REGION_HEADER_LENGTH; //32 * 32 1MiB chunks + header size

	public static $COMPRESSION_LEVEL = 7;

	/** @var int */
	protected $x;
	/** @var int */
	protected $z;
	/** @var string */
	protected $filePath;
	/** @var resource */
	protected $filePointer;
	/** @var int */
	protected $lastSector;
	/** @var int[][] [offset in sectors, chunk size in sectors, timestamp] */
	protected $locationTable = [];
	/** @var int */
	public $lastUsed = 0;

	public function __construct(string $filePath, int $regionX, int $regionZ){
		$this->x = $regionX;
		$this->z = $regionZ;
		$this->filePath = $filePath;
	}

	public function open(){
		$exists = file_exists($this->filePath);
		if(!$exists){
			touch($this->filePath);
		}else{
			$fileSize = filesize($this->filePath);
			if($fileSize > self::MAX_REGION_FILE_SIZE){
				throw new CorruptedRegionException("Corrupted oversized region file found, should be a maximum of " . self::MAX_REGION_FILE_SIZE . " bytes, got " . $fileSize . " bytes");
			}elseif($fileSize % 4096 !== 0){
				throw new CorruptedRegionException("Region file should be padded to a multiple of 4KiB");
			}
		}

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

	public function __destruct(){
		if(is_resource($this->filePointer)){
			$this->writeLocationTable();
			fclose($this->filePointer);
		}
	}

	protected function isChunkGenerated(int $index) : bool{
		return !($this->locationTable[$index][0] === 0 or $this->locationTable[$index][1] === 0);
	}

	/**
	 * @param int $x
	 * @param int $z
	 *
	 * @return null|string
	 * @throws \InvalidArgumentException if invalid coordinates are given
	 * @throws CorruptedChunkException if chunk corruption is detected
	 */
	public function readChunk(int $x, int $z) : ?string{
		$index = self::getChunkOffset($x, $z);
		if($index < 0 or $index >= 4096){
			throw new \InvalidArgumentException("Invalid chunk position in region, expected x/z in range 0-31, got x=$x, z=$z");
		}

		$this->lastUsed = time();

		if(!$this->isChunkGenerated($index)){
			return null;
		}

		fseek($this->filePointer, $this->locationTable[$index][0] << 12);
		$length = Binary::readInt(fread($this->filePointer, 4));
		$compression = ord(fgetc($this->filePointer));

		if($length <= 0 or $length > self::MAX_SECTOR_LENGTH){ //Not yet generated / corrupted
			if($length >= self::MAX_SECTOR_LENGTH){
				$this->locationTable[$index][0] = ++$this->lastSector;
				$this->locationTable[$index][1] = 1;
				throw new CorruptedChunkException("Corrupted chunk header detected (sector count larger than max)");
			}
			return null;
		}

		if($length > ($this->locationTable[$index][1] << 12)){ //Invalid chunk, bigger than defined number of sectors
			MainLogger::getLogger()->error("Corrupted bigger chunk detected (bigger than number of sectors given in header)");
			$this->locationTable[$index][1] = $length >> 12;
			$this->writeLocationIndex($index);
		}elseif($compression !== self::COMPRESSION_ZLIB and $compression !== self::COMPRESSION_GZIP){
			throw new CorruptedChunkException("Invalid compression type (got $compression, expected " . self::COMPRESSION_ZLIB . " or " . self::COMPRESSION_GZIP . ")");
		}

		$chunkData = fread($this->filePointer, $length - 1);
		if($chunkData === false){
			throw new CorruptedChunkException("Corrupted chunk detected (failed to read chunk data from disk)");

		}

		return $chunkData;
	}

	public function chunkExists(int $x, int $z) : bool{
		return $this->isChunkGenerated(self::getChunkOffset($x, $z));
	}

	public function writeChunk(int $x, int $z, string $chunkData){
		$this->lastUsed = time();

		$length = strlen($chunkData) + 1;
		if($length + 4 > self::MAX_SECTOR_LENGTH){
			throw new ChunkException("Chunk is too big! " . ($length + 4) . " > " . self::MAX_SECTOR_LENGTH);
		}
		$sectors = (int) ceil(($length + 4) / 4096);
		$index = self::getChunkOffset($x, $z);
		$indexChanged = false;
		if($this->locationTable[$index][1] < $sectors){
			$this->locationTable[$index][0] = $this->lastSector + 1;
			$this->lastSector += $sectors; //The GC will clean this shift "later"
			$indexChanged = true;
		}elseif($this->locationTable[$index][1] != $sectors){
			$indexChanged = true;
		}

		$this->locationTable[$index][1] = $sectors;
		$this->locationTable[$index][2] = time();

		fseek($this->filePointer, $this->locationTable[$index][0] << 12);
		fwrite($this->filePointer, str_pad(Binary::writeInt($length) . chr(self::COMPRESSION_ZLIB) . $chunkData, $sectors << 12, "\x00", STR_PAD_RIGHT));

		if($indexChanged){
			$this->writeLocationIndex($index);
		}
	}

	public function removeChunk(int $x, int $z){
		$index = self::getChunkOffset($x, $z);
		$this->locationTable[$index][0] = 0;
		$this->locationTable[$index][1] = 0;
	}

	protected static function getChunkOffset(int $x, int $z) : int{
		return $x + ($z << 5);
	}

	/**
	 * Writes the region header and closes the file
	 *
	 * @param bool $writeHeader
	 */
	public function close(bool $writeHeader = true){
		if(is_resource($this->filePointer)){
			if($writeHeader){
				$this->writeLocationTable();
			}

			fclose($this->filePointer);
		}
	}

	public function doSlowCleanUp() : int{
		for($i = 0; $i < 1024; ++$i){
			if($this->locationTable[$i][0] === 0 or $this->locationTable[$i][1] === 0){
				continue;
			}
			fseek($this->filePointer, $this->locationTable[$i][0] << 12);
			$chunk = fread($this->filePointer, $this->locationTable[$i][1] << 12);
			$length = Binary::readInt(substr($chunk, 0, 4));
			if($length <= 1){
				$this->locationTable[$i] = [0, 0, 0]; //Non-generated chunk, remove it from index
			}

			try{
				$chunk = zlib_decode(substr($chunk, 5));
			}catch(\Throwable $e){
				$this->locationTable[$i] = [0, 0, 0]; //Corrupted chunk, remove it
				continue;
			}

			$chunk = chr(self::COMPRESSION_ZLIB) . zlib_encode($chunk, ZLIB_ENCODING_DEFLATE, 9);
			$chunk = Binary::writeInt(strlen($chunk)) . $chunk;
			$sectors = (int) ceil(strlen($chunk) / 4096);
			if($sectors > $this->locationTable[$i][1]){
				$this->locationTable[$i][0] = $this->lastSector + 1;
				$this->lastSector += $sectors;
			}
			fseek($this->filePointer, $this->locationTable[$i][0] << 12);
			fwrite($this->filePointer, str_pad($chunk, $sectors << 12, "\x00", STR_PAD_RIGHT));
		}
		$this->writeLocationTable();
		$n = $this->cleanGarbage();
		$this->writeLocationTable();

		return $n;
	}

	private function cleanGarbage() : int{
		$sectors = [];
		foreach($this->locationTable as $index => $data){ //Calculate file usage
			if($data[0] === 0 or $data[1] === 0){
				$this->locationTable[$index] = [0, 0, 0];
				continue;
			}
			for($i = 0; $i < $data[1]; ++$i){
				$sectors[$data[0]] = $index;
			}
		}

		if(count($sectors) === ($this->lastSector - 2)){ //No collection needed
			return 0;
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
			$lastSector = $sector;
		}
		ftruncate($this->filePointer, ($sector + 1) << 12); //Truncate to the end of file written
		return $shift;
	}

	protected function loadLocationTable(){
		fseek($this->filePointer, 0);
		$this->lastSector = 1;

		$headerRaw = fread($this->filePointer, self::REGION_HEADER_LENGTH);
		if(($len = strlen($headerRaw)) !== self::REGION_HEADER_LENGTH){
			throw new CorruptedRegionException("Invalid region file header, expected " . self::REGION_HEADER_LENGTH . " bytes, got " . $len . " bytes");
		}

		$data = unpack("N*", $headerRaw);
		$usedOffsets = [];
		for($i = 0; $i < 1024; ++$i){
			$index = $data[$i + 1];
			$offset = $index >> 8;
			if($offset !== 0){
				fseek($this->filePointer, $offset << 12);
				if(fgetc($this->filePointer) === false){ //Try and read from the location
					throw new CorruptedRegionException("Region file location offset points to invalid location");
				}elseif(isset($usedOffsets[$offset])){
					throw new CorruptedRegionException("Found two chunk offsets pointing to the same location");
				}else{
					$usedOffsets[$offset] = true;
				}
			}

			$this->locationTable[$i] = [$index >> 8, $index & 0xff, $data[1024 + $i + 1]];
			if(($this->locationTable[$i][0] + $this->locationTable[$i][1] - 1) > $this->lastSector){
				$this->lastSector = $this->locationTable[$i][0] + $this->locationTable[$i][1] - 1;
			}
		}

		fseek($this->filePointer, 0);
	}

	private function writeLocationTable(){
		$write = [];

		for($i = 0; $i < 1024; ++$i){
			$write[] = (($this->locationTable[$i][0] << 8) | $this->locationTable[$i][1]);
		}
		for($i = 0; $i < 1024; ++$i){
			$write[] = $this->locationTable[$i][2];
		}
		fseek($this->filePointer, 0);
		fwrite($this->filePointer, pack("N*", ...$write), 4096 * 2);
	}

	protected function writeLocationIndex($index){
		fseek($this->filePointer, $index << 2);
		fwrite($this->filePointer, Binary::writeInt(($this->locationTable[$index][0] << 8) | $this->locationTable[$index][1]), 4);
		fseek($this->filePointer, 4096 + ($index << 2));
		fwrite($this->filePointer, Binary::writeInt($this->locationTable[$index][2]), 4);
	}

	protected function createBlank(){
		fseek($this->filePointer, 0);
		ftruncate($this->filePointer, 8192); // this fills the file with the null byte
		$this->lastSector = 1;
		$this->locationTable = array_fill(0, 1024, [0, 0, 0]);
	}

	public function getX() : int{
		return $this->x;
	}

	public function getZ() : int{
		return $this->z;
	}

	public function getFilePath() : string{
		return $this->filePath;
	}
}
