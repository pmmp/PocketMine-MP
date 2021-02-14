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

use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Binary;
use pocketmine\utils\BinaryDataException;
use pocketmine\utils\BinaryStream;
use pocketmine\world\format\ChunkException;
use pocketmine\world\format\io\exception\CorruptedChunkException;
use function assert;
use function ceil;
use function chr;
use function clearstatcache;
use function fclose;
use function file_exists;
use function filesize;
use function fopen;
use function fread;
use function fseek;
use function ftruncate;
use function fwrite;
use function is_resource;
use function ksort;
use function max;
use function str_pad;
use function str_repeat;
use function stream_set_read_buffer;
use function stream_set_write_buffer;
use function strlen;
use function time;
use function touch;
use function unpack;
use const SORT_NUMERIC;
use const STR_PAD_RIGHT;

class RegionLoader{
	public const COMPRESSION_GZIP = 1;
	public const COMPRESSION_ZLIB = 2;

	private const MAX_SECTOR_LENGTH = 255 << 12; //255 sectors (~0.996 MiB)
	private const REGION_HEADER_LENGTH = 8192; //4096 location table + 4096 timestamps

	public const FIRST_SECTOR = 2; //location table occupies 0 and 1

	/** @var int */
	public static $COMPRESSION_LEVEL = 7;

	/** @var string */
	protected $filePath;
	/** @var resource */
	protected $filePointer;
	/** @var int */
	protected $nextSector = self::FIRST_SECTOR;
	/** @var RegionLocationTableEntry[]|null[] */
	protected $locationTable = [];
	/** @var RegionGarbageMap */
	protected $garbageTable;
	/** @var int */
	public $lastUsed = 0;

	public function __construct(string $filePath){
		$this->filePath = $filePath;
		$this->garbageTable = new RegionGarbageMap([]);
	}

	/**
	 * @throws CorruptedRegionException
	 */
	public function open() : void{
		clearstatcache(false, $this->filePath);
		$exists = file_exists($this->filePath);
		if(!$exists){
			touch($this->filePath);
		}elseif(filesize($this->filePath) % 4096 !== 0){
			throw new CorruptedRegionException("Region file should be padded to a multiple of 4KiB");
		}

		$filePointer = fopen($this->filePath, "r+b");
		if($filePointer === false) throw new AssumptionFailedError("fopen() should not fail here");
		$this->filePointer = $filePointer;
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
		return $this->locationTable[$index] !== null;
	}

	/**
	 * @throws \InvalidArgumentException if invalid coordinates are given
	 * @throws CorruptedChunkException if chunk corruption is detected
	 */
	public function readChunk(int $x, int $z) : ?string{
		$index = self::getChunkOffset($x, $z);

		$this->lastUsed = time();

		if($this->locationTable[$index] === null){
			return null;
		}

		fseek($this->filePointer, $this->locationTable[$index]->getFirstSector() << 12);

		/*
		 * this might cause us to read some junk, but under normal circumstances it won't be any more than 4096 bytes wasted.
		 * doing this in a single call is faster than making two seeks and reads to fetch the chunk.
		 * this relies on the assumption that the end of the file is always padded to a multiple of 4096 bytes.
		 */
		$bytesToRead = $this->locationTable[$index]->getSectorCount() << 12;
		$payload = fread($this->filePointer, $bytesToRead);

		if($payload === false || strlen($payload) !== $bytesToRead){
			throw new CorruptedChunkException("Corrupted chunk detected (unexpected EOF, truncated or non-padded chunk found)");
		}
		$stream = new BinaryStream($payload);

		try{
			$length = $stream->getInt();
			if($length <= 0){ //TODO: if we reached here, the locationTable probably needs updating
				return null;
			}

			$compression = $stream->getByte();
			if($compression !== self::COMPRESSION_ZLIB and $compression !== self::COMPRESSION_GZIP){
				throw new CorruptedChunkException("Invalid compression type (got $compression, expected " . self::COMPRESSION_ZLIB . " or " . self::COMPRESSION_GZIP . ")");
			}

			return $stream->get($length - 1); //length prefix includes the compression byte
		}catch(BinaryDataException $e){
			throw new CorruptedChunkException("Corrupted chunk detected: " . $e->getMessage(), 0, $e);
		}
	}

