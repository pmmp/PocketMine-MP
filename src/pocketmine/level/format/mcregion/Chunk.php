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

use pocketmine\level\format\generic\BaseChunk;
use pocketmine\level\format\generic\EmptyChunkSection;
use pocketmine\level\format\LevelProvider;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\Byte;
use pocketmine\nbt\tag\ByteArray;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Enum;
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

		if(!isset($this->nbt->Biomes) or !($this->nbt->Biomes instanceof ByteArray)){
			$this->nbt->Biomes = new ByteArray("Biomes", str_repeat("\x01", 256));
		}

		if(!isset($this->nbt->BiomeColors) or !($this->nbt->BiomeColors instanceof IntArray)){
			$this->nbt->BiomeColors = new IntArray("BiomeColors", array_fill(0, 156, Binary::readInt("\x00\x85\xb2\x4a")));
		}

		/** @var ChunkSection[] $sections */
		$sections = [];

		$blockLight = $skyLight = $datas = $blocks = [$fill = array_fill(0, 256, ""), $fill, $fill, $fill, $fill, $fill, $fill, $fill];

		$offset = 0;
		for($i = 0; $i < 256; ++$i){
			list($blocks[0][$i], $blocks[1][$i], $blocks[2][$i], $blocks[3][$i], $blocks[4][$i], $blocks[5][$i], $blocks[6][$i], $blocks[7][$i]) = str_split(substr($this->nbt["Blocks"], $offset << 1, 128), 16);
			list($datas[0][$i], $datas[1][$i], $datas[2][$i], $datas[3][$i], $datas[4][$i], $datas[5][$i], $datas[6][$i], $datas[7][$i]) = str_split(substr($this->nbt["Data"], $offset, 64), 8);
			list($skyLight[0][$i], $skyLight[1][$i], $skyLight[2][$i], $skyLight[3][$i], $skyLight[4][$i], $skyLight[5][$i], $skyLight[6][$i], $skyLight[7][$i]) = str_split(substr($this->nbt["SkyLight"], $offset, 64), 8);
			list($blockLight[0][$i], $blockLight[1][$i], $blockLight[2][$i], $blockLight[3][$i], $blockLight[4][$i], $blockLight[5][$i], $blockLight[6][$i], $blockLight[7][$i]) = str_split(substr($this->nbt["BlockLight"], $offset, 64), 8);
			$offset += 64;
		}

		for($Y = 0; $Y < 8; ++$Y){
			$sections[$Y] = new ChunkSection(
				$Y,
				implode($blocks[$Y]),
				implode($datas[$Y]),
				implode($skyLight[$Y]),
				implode($blockLight[$Y])
			);
		}

		for($y = 0; $y < 8; ++$y){
			if(substr_count($sections[$y]->getIdArray(), "\x00") === 4096){
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