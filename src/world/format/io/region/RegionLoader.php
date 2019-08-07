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

namespace pocketmine\world\format\io\region;

use pocketmine\utils\Binary;
use pocketmine\world\format\ChunkException;
use pocketmine\world\format\io\exception\CorruptedChunkException;
use function ceil;
use function chr;
use function fclose;
use function feof;
use function file_exists;
use function filesize;
use function fopen;
use function fread;
use function fseek;
use function ftruncate;
use function fwrite;
use function is_resource;
use function max;
use function ord;
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

	private const FIRST_SECTOR = 2; //location table occupies 0 and 1

	public static $COMPRESSION_LEVEL = 7;

	/** @var string */
	protected $filePath;
	/** @var resource */
	protected $filePointer;
	/** @var int */
	protected $nextSector = self::FIRST_SECTOR;
	/** @var RegionLocationTableEntry[] */
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
			fclose($this->filePointer);
		}
	}

	protected function isChunkGenerated(int $index) : bool{
		return !$this->locationTable[$index]->isNull();
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

		fseek($this->filePointer, $this->locationTable[$index]->getFirstSector() << 12);

		$prefix = fread($this->filePointer, 4);
		if($prefix === false or strlen($prefix) !== 4){
			throw new CorruptedChunkException("Corrupted chunk header detected (unexpected end of file reading length prefix)");
		}
		$length = Binary::readInt($prefix);

		if($length <= 0){ //TODO: if we reached here, the locationTable probably needs updating
			return null;
		}
		if($length > self::MAX_SECTOR_LENGTH){ //corrupted
			throw new CorruptedChunkException("Length for chunk x=$x,z=$z ($length) is larger than maximum " . self::MAX_SECTOR_LENGTH);
		}

		if($length > ($this->locationTable[$index]->getSectorCount() << 12)){ //Invalid chunk, bigger than defined number of sectors
			\GlobalLogger::get()->error("Chunk x=$x,z=$z length mismatch (expected " . ($this->locationTable[$index]->getSectorCount() << 12) . " sectors, got $length sectors)");
			$old = $this->locationTable[$index];
			$this->locationTable[$index] = new RegionLocationTableEntry($old->getFirstSector(), $length >> 12, time());
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

		$newSize = (int) ceil(($length + 4) / 4096);
		$index = self::getChunkOffset($x, $z);
		$offset = $this->locationTable[$index]->getFirstSector();

		if($this->locationTable[$index]->getSectorCount() < $newSize){
			$offset = $this->nextSector;
		}

		$this->locationTable[$index] = new RegionLocationTableEntry($offset, $newSize, time());
		$this->bumpNextFreeSector($this->locationTable[$index]);

		fseek($this->filePointer, $offset << 12);
		fwrite($this->filePointer, str_pad(Binary::writeInt($length) . chr(self::COMPRESSION_ZLIB) . $chunkData, $newSize << 12, "\x00", STR_PAD_RIGHT));

		$this->writeLocationIndex($index);
	}

	/**
	 * @param int $x
	 * @param int $z
	 *
	 * @throws \InvalidArgumentException
	 */
	public function removeChunk(int $x, int $z) : void{
		$index = self::getChunkOffset($x, $z);
		$this->locationTable[$index] = new RegionLocationTableEntry(0, 0, 0);
		$this->writeLocationIndex($index);
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
	 * Closes the file
	 */
	public function close() : void{
		if(is_resource($this->filePointer)){
			fclose($this->filePointer);
		}
	}

	/**
	 * @throws CorruptedRegionException
	 */
	protected function loadLocationTable() : void{
		fseek($this->filePointer, 0);

		$headerRaw = fread($this->filePointer, self::REGION_HEADER_LENGTH);
		if(($len = strlen($headerRaw)) !== self::REGION_HEADER_LENGTH){
			throw new CorruptedRegionException("Invalid region file header, expected " . self::REGION_HEADER_LENGTH . " bytes, got " . $len . " bytes");
		}

		$data = unpack("N*", $headerRaw);

		for($i = 0; $i < 1024; ++$i){
			$index = $data[$i + 1];
			$offset = $index >> 8;
			$timestamp = $data[$i + 1025];

			if($offset === 0){
				$this->locationTable[$i] = new RegionLocationTableEntry(0, 0, 0);
			}else{
				$this->locationTable[$i] = new RegionLocationTableEntry($offset, $index & 0xff, $timestamp);
				$this->bumpNextFreeSector($this->locationTable[$i]);
			}
		}

		$this->checkLocationTableValidity();

		fseek($this->filePointer, 0);
	}

	/**
	 * @throws CorruptedRegionException
	 */
	private function checkLocationTableValidity() : void{
		/** @var int[] $usedOffsets */
		$usedOffsets = [];

		for($i = 0; $i < 1024; ++$i){
			$entry = $this->locationTable[$i];
			if($entry->isNull()){
				continue;
			}

			self::getChunkCoords($i, $x, $z);
			$offset = $entry->getFirstSector();
			$fileOffset = $offset << 12;

			//TODO: more validity checks

			fseek($this->filePointer, $fileOffset);
			if(feof($this->filePointer)){
				throw new CorruptedRegionException("Region file location offset x=$x,z=$z points to invalid file location $fileOffset");
			}
			if(isset($usedOffsets[$offset])){
				self::getChunkCoords($usedOffsets[$offset], $existingX, $existingZ);
				throw new CorruptedRegionException("Found two chunk offsets (chunk1: x=$existingX,z=$existingZ, chunk2: x=$x,z=$z) pointing to the file location $fileOffset");
			}
			$usedOffsets[$offset] = $i;
		}
	}

	protected function writeLocationIndex(int $index) : void{
		fseek($this->filePointer, $index << 2);
		fwrite($this->filePointer, Binary::writeInt(($this->locationTable[$index]->getFirstSector() << 8) | $this->locationTable[$index]->getSectorCount()), 4);
		fseek($this->filePointer, 4096 + ($index << 2));
		fwrite($this->filePointer, Binary::writeInt($this->locationTable[$index]->getTimestamp()), 4);
	}

	protected function createBlank() : void{
		fseek($this->filePointer, 0);
		ftruncate($this->filePointer, 8192); // this fills the file with the null byte
		for($i = 0; $i < 1024; ++$i){
			$this->locationTable[$i] = new RegionLocationTableEntry(0, 0, 0);
		}
	}

	private function bumpNextFreeSector(RegionLocationTableEntry $entry) : void{
		$this->nextSector = max($this->nextSector, $entry->getLastSector()) + 1;
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
