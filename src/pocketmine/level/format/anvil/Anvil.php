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

use pocketmine\level\format\generic\BaseLevelProvider;
use pocketmine\level\format\mcregion\McRegion;
use pocketmine\level\format\SimpleChunk;
use pocketmine\level\generator\Generator;
use pocketmine\level\Level;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\Byte;
use pocketmine\nbt\tag\ByteArray;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Int;
use pocketmine\nbt\tag\Long;
use pocketmine\nbt\tag\String;
use pocketmine\Player;

class Anvil extends McRegion{

	/** @var RegionLoader[] */
	protected $regions = [];

	/** @var Chunk[] */
	protected $chunks = [];

	public static function getProviderName(){
		return "anvil";
	}

	public static function getProviderOrder(){
		return self::ORDER_YZX;
	}

	public static function usesChunkSection(){
		return true;
	}

	public static function isValid($path){
		$isValid = (file_exists($path . "/level.dat") and is_dir($path . "/region/"));

		if($isValid){
			$files = glob($path . "/region/*.mc*");
			foreach($files as $f){
				if(strpos($f, ".mcr") !== false){ //McRegion
					$isValid = false;
					break;
				}
			}
		}
		return $isValid;
	}

	public function loadChunk($chunkX, $chunkZ, $create = false){
		$index = Level::chunkHash($chunkX, $chunkZ);
		if(isset($this->chunks[$index])){
			return true;
		}
		$regionX = $regionZ = null;
		self::getRegionIndex($chunkX, $chunkZ, $regionX, $regionZ);
		$this->loadRegion($regionX, $regionZ);

		$this->level->timings->syncChunkLoadDataTimer->startTiming();
		$chunk = $this->getRegion($regionX, $regionZ)->readChunk($chunkX - $regionX * 32, $chunkZ - $regionZ * 32, $create); //generate empty chunk if not loaded
		$this->level->timings->syncChunkLoadDataTimer->stopTiming();

		if($chunk instanceof Chunk){
			$this->chunks[$index] = $chunk;
		}else{
			return false;
		}
	}

	/**
	 * @param int  $chunkX
	 * @param int  $chunkZ
	 * @param bool $create
	 *
	 * @return Chunk
	 */
	public function getChunk($chunkX, $chunkZ, $create = false){
		return parent::getChunk($chunkX, $chunkZ, $create);
	}

	public function setChunk($chunkX, $chunkZ, SimpleChunk $chunk){
		if($chunk->isGenerated() === false){
			$this->unloadChunk($chunkX, $chunkZ, false);
			$regionX = $regionZ = null;
			self::getRegionIndex($chunkX, $chunkZ, $regionX, $regionZ);
			$this->loadRegion($regionX, $regionZ);
			$region = $this->getRegion($regionX, $regionZ);
			$region->removeChunk($chunkX - $region->getX() * 32, $chunkZ - $region->getZ() * 32);
			$this->loadChunk($chunkX, $chunkZ);
		}else{
			$newChunk = $this->getChunk($chunkX, $chunkZ, true);
			for($y = 0; $y < 8; ++$y){
				$section = new ChunkSection(new Compound(null, [
					"Y" => new Byte("Y", $y),
					"Blocks" => new ByteArray("Blocks", $chunk->getSectionIds($y)),
					"Data" => new ByteArray("Data", $chunk->getSectionData($y)),
					"SkyLight" => new ByteArray("SkyLight", str_repeat("\xff", 2048)), //TODO
					"BlockLight" => new ByteArray("BlockLight", str_repeat("\x00", 2048)) //TODO
				]));
				$newChunk->setSection($y, $section);
			}
			if($chunk->isPopulated()){
				$newChunk->setPopulated(1);
			}
			$this->chunks[Level::chunkHash($chunkX, $chunkZ)] = $newChunk;
			$this->saveChunk($chunkX, $chunkZ);
		}
	}

	public function createChunkSection($Y){
		return new ChunkSection(new Compound(null, [
			"Y" => new Byte("Y", $Y),
			"Blocks" => new ByteArray("Blocks", str_repeat("\xff", 4096)),
			"Data" => new ByteArray("Data", $half = str_repeat("\xff", 2048)),
			"SkyLight" => new ByteArray("SkyLight", $half),
			"BlockLight" => new ByteArray("BlockLight", $half)
		]));
	}

	public function isChunkGenerated($chunkX, $chunkZ){
		if(($region = $this->getRegion($chunkX >> 5, $chunkZ >> 5)) instanceof RegionLoader){
			return $region->chunkExists($chunkX - $region->getX() * 32, $chunkZ - $region->getZ() * 32);
		}

		return false;
	}

	protected function loadRegion($x, $z){
		$index = $x . ":" . $z;
		if(isset($this->regions[$index])){
			return true;
		}

		$this->regions[$index] = new RegionLoader($this, $x, $z);

		return true;
	}
}