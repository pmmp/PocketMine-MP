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
use pocketmine\utils\Binary;

class Chunk extends BaseChunk{

	/** @var Compound */
	protected $nbt;

	public function __construct(LevelProvider $level, Compound $nbt){
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

		if(isset($this->nbt->Sections) and $this->nbt->Sections instanceof Enum){
			$this->nbt->Sections->setTagType(NBT::TAG_Compound);
		}else{
			$this->nbt->Sections = new Enum("Sections", []);
			$this->nbt->Sections->setTagType(NBT::TAG_Compound);
		}

		if(!isset($this->nbt->Biomes) or !($this->nbt->Biomes instanceof ByteArray)){
			$this->nbt->Biomes = new ByteArray("Biomes", str_repeat("\x01", 256));
		}

		if(!isset($this->nbt->BiomeColors) or !($this->nbt->BiomeColors instanceof IntArray)){
			$this->nbt->BiomeColors = new IntArray("BiomeColors", array_fill(0, 156, Binary::readInt("\x01\x85\xb2\x4a")));
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

	public function getChunkSnapshot($includeMaxBlockY = true, $includeBiome = false, $includeBiomeTemp = false){
		$blockId = "";
		$blockData = "";
		$blockSkyLight = "";
		$blockLight = "";
		$emptySections = [false, false, false, false, false, false, false, false];

		$emptyBlocks = str_repeat("\x00", 4096);
		$emptyHalf = str_repeat("\x00", 2048);

		foreach($this->sections as $i => $section){
			if($section instanceof EmptyChunkSection){
				$blockId .= $emptyBlocks;
				$blockData .= $emptyHalf;
				$blockSkyLight .= $emptyHalf;
				$blockLight .= $emptyHalf;
				$emptySections[$i] = true;
			}else{
				$blockId .= $section->getIdArray();
				$blockData .= $section->getDataArray();
				$blockSkyLight .= $section->getSkyLightArray();
				$blockLight .= $section->getLightArray();
			}
		}

		//TODO: maxBlockY, biomeMap, biomeTemp

		//TODO: time
		return new ChunkSnapshot($this->getX(), $this->getZ(), $this->getLevel()->getName(), 0 /*$this->getLevel()->getTime()*/, $blockId, $blockData, $blockSkyLight, $blockLight, $emptySections, null, null, null, null);
	}

	/**
	 * @return Compound
	 */
	public function getNBT(){
		return $this->nbt;
	}
}