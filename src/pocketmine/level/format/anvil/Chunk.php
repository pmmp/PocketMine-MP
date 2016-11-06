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

use pocketmine\level\format\generic\BaseChunk;
use pocketmine\level\format\generic\EmptyChunkSection;
use pocketmine\level\format\LevelProvider;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteArrayTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntArrayTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\LongTag;
use pocketmine\Player;
use pocketmine\utils\Binary;
use pocketmine\utils\BinaryStream;

class Chunk extends BaseChunk{

	/** @var CompoundTag */
	protected $nbt;

	public function __construct($level, CompoundTag $nbt = null){
		if($nbt === null){
			$this->provider = $level;
			$this->nbt = new CompoundTag("Level", []);
			return;
		}

		$this->nbt = $nbt;

		if(!isset($this->nbt->Entities) or !($this->nbt->Entities instanceof ListTag)){
			$this->nbt->Entities = new ListTag("Entities", []);
			$this->nbt->Entities->setTagType(NBT::TAG_Compound);
		}

		if(!isset($this->nbt->TileEntities) or !($this->nbt->TileEntities instanceof ListTag)){
			$this->nbt->TileEntities = new ListTag("TileEntities", []);
			$this->nbt->TileEntities->setTagType(NBT::TAG_Compound);
		}

		if(!isset($this->nbt->TileTicks) or !($this->nbt->TileTicks instanceof ListTag)){
			$this->nbt->TileTicks = new ListTag("TileTicks", []);
			$this->nbt->TileTicks->setTagType(NBT::TAG_Compound);
		}

		if(!isset($this->nbt->Sections) or !($this->nbt->Sections instanceof ListTag)){
			$this->nbt->Sections = new ListTag("Sections", []);
			$this->nbt->Sections->setTagType(NBT::TAG_Compound);
		}

		if(!isset($this->nbt->BiomeColors) or !($this->nbt->BiomeColors instanceof IntArrayTag)){
			$this->nbt->BiomeColors = new IntArrayTag("BiomeColors", array_fill(0, 256, 0));
		}

		if(!isset($this->nbt->HeightMap) or !($this->nbt->HeightMap instanceof IntArrayTag)){
			$this->nbt->HeightMap = new IntArrayTag("HeightMap", array_fill(0, 256, 0));
		}

		$sections = [];
		foreach($this->nbt->Sections as $section){
			if($section instanceof CompoundTag){
				$y = (int) $section["Y"];
				if($y < 8){
					$sections[$y] = new ChunkSection($section);
				}
			}
		}
		for($y = 0; $y < 8; ++$y){
			if(!isset($sections[$y])){
				$sections[$y] = new EmptyChunkSection($y);
			}
		}

		$extraData = [];

		if(!isset($this->nbt->ExtraData) or !($this->nbt->ExtraData instanceof ByteArrayTag)){
			$this->nbt->ExtraData = new ByteArrayTag("ExtraData", Binary::writeInt(0));
		}else{
			$stream = new BinaryStream($this->nbt->ExtraData->getValue());
			$count = $stream->getInt();
			for($i = 0; $i < $count; ++$i){
				$key = $stream->getInt();
				$extraData[$key] = $stream->getShort(false);
			}
		}

		parent::__construct($level, (int) $this->nbt["xPos"], (int) $this->nbt["zPos"], $sections, $this->nbt->BiomeColors->getValue(), $this->nbt->HeightMap->getValue(), $this->nbt->Entities->getValue(), $this->nbt->TileEntities->getValue(), $extraData);

		if(isset($this->nbt->Biomes)){
			$this->checkOldBiomes($this->nbt->Biomes->getValue());
			unset($this->nbt->Biomes);
		}

		unset($this->nbt->Sections, $this->nbt->ExtraData);
	}

	public function isLightPopulated(){
		return $this->nbt["LightPopulated"] > 0;
	}

	public function setLightPopulated($value = 1){
		$this->nbt->LightPopulated = new ByteTag("LightPopulated", $value);
		$this->hasChanged = true;
	}

