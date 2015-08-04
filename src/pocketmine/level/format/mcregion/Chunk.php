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

use pocketmine\level\format\generic\BaseFullChunk;
use pocketmine\level\format\LevelProvider;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\Byte;
use pocketmine\nbt\tag\ByteArray;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Int;
use pocketmine\nbt\tag\IntArray;
use pocketmine\nbt\tag\Long;
use pocketmine\Player;
use pocketmine\utils\Binary;
use pocketmine\utils\BinaryStream;

class Chunk extends BaseFullChunk{

	/** @var Compound */
	protected $nbt;

	public function __construct($level, Compound $nbt = null){
		if($nbt === null){
			$this->provider = $level;
			$this->nbt = new Compound("Level", []);
			return;
		}
		
		$this->nbt = $nbt;

		if(isset($this->nbt->Entities) and $this->nbt->Entities instanceof Enum){
			$this->nbt->Entities->setTagType(NBT::TAG_Compound);
		}else{
			$this->nbt->Entities = new Enum("Entities", []);
			$this->nbt->Entities->setTagType(NBT::TAG_Compound);
		}

		if(isset($this->nbt->TileEntities) and $this->nbt->TileEntities instanceof Enum){
			$this->nbt->TileEntities->setTagType(NBT::TAG_Compound);
		}else{
			$this->nbt->TileEntities = new Enum("TileEntities", []);
			$this->nbt->TileEntities->setTagType(NBT::TAG_Compound);
		}

		if(isset($this->nbt->TileTicks) and $this->nbt->TileTicks instanceof Enum){
			$this->nbt->TileTicks->setTagType(NBT::TAG_Compound);
		}else{
			$this->nbt->TileTicks = new Enum("TileTicks", []);
			$this->nbt->TileTicks->setTagType(NBT::TAG_Compound);
		}

		if(!isset($this->nbt->BiomeColors) or !($this->nbt->BiomeColors instanceof IntArray)){
			$this->nbt->BiomeColors = new IntArray("BiomeColors", array_fill(0, 256, 0));
		}

		if(!isset($this->nbt->HeightMap) or !($this->nbt->HeightMap instanceof IntArray)){
			$this->nbt->HeightMap = new IntArray("HeightMap", array_fill(0, 256, 0));
		}

		if(!isset($this->nbt->Blocks)){
			$this->nbt->Blocks = new ByteArray("Blocks", str_repeat("\x00", 32768));
		}

		if(!isset($this->nbt->Data)){
			$this->nbt->Data = new ByteArray("Data", $half = str_repeat("\x00", 16384));
			$this->nbt->SkyLight = new ByteArray("SkyLight", $half);
			$this->nbt->BlockLight = new ByteArray("BlockLight", $half);
		}

		$extraData = [];

		if(!isset($this->nbt->ExtraData) or !($this->nbt->ExtraData instanceof ByteArray)){
			$this->nbt->ExtraData = new ByteArray("ExtraData", Binary::writeInt(0));
		}else{
			$stream = new BinaryStream($this->nbt->ExtraData->getValue());
			$count = $stream->getInt();
			for($i = 0; $i < $count; ++$i){
				$key = $stream->getInt();
				$extraData[$key] = $stream->getShort(false);
			}
		}

		parent::__construct($level, $this->nbt["xPos"], $this->nbt["zPos"], $this->nbt->Blocks->getValue(), $this->nbt->Data->getValue(), $this->nbt->SkyLight->getValue(), $this->nbt->BlockLight->getValue(), $this->nbt->BiomeColors->getValue(), $this->nbt->HeightMap->getValue(), $this->nbt->Entities->getValue(), $this->nbt->TileEntities->getValue(), $extraData);

		if(isset($this->nbt->Biomes)){
			$this->checkOldBiomes($this->nbt->Biomes->getValue());
			unset($this->nbt->Biomes);
		}

		unset($this->nbt->Blocks);
		unset($this->nbt->Data);
		unset($this->nbt->SkyLight);
		unset($this->nbt->BlockLight);
		unset($this->nbt->BiomeColors);
		unset($this->nbt->HeightMap);
		unset($this->nbt->Biomes);
	}

