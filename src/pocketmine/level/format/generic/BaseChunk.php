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

use pocketmine\entity\DroppedItem;
use pocketmine\entity\Entity;
use pocketmine\level\format\Chunk;
use pocketmine\level\format\ChunkSection;
use pocketmine\level\format\LevelProvider;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\String;
use pocketmine\tile\Chest;
use pocketmine\tile\Furnace;
use pocketmine\tile\Sign;
use pocketmine\tile\Tile;
use pocketmine\utils\Binary;

abstract class BaseChunk extends BaseFullChunk implements Chunk{

	/** @var ChunkSection[] */
	protected $sections = [];

	/** @var Entity[] */
	protected $entities = [];

	/** @var Tile[] */
	protected $tiles = [];

	/** @var string */
	protected $biomeIds;

	/** @var int[256] */
	protected $biomeColors;

	/** @var LevelProvider */
	protected $level;

	protected $x;
	protected $z;

	/**
	 * @param LevelProvider  $provider
	 * @param int            $x
	 * @param int            $z
	 * @param ChunkSection[] $sections
	 * @param string         $biomeIds
	 * @param int[]          $biomeColors
	 * @param Compound[]     $entities
	 * @param Compound[]     $tiles
	 *
	 * @throws \Exception
	 */
	protected function __construct($provider, $x, $z, array $sections, $biomeIds = null, array $biomeColors = [], array $entities = [], array $tiles = []){
		$this->provider = $provider;
		$this->x = (int) $x;
		$this->z = (int) $z;
		foreach($sections as $Y => $section){
			if($section instanceof ChunkSection){
				$this->sections[$Y] = $section;
			}else{
				trigger_error("Received invalid ChunkSection instance", E_USER_ERROR);
				throw new \Exception("Received invalid ChunkSection instance");
			}

			if($Y >= self::SECTION_COUNT){
				throw new \Exception("Invalid amount of chunks");
			}
		}

		if(strlen($biomeIds) === 256){
			$this->biomeIds = $biomeIds;
		}else{
			$this->biomeIds = str_repeat("\x01", 256);
		}

		if(count($biomeColors) === 256){
			$this->biomeColors = $biomeColors;
		}else{
			$this->biomeColors = array_fill(0, 256, Binary::readInt("\x00\x85\xb2\x4a"));
		}

		if($this->provider instanceof LevelProvider){
			$this->provider->getLevel()->timings->syncChunkLoadEntitiesTimer->startTiming();
			foreach($entities as $nbt){
				if($nbt instanceof Compound){
					if(!isset($nbt->id)){
						continue;
					}

					if($nbt->id instanceof String){ //New format
						switch($nbt["id"]){
							case "Item":
								(new DroppedItem($this, $nbt))->spawnToAll();
								break;
						}
					}else{ //Old format

					}
				}
			}
			$this->getProvider()->getLevel()->timings->syncChunkLoadEntitiesTimer->stopTiming();

			$this->getProvider()->getLevel()->timings->syncChunkLoadTileEntitiesTimer->startTiming();
			foreach($tiles as $nbt){
				if($nbt instanceof Compound){
					if(!isset($nbt->id)){
						continue;
					}
					switch($nbt["id"]){
						case Tile::CHEST:
							new Chest($this, $nbt);
							break;
						case Tile::FURNACE:
							new Furnace($this, $nbt);
							break;
						case Tile::SIGN:
							new Sign($this, $nbt);
							break;
					}
				}
			}
			$this->getProvider()->getLevel()->timings->syncChunkLoadTileEntitiesTimer->stopTiming();
		}
	}

	public function getBlock($x, $y, $z, &$blockId, &$meta = null){
		$this->sections[$y >> 4]->getBlock($x, $y & 0x0f, $z, $blockId, $meta);
	}

	public function setBlock($x, $y, $z, $blockId = null, $meta = null){
		try{
			return $this->sections[$y >> 4]->setBlock($x, $y & 0x0f, $z, $blockId & 0xff, $meta & 0x0f);
		}catch(\Exception $e){
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
		}catch(\Exception $e){
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
		}catch(\Exception $e){
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
			$this->sections[$y >> 4]->getBlockSkyLight($x, $y & 0x0f, $z, $data);
		}catch(\Exception $e){
			$level = $this->getProvider();
			$this->setInternalSection($Y = $y >> 4, $level::createChunkSection($Y));
			$this->setBlockSkyLight($x, $y, $z, $data);
		}
	}

	public function getBlockLight($x, $y, $z){
		return $this->sections[$y >> 4]->getBlockSkyLight($x, $y & 0x0f, $z);
	}

	public function setBlockLight($x, $y, $z, $data){
		try{
			$this->sections[$y >> 4]->getBlockSkyLight($x, $y & 0x0f, $z, $data);
		}catch(\Exception $e){
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
	}

	private function setInternalSection($fY, ChunkSection $section){
		$this->sections[(int) $fY] = $section;
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