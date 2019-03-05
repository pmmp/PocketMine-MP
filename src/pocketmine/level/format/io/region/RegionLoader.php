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
use function array_fill;
use function ceil;
use function chr;
use function fclose;
use function fgetc;
use function file_exists;
use function filesize;
use function fopen;
use function fread;
use function fseek;
use function ftruncate;
use function fwrite;
use function is_resource;
use function ord;
use function pack;
use function str_pad;
use function stream_set_read_buffer;
use function stream_set_write_buffer;
use function strlen;
use function substr;
use function time;
use function touch;
use function unpack;
use const STR_PAD_RIGHT;

class RegionLoader{
	public const COMPRESSION_GZIP = 1;
	public const COMPRESSION_ZLIB = 2;

	private const MAX_SECTOR_LENGTH = 255 << 12; //255 sectors (~0.996 MiB)
	private const REGION_HEADER_LENGTH = 8192; //4096 location table + 4096 timestamps

	public static $COMPRESSION_LEVEL = 7;

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

	public function __construct(string $filePath){
		$this->filePath = $filePath;
	}

	/**
	 * @throws CorruptedRegionException
	 */
	public function open() : void{
		$exists = file_exists($this->filePath);
		if(!$exists){
			touch($this->filePath);
		}elseif(filesize($this->filePath) % 4096 !== 0){
			throw new CorruptedRegionException("Region file should be padded to a multiple of 4KiB");
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

		$this->lastUsed = time();

		if(!$this->isChunkGenerated($index)){
			return null;
		}

		fseek($this->filePointer, $this->locationTable[$index][0] << 12);
		$prefix = fread($this->filePointer, 4);
		if($prefix === false or strlen($prefix) !== 4){
			throw new CorruptedChunkException("Corrupted chunk header detected (unexpected end of file reading length prefix)");
		}
		$length = Binary::readInt($prefix);

		if($length <= 0 or $length > self::MAX_SECTOR_LENGTH){ //Not yet generated / corrupted
			if($length >= self::MAX_SECTOR_LENGTH){
				throw new CorruptedChunkException("Corrupted chunk header detected (sector count $length larger than max " . self::MAX_SECTOR_LENGTH . ")");
			}
			return null;
		}

		if($length > ($this->locationTable[$index][1] << 12)){ //Invalid chunk, bigger than defined number of sectors
			\GlobalLogger::get()->error("Chunk x=$x,z=$z length mismatch (expected " . ($this->locationTable[$index][1] << 12) . " sectors, got $length sectors)");
			$this->locationTable[$index][1] = $length >> 12;
			$this->writeLocationIndex($index);
		}

		$chunkData = fread($this->filePointer, $length);
		if($chunkData === false or strlen($chunkData) !== $length){
			throw new CorruptedChunkException("Corrupted chunk detected (unexpected end of file reading chunk data)");
		}

		$compression = ord($chunkData[0]);
		if($compression !== self::COMPRESSION_ZLIB and $compression !== self::COMPRESSION_GZIP){
			throw new CorruptedChunkException("Invalid compression type (got $compression, expected " . self::COMPRESSION_ZLIB . " or " . self::COMPRESSION_GZIP . ")");
		}

		return substr($chunkData, 1);
	}

	/**
	 * @param int $x
	 * @param int $z
	 *
	 * @return bool
	 * @throws \InvalidArgumentException
	 */
	public function chunkExists(int $x, int $z) : bool{
		return $this->isChunkGenerated(self::getChunkOffset($x, $z));
	}

	/**
	 * @param int    $x
	 * @param int    $z
	 * @param string $chunkData
	 *
	 * @throws ChunkException
	 * @throws \InvalidArgumentException
	 */
	public function writeChunk(int $x, int $z, string $chunkData) : void{
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

	/**
	 * @param int $x
	 * @param int $z
	 *
	 * @throws \InvalidArgumentException
	 */
	public function removeChunk(int $x, int $z) : void{
		$index = self::getChunkOffset($x, $z);
		$this->locationTable[$index][0] = 0;
		$this->locationTable[$index][1] = 0;
	}

	/**
	 * @param int $x
	 * @param int $z
	 *
	 * @return int
	 * @throws \InvalidArgumentException
	 */
	protected static function getChunkOffset(int $x, int $z) : int{
		if($x < 0 or $x > 31 or $z < 0 or $z > 31){
			throw new \InvalidArgumentException("Invalid chunk position in region, expected x/z in range 0-31, got x=$x, z=$z");
		}
		return $x | ($z << 5);
	}

	/**
	 * @param int $offset
	 * @param int &$x
	 * @param int &$z
	 */
	protected static function getChunkCoords(int $offset, ?int &$x, ?int &$z) : void{
		$x = $offset & 0x1f;
		$z = ($offset >> 5) & 0x1f;
	}

	/**
	 * Writes the region header and closes the file
	 *
	 * @param bool $writeHeader
	 */
	public function close(bool $writeHeader = true) : void{
		if(is_resource($this->filePointer)){
			if($writeHeader){
				$this->writeLocationTable();
			}

			fclose($this->filePointer);
		}
	}

	/**
	 * @throws CorruptedRegionException
	 */
	protected function loadLocationTable() : void{
		fseek($this->filePointer, 0);
		$this->lastSector = 1;

		$headerRaw = fread($this->filePointer, self::REGION_HEADER_LENGTH);
		if(($len = strlen($headerRaw)) !== self::REGION_HEADER_LENGTH){
			throw new CorruptedRegionException("Invalid region file header, expected " . self::REGION_HEADER_LENGTH . " bytes, got " . $len . " bytes");
		}

		$data = unpack("N*", $headerRaw);
		/** @var int[] $usedOffsets */
		$usedOffsets = [];
		for($i = 0; $i < 1024; ++$i){
			$index = $data[$i + 1];
			$offset = $index >> 8;
			if($offset !== 0){
				self::getChunkCoords($i, $x, $z);
				$fileOffset = $offset << 12;

				fseek($this->filePointer, $fileOffset);
				if(fgetc($this->filePointer) === false){ //Try and read from the location
					throw new CorruptedRegionException("Region file location offset x=$x,z=$z points to invalid file location $fileOffset");
				}elseif(isset($usedOffsets[$offset])){
					self::getChunkCoords($usedOffsets[$offset], $existingX, $existingZ);
					throw new CorruptedRegionException("Found two chunk offsets (chunk1: x=$existingX,z=$existingZ, chunk2: x=$x,z=$z) pointing to the file location $fileOffset");
				}else{
					$usedOffsets[$offset] = $i;
				}
			}

			$this->locationTable[$i] = [$index >> 8, $index & 0xff, $data[1024 + $i + 1]];
			if(($this->locationTable[$i][0] + $this->locationTable[$i][1] - 1) > $this->lastSector){
				$this->lastSector = $this->locationTable[$i][0] + $this->locationTable[$i][1] - 1;
			}
		}

		fseek($this->filePointer, 0);
	}

	private function writeLocationTable() : void{
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

	protected function writeLocationIndex(int $index) : void{
		fseek($this->filePointer, $index << 2);
		fwrite($this->filePointer, Binary::writeInt(($this->locationTable[$index][0] << 8) | $this->locationTable[$index][1]), 4);
		fseek($this->filePointer, 4096 + ($index << 2));
		fwrite($this->filePointer, Binary::writeInt($this->locationTable[$index][2]), 4);
	}

	protected function createBlank() : void{
		fseek($this->filePointer, 0);
		ftruncate($this->filePointer, 8192); // this fills the file with the null byte
		$this->lastSector = 1;
		$this->locationTable = array_fill(0, 1024, [0, 0, 0]);
	}

	public function getFilePath() : string{
		return $this->filePath;
	}

	public function calculateChunkCount() : int{
		$count = 0;
		for($i = 0; $i < 1024; ++$i){
			if($this->isChunkGenerated($i)){
				$count++;
			}
		}
		return $count;
	}
}