	public function getBlockId($x, $y, $z){
		return ord($this->blocks{($x << 11) | ($z << 7) | $y});
	}

	public function setBlockId($x, $y, $z, $id){
		$this->blocks{($x << 11) | ($z << 7) | $y} = chr($id);
		$this->hasChanged = true;
	}

	public function getBlockData($x, $y, $z){
		$m = ord($this->data{($x << 10) | ($z << 6) | ($y >> 1)});
		if(($y & 1) === 0){
			return $m & 0x0F;
		}else{
			return $m >> 4;
		}
	}

	public function setBlockData($x, $y, $z, $data){
		$i = ($x << 10) | ($z << 6) | ($y >> 1);
		$old_m = ord($this->data{$i});
		if(($y & 1) === 0){
			$this->data{$i} = chr(($old_m & 0xf0) | ($data & 0x0f));
		}else{
			$this->data{$i} = chr((($data & 0x0f) << 4) | ($old_m & 0x0f));
		}
		$this->hasChanged = true;
	}

	public function getFullBlock($x, $y, $z){
		$i = ($x << 11) | ($z << 7) | $y;
		if(($y & 1) === 0){
			return (ord($this->blocks{$i}) << 4) | (ord($this->data{$i >> 1}) & 0x0F);
		}else{
			return (ord($this->blocks{$i}) << 4) | (ord($this->data{$i >> 1}) >> 4);
		}
	}

	public function getBlock($x, $y, $z, &$blockId, &$meta = null){
		$full = $this->getFullBlock($x, $y, $z);
		$blockId = $full >> 4;
		$meta = $full & 0x0f;
	}

	public function setBlock($x, $y, $z, $blockId = null, $meta = null){
		$i = ($x << 11) | ($z << 7) | $y;

		$changed = false;

		if($blockId !== null){
			$blockId = chr($blockId);
			if($this->blocks{$i} !== $blockId){
				$this->blocks{$i} = $blockId;
				$changed = true;
			}
		}

		if($meta !== null){
			$i >>= 1;
			$old_m = ord($this->data{$i});
			if(($y & 1) === 0){
				$this->data{$i} = chr(($old_m & 0xf0) | ($meta & 0x0f));
				if(($old_m & 0x0f) !== $meta){
					$changed = true;
				}
			}else{
				$this->data{$i} = chr((($meta & 0x0f) << 4) | ($old_m & 0x0f));
				if((($old_m & 0xf0) >> 4) !== $meta){
					$changed = true;
				}
			}
		}

		if($changed){
			$this->hasChanged = true;
		}

		return $changed;
	}

	public function getBlockSkyLight($x, $y, $z){
		$sl = ord($this->skyLight{($x << 10) | ($z << 6) | ($y >> 1)});
		if(($y & 1) === 0){
			return $sl & 0x0F;
		}else{
			return $sl >> 4;
		}
	}

	public function setBlockSkyLight($x, $y, $z, $level){
		$i = ($x << 10) | ($z << 6) | ($y >> 1);
		$old_sl = ord($this->skyLight{$i});
		if(($y & 1) === 0){
			$this->skyLight{$i} = chr(($old_sl & 0xf0) | ($level & 0x0f));
		}else{
			$this->skyLight{$i} = chr((($level & 0x0f) << 4) | ($old_sl & 0x0f));
		}
		$this->hasChanged = true;
	}

	public function getBlockLight($x, $y, $z){
		$l = ord($this->blockLight{($x << 10) | ($z << 6) | ($y >> 1)});
		if(($y & 1) === 0){
			return $l & 0x0F;
		}else{
			return $l >> 4;
		}
	}

