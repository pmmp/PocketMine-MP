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
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\Byte;
use pocketmine\nbt\tag\ByteArray;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Int;
use pocketmine\nbt\tag\IntArray;
use pocketmine\nbt\tag\Long;
use pocketmine\utils\Binary;

class RegionLoader extends \pocketmine\level\format\mcregion\RegionLoader{
	const VERSION = 1;
	const COMPRESSION_GZIP = 1;
	const COMPRESSION_ZLIB = 2;
	public static $COMPRESSION_LEVEL = 7;

	public function __construct(LevelProvider $level, $regionX, $regionZ){
		$this->x = $regionX;
		$this->z = $regionZ;
		$this->levelProvider = $level;
		$this->filePath = $this->levelProvider->getPath() . "region/r.$regionX.$regionZ.mca";
		touch($this->filePath);
		$this->filePointer = fopen($this->filePath, "r+b");
		flock($this->filePointer, LOCK_EX);
		stream_set_read_buffer($this->filePointer, 1024 * 16); //16KB
		stream_set_write_buffer($this->filePointer, 1024 * 16); //16KB
		if(!file_exists($this->filePath)){
			$this->createBlank();
		}else{
			$this->loadLocationTable();
		}
	}

	public function readChunk($x, $z, $generate = true, $forward = false){
		$index = self::getChunkOffset($x, $z);
		if($index < 0 or $index >= 4096){
			//Regenerate chunk due to corruption
			$this->locationTable[$index][0] = 0;
			$this->locationTable[$index][1] = 1;
		}

		if(!$this->isChunkGenerated($index)){
			if($generate === true){
				//Allocate space
				$this->locationTable[$index][0] = ++$this->lastSector;
				$this->locationTable[$index][1] = 1;
				fseek($this->filePointer, $this->locationTable[$index][0] << 12);
				fwrite($this->filePointer, str_pad(Binary::writeInt(-1) . chr(self::COMPRESSION_ZLIB), 4096, "\x00", STR_PAD_RIGHT));
				$this->writeLocationIndex($index);
			}else{
				return false;
			}
		}

		fseek($this->filePointer, $this->locationTable[$index][0] << 12);
		$length = Binary::readInt(fread($this->filePointer, 4));
		$compression = ord(fgetc($this->filePointer));

		if($length <= 0){ //Not yet generated
			$this->generateChunk($x, $z);
			fseek($this->filePointer, $this->locationTable[$index][0] << 12);
			$length = Binary::readInt(fread($this->filePointer, 4));
			$compression = ord(fgetc($this->filePointer));
		}

		if($length > ($this->locationTable[$index][1] << 12)){ //Invalid chunk, bigger than defined number of sectors
			trigger_error("Corrupted bigger chunk detected", E_USER_WARNING);
			$this->locationTable[$index][1] = $length >> 12;
			$this->writeLocationIndex($index);
		}elseif($compression !== self::COMPRESSION_ZLIB and $compression !== self::COMPRESSION_GZIP){
			trigger_error("Invalid compression type", E_USER_WARNING);

			return false;
		}

		$chunk = Chunk::fromBinary(fread($this->filePointer, $length - 1), $this->levelProvider);
		if($chunk instanceof Chunk){
			return $chunk;
		}elseif($forward === false){
			trigger_error("Corrupted chunk detected", E_USER_WARNING);
			$this->generateChunk($x, $z);

			return $this->readChunk($x, $z, $generate, true);
		}else{
			return null;
		}
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
		$nbt->BiomeColors = new IntArray("BiomeColors", array_fill(0, 156, Binary::readInt("\x00\x85\xb2\x4a")));
		$nbt->HeightMap = new IntArray("HeightMap", array_fill(0, 256, 127));
		$nbt->Sections = new Enum("Sections", []);
		$nbt->Sections->setTagType(NBT::TAG_Compound);
		$nbt->Entities = new Enum("Entities", []);
		$nbt->Entities->setTagType(NBT::TAG_Compound);
		$nbt->TileEntities = new Enum("TileEntities", []);
		$nbt->TileEntities->setTagType(NBT::TAG_Compound);
		$nbt->TileTicks = new Enum("TileTicks", []);
		$nbt->TileTicks->setTagType(NBT::TAG_Compound);
		$writer = new NBT(NBT::BIG_ENDIAN);
		$nbt->setName("Level");
		$writer->setData(new Compound("", array("Level" => $nbt)));
		$chunkData = $writer->writeCompressed(ZLIB_ENCODING_DEFLATE, RegionLoader::$COMPRESSION_LEVEL);
		$this->saveChunk($x, $z, $chunkData);
	}

}