	/**
	 * @throws \InvalidArgumentException
	 */
	public function chunkExists(int $x, int $z) : bool{
		return $this->isChunkGenerated(self::getChunkOffset($x, $z));
	}

	private function disposeGarbageArea(RegionLocationTableEntry $oldLocation) : void{
		/* release the area containing the old copy to the garbage pool */
		$this->garbageTable->add($oldLocation);

		$endGarbage = $this->garbageTable->end();
		$nextSector = $this->nextSector;
		for(; $endGarbage !== null and $endGarbage->getLastSector() + 1 === $nextSector; $endGarbage = $this->garbageTable->end()){
			$nextSector = $endGarbage->getFirstSector();
			$this->garbageTable->remove($endGarbage);
		}

		if($nextSector !== $this->nextSector){
			$this->nextSector = $nextSector;
			ftruncate($this->filePointer, $this->nextSector << 12);
		}
	}

	/**
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

		/*
		 * look for an unused area big enough to hold this data
		 * this is corruption-resistant (it leaves the old data intact if a failure occurs when writing new data), and
		 * also allows the file to become more compact across consecutive writes without introducing a dedicated garbage
		 * collection mechanism.
		 */
		$newLocation = $this->garbageTable->allocate($newSize);

		/* if no gaps big enough were found, append to the end of the file instead */
		if($newLocation === null){
			$newLocation = new RegionLocationTableEntry($this->nextSector, $newSize, time());
			$this->bumpNextFreeSector($newLocation);
		}

		/* write the chunk data into the chosen location */
		fseek($this->filePointer, $newLocation->getFirstSector() << 12);
		fwrite($this->filePointer, str_pad(Binary::writeInt($length) . chr(self::COMPRESSION_ZLIB) . $chunkData, $newSize << 12, "\x00", STR_PAD_RIGHT));

		/*
		 * update the file header - we do this after writing the main data, so that if a failure occurs while writing,
		 * the header will still point to the old (intact) copy of the chunk, instead of a potentially broken new
		 * version of the file (e.g. partially written).
		*/
		$oldLocation = $this->locationTable[$index];
		$this->locationTable[$index] = $newLocation;
		$this->writeLocationIndex($index);