	public function setBlockLight($x, $y, $z, $level){
		$i = ($x << 10) | ($z << 6) | ($y >> 1);
		$old_l = ord($this->blockLight{$i});
		if(($y & 1) === 0){
			$this->blockLight{$i} = chr(($old_l & 0xf0) | ($level & 0x0f));
		}else{
			$this->blockLight{$i} = chr((($level & 0x0f) << 4) | ($old_l & 0x0f));
		}
		$this->hasChanged = true;
	}

	public function getBlockIdColumn($x, $z){
		return substr($this->blocks, ($x << 11) + ($z << 7), 128);
	}

	public function getBlockDataColumn($x, $z){
		return substr($this->data, ($x << 10) + ($z << 6), 64);
	}

	public function getBlockSkyLightColumn($x, $z){
		return substr($this->skyLight, ($x << 10) + ($z << 6), 64);
	}

	public function getBlockLightColumn($x, $z){
		return substr($this->blockLight, ($x << 10) + ($z << 6), 64);
	}

	public function isLightPopulated(){
		return $this->nbt["LightPopulated"] > 0;
	}

	public function setLightPopulated($value = 1){
		$this->nbt->LightPopulated = new Byte("LightPopulated", $value ? 1 : 0);
		$this->hasChanged = true;
	}

	/**
	 * @return bool
	 */
	public function isPopulated(){
		return isset($this->nbt->TerrainPopulated) and $this->nbt->TerrainPopulated->getValue() > 0;
	}

	/**
	 * @param int $value
	 */
	public function setPopulated($value = 1){
		$this->nbt->TerrainPopulated = new Byte("TerrainPopulated", $value ? 1 : 0);
		$this->hasChanged = true;
	}

	/**
	 * @return bool
	 */
	public function isGenerated(){
		if(isset($this->nbt->TerrainGenerated)){
			return $this->nbt->TerrainGenerated->getValue() > 0;
		}elseif(isset($this->nbt->TerrainPopulated)){
			return $this->nbt->TerrainPopulated->getValue() > 0;
		}
		return false;
	}

