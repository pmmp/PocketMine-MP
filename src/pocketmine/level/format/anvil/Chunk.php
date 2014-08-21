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
use pocketmine\nbt\tag\Byte;
use pocketmine\nbt\tag\ByteArray;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Int;
use pocketmine\nbt\tag\IntArray;
use pocketmine\Player;
use pocketmine\utils\Binary;

class Chunk extends BaseChunk{

	/** @var Compound */
	protected $nbt;

	public function __construct($level, Compound $nbt){
		$this->nbt = $nbt;

		if(!isset($this->nbt->Entities) or !($this->nbt->Entities instanceof Enum)){
			$this->nbt->Entities = new Enum("Entities", []);
			$this->nbt->Entities->setTagType(NBT::TAG_Compound);
		}

		if(!isset($this->nbt->TileEntities) or !($this->nbt->TileEntities instanceof Enum)){
			$this->nbt->TileEntities = new Enum("TileEntities", []);
			$this->nbt->TileEntities->setTagType(NBT::TAG_Compound);
		}

		if(!isset($this->nbt->TileTicks) or !($this->nbt->TileTicks instanceof Enum)){
			$this->nbt->TileTicks = new Enum("TileTicks", []);
			$this->nbt->TileTicks->setTagType(NBT::TAG_Compound);
		}

		if(!isset($this->nbt->Sections) or !($this->nbt->Sections instanceof Enum)){
			$this->nbt->Sections = new Enum("Sections", []);
			$this->nbt->Sections->setTagType(NBT::TAG_Compound);
		}

		if(!isset($this->nbt->Biomes) or !($this->nbt->Biomes instanceof ByteArray)){
			$this->nbt->Biomes = new ByteArray("Biomes", str_repeat("\x01", 256));
		}

		if(!isset($this->nbt->BiomeColors) or !($this->nbt->BiomeColors instanceof IntArray)){
			$this->nbt->BiomeColors = new IntArray("BiomeColors", array_fill(0, 256, Binary::readInt("\x00\x85\xb2\x4a")));
		}

		$sections = [];
		foreach($this->nbt->Sections as $section){
			if($section instanceof Compound){
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

		parent::__construct($level, $this->nbt["xPos"], $this->nbt["zPos"], $sections, $this->nbt->Biomes->getValue(), $this->nbt->BiomeColors->getValue(), $this->nbt->Entities->getValue(), $this->nbt->TileEntities->getValue());

		unset($this->nbt->Sections);
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
	 * @return Compound
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
		$nbt->readCompressed($data, ZLIB_ENCODING_DEFLATE);
		$chunk = $nbt->getData();

		if(!isset($chunk->Level) or !($chunk->Level instanceof Compound)){
			return null;
		}

		return new Chunk($provider instanceof LevelProvider ? $provider : "pocketmine\\level\\format\\anvil\\Anvil", $chunk->Level);
	}

	public function toBinary(){
		$nbt = clone $this->getNBT();

		$nbt->xPos = new Int("xPos", $this->x);
		$nbt->zPos = new Int("zPos", $this->z);

		$nbt->Sections = new Enum("Sections", []);
		$nbt->Sections->setTagType(NBT::TAG_Compound);
		foreach($this->getSections() as $section){
			if($section instanceof EmptyChunkSection){
				continue;
			}
			$nbt->Sections[$section->getY()] = new Compound(null, [
				"Y" => new Byte("Y", $section->getY()),
				"Blocks" => new ByteArray("Blocks", $section->getIdArray()),
				"Data" => new ByteArray("Data", $section->getDataArray()),
				"BlockLight" => new ByteArray("BlockLight", $section->getLightArray()),
				"SkyLight" => new ByteArray("SkyLight", $section->getSkyLightArray())
			]);
		}

		$nbt->Biomes = new ByteArray("Biomes", $this->getBiomeIdArray());
		$nbt->BiomeColors = new IntArray("BiomeColors", $this->getBiomeColorArray());

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
}