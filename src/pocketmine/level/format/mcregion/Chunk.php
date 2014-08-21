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
use pocketmine\Player;
use pocketmine\utils\Binary;

class Chunk extends BaseFullChunk{

	/** @var Compound */
	protected $nbt;

	public function __construct($level, Compound $nbt){
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

		if(!isset($this->nbt->Biomes) or !($this->nbt->Biomes instanceof ByteArray)){
			$this->nbt->Biomes = new ByteArray("Biomes", str_repeat("\x01", 256));
		}

		if(!isset($this->nbt->BiomeColors) or !($this->nbt->BiomeColors instanceof IntArray)){
			$this->nbt->BiomeColors = new IntArray("BiomeColors", array_fill(0, 156, Binary::readInt("\x00\x85\xb2\x4a")));
		}

		if(!isset($this->nbt->Blocks)){
			$this->nbt->Blocks = new ByteArray("Blocks", str_repeat("\x00", 32768));
		}

		if(!isset($this->nbt->Data)){
			$this->nbt->Data = new ByteArray("Data", $half = str_repeat("\x00", 16384));
			$this->nbt->SkyLight = new ByteArray("SkyLight", $half);
			$this->nbt->BlockLight = new ByteArray("BlockLight", $half);
		}

		parent::__construct($level, $this->nbt["xPos"], $this->nbt["zPos"], $this->nbt["Blocks"], $this->nbt["Data"], $this->nbt["SkyLight"], $this->nbt["BlockLight"], $this->nbt->Biomes->getValue(), $this->nbt->BiomeColors->getValue(), $this->nbt->Entities->getValue(), $this->nbt->TileEntities->getValue());
		unset($this->nbt->Blocks);
		unset($this->nbt->Data);
		unset($this->nbt->SkyLight);
		unset($this->nbt->BlockLight);
	}

	public function getBlockId($x, $y, $z){
		return ord($this->blocks{($x << 11) + ($z << 7) + $y});
	}

	public function setBlockId($x, $y, $z, $id){
		$this->blocks{($x << 11) + ($z << 7) + $y} = chr($id);
	}

	public function getBlockData($x, $y, $z){
		$m = ord($this->data{($x << 10) + ($z << 6) + ($y >> 1)});
		if(($y & 1) === 0){
			return $m & 0x0F;
		}else{
			return $m >> 4;
		}
	}

	public function setBlockData($x, $y, $z, $data){
		$i = ($x << 10) + ($z << 6) + ($y >> 1);
		$old_m = ord($this->data{$i});
		if(($y & 1) === 0){
			$this->data{$i} = chr(($old_m & 0xf0) | ($data & 0x0f));
		}else{
			$this->data{$i} = chr((($data & 0x0f) << 4) | ($old_m & 0x0f));
		}
	}

	public function getBlock($x, $y, $z, &$blockId, &$meta = null){
		$i = ($x << 11) + ($z << 7) + $y;
		$blockId = ord($this->blocks{$i});
		$m = ord($this->data{$i >> 1});
		if(($y & 1) === 0){
			$meta = $m & 0x0F;
		}else{
			$meta = $m >> 4;
		}
	}

	public function setBlock($x, $y, $z, $blockId = null, $meta = null){
		$i = ($x << 11) + ($z << 7) + $y;

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

		return $changed;
	}

	public function getBlockSkyLight($x, $y, $z){
		$sl = ord($this->skyLight{($x << 10) + ($z << 6) + ($y >> 1)});
		if(($y & 1) === 0){
			return $sl & 0x0F;
		}else{
			return $sl >> 4;
		}
	}

	public function setBlockSkyLight($x, $y, $z, $level){
		$i = ($x << 10) + ($z << 6) + ($y >> 1);
		$old_sl = ord($this->skyLight{$i});
		if(($y & 1) === 0){
			$this->skyLight{$i} = chr(($old_sl & 0xf0) | ($level & 0x0f));
		}else{
			$this->skyLight{$i} = chr((($level & 0x0f) << 4) | ($old_sl & 0x0f));
		}
	}

	public function getBlockLight($x, $y, $z){
		$l = ord($this->blockLight{($x << 10) + ($z << 6) + ($y >> 1)});
		if(($y & 1) === 0){
			return $l & 0x0F;
		}else{
			return $l >> 4;
		}
	}

	public function setBlockLight($x, $y, $z, $level){
		$i = ($x << 10) + ($z << 6) + ($y >> 1);
		$old_l = ord($this->blockLight{$i});
		if(($y & 1) === 0){
			$this->blockLight{$i} = chr(($old_l & 0xf0) | ($level & 0x0f));
		}else{
			$this->blockLight{$i} = chr((($level & 0x0f) << 4) | ($old_l & 0x0f));
		}
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
		$this->nbt->TerrainPopulated = new Byte("TerrainPopulated", $value);
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
		$this->nbt->TerrainGenerated = new Byte("TerrainGenerated", $value);
	}

	/**
	 * @param string        $data
	 * @param LevelProvider $provider
	 *
	 * @return Chunk
	 */
	public static function fromBinary($data, LevelProvider $provider = null){
		$nbt = new NBT(NBT::BIG_ENDIAN);
		$nbt->readCompressed($data, ZLIB_ENCODING_DEFLATE);
		$chunk = $nbt->getData();

		if(!isset($chunk->Level) or !($chunk->Level instanceof Compound)){
			return null;
		}

		return new Chunk($provider instanceof LevelProvider ? $provider : "pocketmine\\level\\format\\mcregion\\McRegion", $chunk->Level);
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

			$nbt->Biomes = new ByteArray("Biomes", $this->getBiomeIdArray());
			$nbt->BiomeColors = new IntArray("BiomeColors", $this->getBiomeColorArray());
		}

		$entities = [];

		foreach($this->getEntities() as $entity){
			if(!($entity instanceof Player) and $entity->closed !== true){
				$entity->saveNBT();
				$entities[] = $entity->namedtag;
			}
		}

		$nbt->Entities = new Enum("Entities", $entities);
		$nbt->Entities->setTagType(NBT::TAG_Compound);


		$tiles = [];
		foreach($this->getTiles() as $tile){
			if($tile->closed !== true){
				$tile->saveNBT();
				$tiles[] = $tile->namedtag;
			}
		}

		$nbt->Entities = new Enum("TileEntities", $tiles);
		$nbt->Entities->setTagType(NBT::TAG_Compound);
		$writer = new NBT(NBT::BIG_ENDIAN);
		$nbt->setName("Level");
		$writer->setData(new Compound("", array("Level" => $nbt)));

		return $writer->writeCompressed(ZLIB_ENCODING_DEFLATE, RegionLoader::$COMPRESSION_LEVEL);
	}

	/**
	 * @return Compound
	 */
	public function getNBT(){
		return $this->nbt;
	}
}