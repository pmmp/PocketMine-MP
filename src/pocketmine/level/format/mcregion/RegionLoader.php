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

use pocketmine\level\format\FullChunk;
use pocketmine\level\format\LevelProvider;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\Byte;
use pocketmine\nbt\tag\ByteArray;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Int;
use pocketmine\nbt\tag\IntArray;
use pocketmine\nbt\tag\Long;
use pocketmine\utils\Binary;
use pocketmine\utils\ChunkException;
use pocketmine\utils\MainLogger;

class RegionLoader{
	const VERSION = 1;
	const COMPRESSION_GZIP = 1;
	const COMPRESSION_ZLIB = 2;
	const MAX_SECTOR_LENGTH = 256 << 12; //256 sectors, (1 MiB)
	public static $COMPRESSION_LEVEL = 7;

	protected $x;
	protected $z;
	protected $filePath;
	protected $filePointer;
	protected $lastSector;
	/** @var LevelProvider */
	protected $levelProvider;
	protected $locationTable = [];

	public $lastUsed;

	public function __construct(LevelProvider $level, $regionX, $regionZ){
		$this->x = $regionX;
		$this->z = $regionZ;
		$this->levelProvider = $level;
		$this->filePath = $this->levelProvider->getPath() . "region/r.$regionX.$regionZ.mcr";
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

	public function __destruct(){
		if(is_resource($this->filePointer)){
			$this->writeLocationTable();
			fclose($this->filePointer);
		}
	}

	protected function isChunkGenerated($index){
		return !($this->locationTable[$index][0] === 0 or $this->locationTable[$index][1] === 0);
	}

	public function readChunk($x, $z, $generate = true, $forward = false){
		$index = self::getChunkOffset($x, $z);
		if($index < 0 or $index >= 4096){
			return null;
		}

		$this->lastUsed = time();

		if(!$this->isChunkGenerated($index)){
			if($generate === true){
				//Allocate space
				$this->locationTable[$index][0] = ++$this->lastSector;
				$this->locationTable[$index][1] = 1;
				fseek($this->filePointer, $this->locationTable[$index][0] << 12);
				fwrite($this->filePointer, str_pad(Binary::writeInt(0) . chr(self::COMPRESSION_ZLIB), 4096, "\x00", STR_PAD_RIGHT));
				$this->writeLocationIndex($index);
			}else{
				return null;
			}
		}

		fseek($this->filePointer, $this->locationTable[$index][0] << 12);
		$length = Binary::readInt(fread($this->filePointer, 4));
		$compression = ord(fgetc($this->filePointer));

		if($length <= 0 or $length > self::MAX_SECTOR_LENGTH){ //Not yet generated / corrupted
			if($length >= self::MAX_SECTOR_LENGTH){
				$this->locationTable[$index][0] = ++$this->lastSector;
				$this->locationTable[$index][1] = 1;
				MainLogger::getLogger()->error("Corrupted chunk header detected");
			}
			$this->generateChunk($x, $z);
			fseek($this->filePointer, $this->locationTable[$index][0] << 12);
			$length = Binary::readInt(fread($this->filePointer, 4));
			$compression = ord(fgetc($this->filePointer));
		}

		if($length > ($this->locationTable[$index][1] << 12)){ //Invalid chunk, bigger than defined number of sectors
			MainLogger::getLogger()->error("Corrupted bigger chunk detected");
			$this->locationTable[$index][1] = $length >> 12;
			$this->writeLocationIndex($index);
		}elseif($compression !== self::COMPRESSION_ZLIB and $compression !== self::COMPRESSION_GZIP){
			MainLogger::getLogger()->error("Invalid compression type");

			return null;
		}

		$chunk = Chunk::fromBinary(fread($this->filePointer, $length - 1), $this->levelProvider);
		if($chunk instanceof Chunk){
			return $chunk;
		}elseif($forward === false){
			MainLogger::getLogger()->error("Corrupted chunk detected");
			$this->generateChunk($x, $z);

			return $this->readChunk($x, $z, $generate, true);
		}else{
			return null;
		}
	}

	public function chunkExists($x, $z){
		return $this->isChunkGenerated(self::getChunkOffset($x, $z));
	}

	public function generateChunk($x, $z){
		$nbt = new Compound("Level", []);
		$nbt->xPos = new Int("xPos", ($this->getX() * 32) + $x);
		$nbt->zPos = new Int("zPos", ($this->getZ() * 32) + $z);
		$nbt->LastUpdate = new Long("LastUpdate", 0);
		$nbt->LightPopulated = new Byte("LightPopulated", 0);
		$nbt->TerrainPopulated = new Byte("TerrainPopulated", 0);
		$nbt->V = new Byte("V", self::VERSION);
		$nbt->InhabitedTime = new Long("InhabitedTime", 0);
		$nbt->Biomes = new ByteArray("Biomes", str_repeat(Binary::writeByte(-1), 256));
		$nbt->HeightMap = new IntArray("HeightMap", array_fill(0, 256, 127));
		$nbt->BiomeColors = new IntArray("BiomeColors", array_fill(0, 256, Binary::readInt("\x00\x85\xb2\x4a")));

		$nbt->Blocks = new ByteArray("Blocks", str_repeat("\x00", 32768));
		$nbt->Data = new ByteArray("Data", $half = str_repeat("\x00", 16384));
		$nbt->SkyLight = new ByteArray("SkyLight", $half);
		$nbt->BlockLight = new ByteArray("BlockLight", $half);

		$nbt->Entities = new Enum("Entities", []);
		$nbt->Entities->setTagType(NBT::TAG_Compound);
		$nbt->TileEntities = new Enum("TileEntities", []);
		$nbt->TileEntities->setTagType(NBT::TAG_Compound);
		$nbt->TileTicks = new Enum("TileTicks", []);
		$nbt->TileTicks->setTagType(NBT::TAG_Compound);
		$writer = new NBT(NBT::BIG_ENDIAN);
		$nbt->setName("Level");
		$writer->setData(new Compound("", ["Level" => $nbt]));
		$chunkData = $writer->writeCompressed(ZLIB_ENCODING_DEFLATE, self::$COMPRESSION_LEVEL);

		if($chunkData !== false){
			$this->saveChunk($x, $z, $chunkData);
		}
	}

	protected function saveChunk($x, $z, $chunkData){
		$length = strlen($chunkData) + 1;
		if($length + 4 > self::MAX_SECTOR_LENGTH){
			throw new ChunkException("Chunk is too big! ".($length + 4)." > ".self::MAX_SECTOR_LENGTH);
		}
		$sectors = (int) ceil(($length + 4) / 4096);
		$index = self::getChunkOffset($x, $z);
		if($this->locationTable[$index][1] < $sectors){
			$this->locationTable[$index][0] = $this->lastSector + 1;
			$this->lastSector += $sectors; //The GC will clean this shift "later"
		}
		$this->locationTable[$index][1] = $sectors;
		$this->locationTable[$index][2] = time();

		fseek($this->filePointer, $this->locationTable[$index][0] << 12);
		fwrite($this->filePointer, str_pad(Binary::writeInt($length) . chr(self::COMPRESSION_ZLIB) . $chunkData, $sectors << 12, "\x00", STR_PAD_RIGHT));
		$this->writeLocationIndex($index);
	}

	public function removeChunk($x, $z){
		$index = self::getChunkOffset($x, $z);
		$this->locationTable[$index][0] = 0;
		$this->locationTable[$index][1] = 0;
	}

	public function writeChunk(FullChunk $chunk){
		$this->lastUsed = time();
		$chunkData = $chunk->toBinary();
		if($chunkData !== false){
			$this->saveChunk($chunk->getX() - ($this->getX() * 32), $chunk->getZ() - ($this->getZ() * 32), $chunkData);
		}
	}

	protected static function getChunkOffset($x, $z){
		return $x + ($z << 5);
	}

	public function close(){
		$this->writeLocationTable();
		fclose($this->filePointer);
		$this->levelProvider = null;
	}

	public function doSlowCleanUp(){
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
			}catch(\Exception $e){
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

	private function cleanGarbage(){
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
		$table = fread($this->filePointer, 4 * 1024 * 2); //1024 records * 4 bytes * 2 times
		for($i = 0; $i < 1024; ++$i){
			$index = unpack("N", substr($table, $i << 2, 4))[1];
			$this->locationTable[$i] = [$index >> 8, $index & 0xff, unpack("N", substr($table, 4096 + ($i << 2), 4))[1]];
			if(($this->locationTable[$i][0] + $this->locationTable[$i][1] - 1) > $this->lastSector){
				$this->lastSector = $this->locationTable[$i][0] + $this->locationTable[$i][1] - 1;
			}
		}
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
		ftruncate($this->filePointer, 0);
		$this->lastSector = 1;
		$table = "";
		for($i = 0; $i < 1024; ++$i){
			$this->locationTable[$i] = [0, 0];
			$table .= Binary::writeInt(0);
		}

		$time = time();
		for($i = 0; $i < 1024; ++$i){
			$this->locationTable[$i][2] = $time;
			$table .= Binary::writeInt($time);
		}

		fwrite($this->filePointer, $table, 4096 * 2);
	}

	public function getX(){
		return $this->x;
	}

	public function getZ(){
		return $this->z;
	}

}