	/**
	 * @return bool
	 */
	public function isPopulated(){
		return $this->nbt["TerrainPopulated"] > 0;
	}

	/**
	 * @param int $value
	 */
	public function setPopulated($value = 1){
		$this->nbt->TerrainPopulated = new ByteTag("TerrainPopulated", $value);
		$this->hasChanged = true;
	}

	/**
	 * @return bool
	 */
	public function isGenerated(){
		return $this->nbt["TerrainPopulated"] > 0 or (isset($this->nbt->TerrainGenerated) and $this->nbt["TerrainGenerated"] > 0);
	}

	/**
	 * @param int $value
	 */
	public function setGenerated($value = 1){
		$this->nbt->TerrainGenerated = new ByteTag("TerrainGenerated", $value);
		$this->hasChanged = true;
	}

	/**
	 * @return CompoundTag
	 */
	public function getNBT(){
		return $this->nbt;
	}

	/**
	 * @param string        $data
	 * @param LevelProvider $provider
	 *
	 * @return Chunk
	 */
	public static function fromBinary($data, LevelProvider $provider = null){
		$nbt = new NBT(NBT::BIG_ENDIAN);

		try{
			$nbt->readCompressed($data, ZLIB_ENCODING_DEFLATE);
			$chunk = $nbt->getData();

			if(!isset($chunk->Level) or !($chunk->Level instanceof CompoundTag)){
				return null;
			}

			return new Chunk($provider instanceof LevelProvider ? $provider : Anvil::class, $chunk->Level);
		}catch(\Throwable $e){
			return null;
		}
	}

	/**
	 * @param string        $data
	 * @param LevelProvider $provider
	 *
	 * @return Chunk
	 */
	public static function fromFastBinary($data, LevelProvider $provider = null){
		$nbt = new NBT(NBT::BIG_ENDIAN);

		try{
			$nbt->read($data);
			$chunk = $nbt->getData();

			if(!isset($chunk->Level) or !($chunk->Level instanceof CompoundTag)){
				return null;
			}

			return new Chunk($provider instanceof LevelProvider ? $provider : Anvil::class, $chunk->Level);
		}catch(\Throwable $e){
			return null;
		}
	}

	public function toFastBinary(){
		$nbt = clone $this->getNBT();

		$nbt->xPos = new IntTag("xPos", $this->x);
		$nbt->zPos = new IntTag("zPos", $this->z);

		$nbt->Sections = new ListTag("Sections", []);
		$nbt->Sections->setTagType(NBT::TAG_Compound);
		foreach($this->getSections() as $section){
			if($section instanceof EmptyChunkSection){
				continue;
			}
			$nbt->Sections[$section->getY()] = new CompoundTag(null, [
				"Y" => new ByteTag("Y", $section->getY()),
				"Blocks" => new ByteArrayTag("Blocks", $section->getIdArray()),
				"Data" => new ByteArrayTag("Data", $section->getDataArray()),
				"BlockLight" => new ByteArrayTag("BlockLight", $section->getLightArray()),
				"SkyLight" => new ByteArrayTag("SkyLight", $section->getSkyLightArray())
			]);
		}

		$nbt->BiomeColors = new IntArrayTag("BiomeColors", $this->getBiomeColorArray());

		$nbt->HeightMap = new IntArrayTag("HeightMap", $this->getHeightMapArray());

		$entities = [];

		foreach($this->getEntities() as $entity){
			if(!($entity instanceof Player) and !$entity->closed){
				$entity->saveNBT();
				$entities[] = $entity->namedtag;
			}
		}

		$nbt->Entities = new ListTag("Entities", $entities);
		$nbt->Entities->setTagType(NBT::TAG_Compound);


		$tiles = [];
		foreach($this->getTiles() as $tile){
			$tile->saveNBT();
			$tiles[] = $tile->namedtag;
		}

		$nbt->TileEntities = new ListTag("TileEntities", $tiles);
		$nbt->TileEntities->setTagType(NBT::TAG_Compound);

		$extraData = new BinaryStream();
		$extraData->putInt(count($this->getBlockExtraDataArray()));
		foreach($this->getBlockExtraDataArray() as $key => $value){
			$extraData->putInt($key);
			$extraData->putShort($value);
		}

		$nbt->ExtraData = new ByteArrayTag("ExtraData", $extraData->getBuffer());

		$writer = new NBT(NBT::BIG_ENDIAN);
		$nbt->setName("Level");
		$writer->setData(new CompoundTag("", ["Level" => $nbt]));

		return $writer->write();
	}