		if($oldLocation !== null){
			$this->disposeGarbageArea($oldLocation);
		}
	}

	/**
	 * @throws \InvalidArgumentException
	 */
	public function removeChunk(int $x, int $z) : void{
		$index = self::getChunkOffset($x, $z);
		$oldLocation = $this->locationTable[$index];
		$this->locationTable[$index] = null;
		$this->writeLocationIndex($index);
		if($oldLocation !== null){
			$this->disposeGarbageArea($oldLocation);
		}
	}

	/**
	 * @throws \InvalidArgumentException
	 */
	protected static function getChunkOffset(int $x, int $z) : int{
		if($x < 0 or $x > 31 or $z < 0 or $z > 31){
			throw new \InvalidArgumentException("Invalid chunk position in region, expected x/z in range 0-31, got x=$x, z=$z");
		}
		return $x | ($z << 5);
	}

	/**
	 * @param int $x reference parameter
	 * @param int $z reference parameter
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
		if($headerRaw === false or strlen($headerRaw) !== self::REGION_HEADER_LENGTH){
			throw new CorruptedRegionException("Corrupted region header (unexpected end of file)");
		}

		/** @var int[] $data */
		$data = unpack("N*", $headerRaw);

		for($i = 0; $i < 1024; ++$i){
			$index = $data[$i + 1];
			$offset = $index >> 8;
			$sectorCount = $index & 0xff;
			$timestamp = $data[$i + 1025];

			if($offset === 0 or $sectorCount === 0){
				$this->locationTable[$i] = null;
			}elseif($offset >= self::FIRST_SECTOR){
				$this->bumpNextFreeSector($this->locationTable[$i] = new RegionLocationTableEntry($offset, $sectorCount, $timestamp));
			}else{
				self::getChunkCoords($i, $chunkXX, $chunkZZ);
				throw new CorruptedRegionException("Invalid region header entry for x=$chunkXX z=$chunkZZ, offset overlaps with header");
			}
		}

		$this->checkLocationTableValidity();

		$this->garbageTable = RegionGarbageMap::buildFromLocationTable($this->locationTable);

		fseek($this->filePointer, 0);
	}

	/**
	 * @throws CorruptedRegionException
	 */
	private function checkLocationTableValidity() : void{
		/** @var int[] $usedOffsets */
		$usedOffsets = [];

		$fileSize = filesize($this->filePath);
		if($fileSize === false) throw new AssumptionFailedError("filesize() should not return false here");
		for($i = 0; $i < 1024; ++$i){
			$entry = $this->locationTable[$i];
			if($entry === null){
				continue;
			}

			self::getChunkCoords($i, $x, $z);
			$offset = $entry->getFirstSector();
			$fileOffset = $offset << 12;

			//TODO: more validity checks

			if($fileOffset >= $fileSize){
				throw new CorruptedRegionException("Region file location offset x=$x,z=$z points to invalid file location $fileOffset");
			}
			if(isset($usedOffsets[$offset])){
				self::getChunkCoords($usedOffsets[$offset], $existingX, $existingZ);
				throw new CorruptedRegionException("Found two chunk offsets (chunk1: x=$existingX,z=$existingZ, chunk2: x=$x,z=$z) pointing to the file location $fileOffset");
			}
			$usedOffsets[$offset] = $i;
		}
		ksort($usedOffsets, SORT_NUMERIC);
		$prevLocationIndex = null;
		foreach($usedOffsets as $startOffset => $locationTableIndex){
			if($this->locationTable[$locationTableIndex] === null){
				continue;
			}
			if($prevLocationIndex !== null){
				assert($this->locationTable[$prevLocationIndex] !== null);
				if($this->locationTable[$locationTableIndex]->overlaps($this->locationTable[$prevLocationIndex])){
					self::getChunkCoords($locationTableIndex, $chunkXX, $chunkZZ);
					self::getChunkCoords($prevLocationIndex, $prevChunkXX, $prevChunkZZ);
					throw new CorruptedRegionException("Overlapping chunks detected in region header (chunk1: x=$chunkXX,z=$chunkZZ, chunk2: x=$prevChunkXX,z=$prevChunkZZ)");
				}
			}
			$prevLocationIndex = $locationTableIndex;
		}
	}

	protected function writeLocationIndex(int $index) : void{
		$entry = $this->locationTable[$index];
		fseek($this->filePointer, $index << 2);
		fwrite($this->filePointer, Binary::writeInt($entry !== null ? ($entry->getFirstSector() << 8) | $entry->getSectorCount() : 0), 4);
		fseek($this->filePointer, 4096 + ($index << 2));
		fwrite($this->filePointer, Binary::writeInt($entry !== null ? $entry->getTimestamp() : 0), 4);
		clearstatcache(false, $this->filePath);
	}

	protected function createBlank() : void{
		fseek($this->filePointer, 0);
		ftruncate($this->filePointer, 8192); // this fills the file with the null byte
		for($i = 0; $i < 1024; ++$i){
			$this->locationTable[$i] = null;
		}
	}

	private function bumpNextFreeSector(RegionLocationTableEntry $entry) : void{
		$this->nextSector = max($this->nextSector, $entry->getLastSector() + 1);
	}

	public function generateSectorMap(string $usedChar, string $freeChar) : string{
		$result = str_repeat($freeChar, $this->nextSector);
		for($i = 0; $i < self::FIRST_SECTOR; ++$i){
			$result[$i] = $usedChar;
		}
		foreach($this->locationTable as $locationTableEntry){
			if($locationTableEntry === null){
				continue;
			}
			foreach($locationTableEntry->getUsedSectors() as $sectorIndex){
				if($sectorIndex >= strlen($result)){
					throw new AssumptionFailedError("This should never happen...");
				}
				if($result[$sectorIndex] === $usedChar){
					throw new AssumptionFailedError("Overlap detected");
				}
				$result[$sectorIndex] = $usedChar;
			}
		}
		return $result;
	}

	/**
	 * Returns a float between 0 and 1 indicating what fraction of the file is currently unused space.
	 */
	public function getProportionUnusedSpace() : float{
		$size = $this->nextSector;
		$used = self::FIRST_SECTOR; //header is always allocated
		foreach($this->locationTable as $entry){
			if($entry !== null){
				$used += $entry->getSectorCount();
			}
		}
		return 1 - ($used / $size);
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
