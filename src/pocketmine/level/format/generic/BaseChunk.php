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

namespace pocketmine\level\format\generic;

use pocketmine\level\format\Chunk;
use pocketmine\level\format\ChunkSection;
use pocketmine\level\format\LevelProvider;
use pocketmine\nbt\tag\Compound;
use pocketmine\utils\Binary;
use pocketmine\utils\ChunkException;

abstract class BaseChunk extends BaseFullChunk implements Chunk{

	/** @var ChunkSection[] */
	protected $sections = [];

	/**
	 * @param LevelProvider  $provider
	 * @param int            $x
	 * @param int            $z
	 * @param ChunkSection[] $sections
	 * @param int[]          $biomeColors
	 * @param int[]          $heightMap
	 * @param Compound[]     $entities
	 * @param Compound[]     $tiles
	 *
	 * @throws ChunkException
	 */
	protected function __construct($provider, $x, $z, array $sections, array $biomeColors = [], array $heightMap = [], array $entities = [], array $tiles = []){
		$this->provider = $provider;
		$this->x = (int) $x;
		$this->z = (int) $z;
		foreach($sections as $Y => $section){
			if($section instanceof ChunkSection){
				$this->sections[$Y] = $section;
			}else{
				throw new ChunkException("Received invalid ChunkSection instance");
			}

			if($Y >= self::SECTION_COUNT){
				throw new ChunkException("Invalid amount of chunks");
			}
		}

		if(count($biomeColors) === 256){
			$this->biomeColors = $biomeColors;
		}else{
			$this->biomeColors = array_fill(0, 256, Binary::readInt("\xff\x00\x00\x00"));
		}

		if(count($heightMap) === 256){
			$this->heightMap = $heightMap;
		}else{
			$this->heightMap = array_fill(0, 256, 127);
		}

		$this->NBTtiles = $tiles;
		$this->NBTentities = $entities;
	}

	public function getBlock($x, $y, $z, &$blockId, &$meta = null){
		$full = $this->sections[$y >> 4]->getFullBlock($x, $y & 0x0f, $z);
		$blockId = $full >> 4;
		$meta = $full & 0x0f;
	}

	public function getFullBlock($x, $y, $z){
		return $this->sections[$y >> 4]->getFullBlock($x, $y & 0x0f, $z);
	}

	public function setBlock($x, $y, $z, $blockId = null, $meta = null){
		try{
			$this->hasChanged = true;
			return $this->sections[$y >> 4]->setBlock($x, $y & 0x0f, $z, $blockId & 0xff, $meta & 0x0f);
		}catch(ChunkException $e){
			$level = $this->getProvider();
			$this->setInternalSection($Y = $y >> 4, $level::createChunkSection($Y));
			return $this->sections[$y >> 4]->setBlock($x, $y & 0x0f, $z, $blockId & 0xff, $meta & 0x0f);
		}
	}

	public function getBlockId($x, $y, $z){
		return $this->sections[$y >> 4]->getBlockId($x, $y & 0x0f, $z);
	}

	public function setBlockId($x, $y, $z, $id){
		try{
			$this->sections[$y >> 4]->setBlockId($x, $y & 0x0f, $z, $id);
			$this->hasChanged = true;
		}catch(ChunkException $e){
			$level = $this->getProvider();
			$this->setInternalSection($Y = $y >> 4, $level::createChunkSection($Y));
			$this->setBlockId($x, $y, $z, $id);
		}
	}

	public function getBlockData($x, $y, $z){
		return $this->sections[$y >> 4]->getBlockData($x, $y & 0x0f, $z);
	}

	public function setBlockData($x, $y, $z, $data){
		try{
			$this->sections[$y >> 4]->setBlockData($x, $y & 0x0f, $z, $data);
			$this->hasChanged = true;
		}catch(ChunkException $e){
			$level = $this->getProvider();
			$this->setInternalSection($Y = $y >> 4, $level::createChunkSection($Y));
			$this->setBlockData($x, $y, $z, $data);
		}
	}

	public function getBlockSkyLight($x, $y, $z){
		return $this->sections[$y >> 4]->getBlockSkyLight($x, $y & 0x0f, $z);
	}