	public function toBinary(){
		$nbt = clone $this->getNBT();

		$nbt->xPos = new IntTag("xPos", $this->x);
		$nbt->zPos = new IntTag("zPos", $this->z);

		$nbt->Sections = new ListTag("Sections", []);
		$nbt->Sections->setTagType(NBT::TAG_Compound);
		foreach($this->getSections() as $section){
			if($section instanceof EmptyChunkSection){
				continue;
			}
			$nbt->Sections[$section->getY()] = new CompoundTag(null, [
				"Y" => new ByteTag("Y", $section->getY()),
				"Blocks" => new ByteArrayTag("Blocks", $section->getIdArray()),
				"Data" => new ByteArrayTag("Data", $section->getDataArray()),
				"BlockLight" => new ByteArrayTag("BlockLight", $section->getLightArray()),
				"SkyLight" => new ByteArrayTag("SkyLight", $section->getSkyLightArray())
			]);
		}

		$nbt->BiomeColors = new IntArrayTag("BiomeColors", $this->getBiomeColorArray());

		$nbt->HeightMap = new IntArrayTag("HeightMap", $this->getHeightMapArray());

		$entities = [];

		foreach($this->getEntities() as $entity){
			if(!($entity instanceof Player) and !$entity->closed){
				$entity->saveNBT();
				$entities[] = $entity->namedtag;
			}
		}

		$nbt->Entities = new ListTag("Entities", $entities);
		$nbt->Entities->setTagType(NBT::TAG_Compound);


		$tiles = [];
		foreach($this->getTiles() as $tile){
			$tile->saveNBT();
			$tiles[] = $tile->namedtag;
		}

		$nbt->TileEntities = new ListTag("TileEntities", $tiles);
		$nbt->TileEntities->setTagType(NBT::TAG_Compound);

		$extraData = new BinaryStream();
		$extraData->putInt(count($this->getBlockExtraDataArray()));
		foreach($this->getBlockExtraDataArray() as $key => $value){
			$extraData->putInt($key);
			$extraData->putShort($value);
		}

		$nbt->ExtraData = new ByteArrayTag("ExtraData", $extraData->getBuffer());

		$writer = new NBT(NBT::BIG_ENDIAN);
		$nbt->setName("Level");
		$writer->setData(new CompoundTag("", ["Level" => $nbt]));

		return $writer->writeCompressed(ZLIB_ENCODING_DEFLATE, RegionLoader::$COMPRESSION_LEVEL);
	}

	/**
	 * @param int           $chunkX
	 * @param int           $chunkZ
	 * @param LevelProvider $provider
	 *
	 * @return Chunk
	 */
	public static function getEmptyChunk($chunkX, $chunkZ, LevelProvider $provider = null){
		try{
			$chunk = new Chunk($provider instanceof LevelProvider ? $provider : Anvil::class, null);
			$chunk->x = $chunkX;
			$chunk->z = $chunkZ;

			for($y = 0; $y < 8; ++$y){
				$chunk->sections[$y] = new EmptyChunkSection($y);
			}

			$chunk->heightMap = array_fill(0, 256, 0);
			$chunk->biomeColors = array_fill(0, 256, 0);

			$chunk->nbt->V = new ByteTag("V", 1);
			$chunk->nbt->InhabitedTime = new LongTag("InhabitedTime", 0);
			$chunk->nbt->TerrainGenerated = new ByteTag("TerrainGenerated", 0);
			$chunk->nbt->TerrainPopulated = new ByteTag("TerrainPopulated", 0);
			$chunk->nbt->LightPopulated = new ByteTag("LightPopulated", 0);

			return $chunk;
		}catch(\Throwable $e){
			return null;
		}
	}
}