	/**
	 * @param int $value
	 */
	public function setGenerated($value = 1){
		$this->nbt->TerrainGenerated = new Byte("TerrainGenerated", (int) $value);
		$this->hasChanged = true;
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

			if(!isset($chunk->Level) or !($chunk->Level instanceof Compound)){
				return null;
			}

			return new Chunk($provider instanceof LevelProvider ? $provider : McRegion::class, $chunk->Level);
		}catch(\Exception $e){
			return null;
		}
	}
	
	public static function fromFastBinary($data, LevelProvider $provider = null){

		try{
			$offset = 0;

			$chunk = new Chunk($provider instanceof LevelProvider ? $provider : McRegion::class, null);
			$chunk->provider = $provider;
			$chunk->x = Binary::readInt(substr($data, $offset, 4));
			$offset += 4;
			$chunk->z = Binary::readInt(substr($data, $offset, 4));
			$offset += 4;

			$chunk->blocks = substr($data, $offset, 32768);
			$offset += 32768;
			$chunk->data = substr($data, $offset, 16384);
			$offset += 16384;
			$chunk->skyLight = substr($data, $offset, 16384);
			$offset += 16384;
			$chunk->blockLight = substr($data, $offset, 16384);
			$offset += 16384;

			$chunk->heightMap = array_values(unpack("C*", substr($data, $offset, 256)));
			$offset += 256;
			$chunk->biomeColors = array_values(unpack("N*", substr($data, $offset, 1024)));
			$offset += 1024;

			$flags = ord($data{$offset++});

			$chunk->nbt->TerrainGenerated = new Byte("TerrainGenerated", $flags & 0b1);
			$chunk->nbt->TerrainPopulated = new Byte("TerrainPopulated", ($flags >> 1) & 0b1);
			$chunk->nbt->LightPopulated = new Byte("LightPopulated", ($flags >> 2) & 0b1);

			return $chunk;
		}catch(\Exception $e){
			return null;
		}
	}
	
	public function toFastBinary(){
		return
			Binary::writeInt($this->x) .
			Binary::writeInt($this->z) .
			$this->getBlockIdArray() .
			$this->getBlockDataArray() .
			$this->getBlockSkyLightArray() .
			$this->getBlockLightArray() .
			pack("C*", ...$this->getHeightMapArray()) .
			pack("N*", ...$this->getBiomeColorArray()) .
			chr(($this->isLightPopulated() ? 1 << 2 : 0) + ($this->isPopulated() ? 1 << 1 : 0) + ($this->isGenerated() ? 1 : 0));
	}

	public function toBinary(){
		$nbt = clone $this->getNBT();

		$nbt->xPos = new Int("xPos", $this->x);
		$nbt->zPos = new Int("zPos", $this->z);

		if($this->isGenerated()){
			$nbt->Blocks = new ByteArray("Blocks", $this->getBlockIdArray());
			$nbt->Data = new ByteArray("Data", $this->getBlockDataArray());
			$nbt->SkyLight = new ByteArray("SkyLight", $this->getBlockSkyLightArray());
			$nbt->BlockLight = new ByteArray("BlockLight", $this->getBlockLightArray());

			$nbt->BiomeColors = new IntArray("BiomeColors", $this->getBiomeColorArray());

			$nbt->HeightMap = new IntArray("HeightMap", $this->getHeightMapArray());
		}

		$entities = [];

		foreach($this->getEntities() as $entity){
			if(!($entity instanceof Player) and !$entity->closed){
				$entity->saveNBT();
				$entities[] = $entity->namedtag;
			}
		}

		$nbt->Entities = new Enum("Entities", $entities);
		$nbt->Entities->setTagType(NBT::TAG_Compound);


		$tiles = [];
		foreach($this->getTiles() as $tile){
			$tile->saveNBT();
			$tiles[] = $tile->namedtag;
		}

		$nbt->TileEntities = new Enum("TileEntities", $tiles);
		$nbt->TileEntities->setTagType(NBT::TAG_Compound);

		$extraData = new BinaryStream();
		$extraData->putInt(count($this->getBlockExtraDataArray()));
		foreach($this->getBlockExtraDataArray() as $key => $value){
			$extraData->putInt($key);
			$extraData->putShort($value);
		}

		$nbt->ExtraData = new ByteArray("ExtraData", $extraData->getBuffer());

		$writer = new NBT(NBT::BIG_ENDIAN);
		$nbt->setName("Level");
		$writer->setData(new Compound("", ["Level" => $nbt]));

		return $writer->writeCompressed(ZLIB_ENCODING_DEFLATE, RegionLoader::$COMPRESSION_LEVEL);
	}

	/**
	 * @return Compound
	 */
	public function getNBT(){
		return $this->nbt;
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
			$chunk = new Chunk($provider instanceof LevelProvider ? $provider : McRegion::class, null);
			$chunk->x = $chunkX;
			$chunk->z = $chunkZ;

			$chunk->data = str_repeat("\x00", 16384);
			$chunk->blocks = $chunk->data . $chunk->data;
			$chunk->skyLight = str_repeat("\xff", 16384);
			$chunk->blockLight = $chunk->data;

			$chunk->heightMap = array_fill(0, 256, 0);
			$chunk->biomeColors = array_fill(0, 256, 0);

			$chunk->nbt->V = new Byte("V", 1);
			$chunk->nbt->InhabitedTime = new Long("InhabitedTime", 0);
			$chunk->nbt->TerrainGenerated = new Byte("TerrainGenerated", 0);
			$chunk->nbt->TerrainPopulated = new Byte("TerrainPopulated", 0);
			$chunk->nbt->LightPopulated = new Byte("LightPopulated", 0);

			return $chunk;
		}catch(\Exception $e){
			return null;
		}
	}
}