	public function setBlockSkyLight($x, $y, $z, $data){
		try{
			$this->sections[$y >> 4]->setBlockSkyLight($x, $y & 0x0f, $z, $data);
			$this->hasChanged = true;
		}catch(ChunkException $e){
			$level = $this->getProvider();
			$this->setInternalSection($Y = $y >> 4, $level::createChunkSection($Y));
			$this->setBlockSkyLight($x, $y, $z, $data);
		}
	}

	public function getBlockLight($x, $y, $z){
		return $this->sections[$y >> 4]->getBlockLight($x, $y & 0x0f, $z);
	}

	public function setBlockLight($x, $y, $z, $data){
		try{
			$this->sections[$y >> 4]->setBlockLight($x, $y & 0x0f, $z, $data);
			$this->hasChanged = true;
		}catch(ChunkException $e){
			$level = $this->getProvider();
			$this->setInternalSection($Y = $y >> 4, $level::createChunkSection($Y));
			$this->setBlockLight($x, $y, $z, $data);
		}
	}

	public function getBlockIdColumn($x, $z){
		$column = "";
		for($y = 0; $y < Chunk::SECTION_COUNT; ++$y){
			$column .= $this->sections[$y]->getBlockIdColumn($x, $z);
		}

		return $column;
	}

	public function getBlockDataColumn($x, $z){
		$column = "";
		for($y = 0; $y < Chunk::SECTION_COUNT; ++$y){
			$column .= $this->sections[$y]->getBlockDataColumn($x, $z);
		}

		return $column;
	}

	public function getBlockSkyLightColumn($x, $z){
		$column = "";
		for($y = 0; $y < Chunk::SECTION_COUNT; ++$y){
			$column .= $this->sections[$y]->getBlockSkyLightColumn($x, $z);
		}

		return $column;
	}

	public function getBlockLightColumn($x, $z){
		$column = "";
		for($y = 0; $y < Chunk::SECTION_COUNT; ++$y){
			$column .= $this->sections[$y]->getBlockLightColumn($x, $z);
		}

		return $column;
	}

	public function isSectionEmpty($fY){
		return $this->sections[(int) $fY] instanceof EmptyChunkSection;
	}

	public function getSection($fY){
		return $this->sections[(int) $fY];
	}

	public function setSection($fY, ChunkSection $section){
		if(substr_count($section->getIdArray(), "\x00") === 4096 and substr_count($section->getDataArray(), "\x00") === 2048){
			$this->sections[(int) $fY] = new EmptyChunkSection($fY);
		}else{
			$this->sections[(int) $fY] = $section;
		}
		$this->hasChanged = true;
	}

	private function setInternalSection($fY, ChunkSection $section){
		$this->sections[(int) $fY] = $section;
		$this->hasChanged = true;
	}

	public function load($generate = true){
		return $this->getProvider() === null ? false : $this->getProvider()->getChunk($this->getX(), $this->getZ(), true) instanceof Chunk;
	}

	public function getBlockIdArray(){
		$blocks = "";
		for($y = 0; $y < Chunk::SECTION_COUNT; ++$y){
			$blocks .= $this->sections[$y]->getIdArray();
		}

		return $blocks;
	}

	public function getBlockDataArray(){
		$data = "";
		for($y = 0; $y < Chunk::SECTION_COUNT; ++$y){
			$data .= $this->sections[$y]->getDataArray();
		}

		return $data;
	}

	public function getBlockSkyLightArray(){
		$skyLight = "";
		for($y = 0; $y < Chunk::SECTION_COUNT; ++$y){
			$skyLight .= $this->sections[$y]->getSkyLightArray();
		}

		return $skyLight;
	}

	public function getBlockLightArray(){
		$blockLight = "";
		for($y = 0; $y < Chunk::SECTION_COUNT; ++$y){
			$blockLight .= $this->sections[$y]->getLightArray();
		}

		return $blockLight;
	}

	/**
	 * @return ChunkSection[]
	 */
	public function getSections(){
		return $this->sections;
	}

}