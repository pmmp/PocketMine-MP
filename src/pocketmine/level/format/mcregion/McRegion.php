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

use pocketmine\level\format\FullChunk;
use pocketmine\level\format\generic\BaseLevelProvider;
use pocketmine\level\generator\Generator;
use pocketmine\level\Level;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\Byte;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Int;
use pocketmine\nbt\tag\Long;
use pocketmine\nbt\tag\String;
use pocketmine\tile\Spawnable;

use pocketmine\utils\BinaryStream;
use pocketmine\utils\ChunkException;

class McRegion extends BaseLevelProvider{

	/** @var RegionLoader[] */
	protected $regions = [];

	/** @var Chunk[] */
	protected $chunks = [];

	public static function getProviderName(){
		return "mcregion";
	}

	public static function getProviderOrder(){
		return self::ORDER_ZXY;
	}

	public static function usesChunkSection(){
		return false;
	}

	public static function isValid($path){
		$isValid = (file_exists($path . "/level.dat") and is_dir($path . "/region/"));

		if($isValid){
			$files = glob($path . "/region/*.mc*");
			foreach($files as $f){
				if(strpos($f, ".mca") !== false){ //Anvil
					$isValid = false;
					break;
				}
			}
		}

		return $isValid;
	}

	public static function generate($path, $name, $seed, $generator, array $options = []){
		if(!file_exists($path)){
			mkdir($path, 0777, true);
		}

		if(!file_exists($path . "/region")){
			mkdir($path . "/region", 0777);
		}
		//TODO, add extra details
		$levelData = new Compound("Data", [
			"hardcore" => new Byte("hardcore", 0),
			"initialized" => new Byte("initialized", 1),
			"GameType" => new Int("GameType", 0),
			"generatorVersion" => new Int("generatorVersion", 1), //2 in MCPE
			"SpawnX" => new Int("SpawnX", 128),
			"SpawnY" => new Int("SpawnY", 70),
			"SpawnZ" => new Int("SpawnZ", 128),
			"version" => new Int("version", 19133),
			"DayTime" => new Int("DayTime", 0),
			"LastPlayed" => new Long("LastPlayed", microtime(true) * 1000),
			"RandomSeed" => new Long("RandomSeed", $seed),
			"SizeOnDisk" => new Long("SizeOnDisk", 0),
			"Time" => new Long("Time", 0),
			"generatorName" => new String("generatorName", Generator::getGeneratorName($generator)),
			"generatorOptions" => new String("generatorOptions", isset($options["preset"]) ? $options["preset"] : ""),
			"LevelName" => new String("LevelName", $name),
			"GameRules" => new Compound("GameRules", [])
		]);
		$nbt = new NBT(NBT::BIG_ENDIAN);
		$nbt->setData(new Compound("", [
			"Data" => $levelData
		]));
		$buffer = $nbt->writeCompressed();
		file_put_contents($path . "level.dat", $buffer);
	}

	public static function getRegionIndex($chunkX, $chunkZ, &$x, &$z){
		$x = $chunkX >> 5;
		$z = $chunkZ >> 5;
	}

	public function requestChunkTask($x, $z){
		$chunk = $this->getChunk($x, $z, false);
		if(!($chunk instanceof Chunk)){
			throw new ChunkException("Invalid Chunk sent");
		}

		$tiles = "";

		if(count($chunk->getTiles()) > 0){
			$nbt = new NBT(NBT::LITTLE_ENDIAN);
			$list = [];
			foreach($chunk->getTiles() as $tile){
				if($tile instanceof Spawnable){
					$list[] = $tile->getSpawnCompound();
				}
			}
			$nbt->setData($list);
			$tiles = $nbt->write();
		}

		$extraData = new BinaryStream();
		$extraData->putLInt(count($chunk->getBlockExtraDataArray()));
		foreach($chunk->getBlockExtraDataArray() as $key => $value){
			$extraData->putLInt($key);
			$extraData->putLShort($value);
		}

		$ordered = $chunk->getBlockIdArray() .
			$chunk->getBlockDataArray() .
			$chunk->getBlockSkyLightArray() .
			$chunk->getBlockLightArray() .
			pack("C*", ...$chunk->getHeightMapArray()) .
			pack("N*", ...$chunk->getBiomeColorArray()) .
			$extraData->getBuffer() .
			$tiles;

		$this->getLevel()->chunkRequestCallback($x, $z, $ordered);

		return null;
	}

	public function unloadChunks(){
		foreach($this->chunks as $chunk){
			$this->unloadChunk($chunk->getX(), $chunk->getZ(), false);
		}
		$this->chunks = [];
	}

	public function getGenerator(){
		return $this->levelData["generatorName"];
	}

	public function getGeneratorOptions(){
		return ["preset" => $this->levelData["generatorOptions"]];
	}

	public function getLoadedChunks(){
		return $this->chunks;
	}

	public function isChunkLoaded($x, $z){
		return isset($this->chunks[Level::chunkHash($x, $z)]);
	}

	public function saveChunks(){
		foreach($this->chunks as $chunk){
			$this->saveChunk($chunk->getX(), $chunk->getZ());
		}
	}

	public function doGarbageCollection(){
		$limit = time() - 300;
		foreach($this->regions as $index => $region){
			if($region->lastUsed <= $limit){
				$region->close();
				unset($this->regions[$index]);
			}
		}
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
		$chunk = $this->getRegion($regionX, $regionZ)->readChunk($chunkX - $regionX * 32, $chunkZ - $regionZ * 32);
		if($chunk === null and $create){
			$chunk = $this->getEmptyChunk($chunkX, $chunkZ);
		}
		$this->level->timings->syncChunkLoadDataTimer->stopTiming();

		if($chunk !== null){
			$this->chunks[$index] = $chunk;
			return true;
		}else{
			return false;
		}
	}

	public function getEmptyChunk($chunkX, $chunkZ){
		return Chunk::getEmptyChunk($chunkX, $chunkZ, $this);
	}

	public function unloadChunk($x, $z, $safe = true){
		$chunk = isset($this->chunks[$index = Level::chunkHash($x, $z)]) ? $this->chunks[$index] : null;
		if($chunk instanceof FullChunk and $chunk->unload(false, $safe)){
			unset($this->chunks[$index]);
			return true;
		}

		return false;
	}

	public function saveChunk($x, $z){
		if($this->isChunkLoaded($x, $z)){
			$this->getRegion($x >> 5, $z >> 5)->writeChunk($this->getChunk($x, $z));

			return true;
		}

		return false;
	}

	/**
	 * @param $x
	 * @param $z
	 *
	 * @return RegionLoader
	 */
	protected function getRegion($x, $z){
		return isset($this->regions[$index = Level::chunkHash($x, $z)]) ? $this->regions[$index] : null;
	}

	/**
	 * @param int  $chunkX
	 * @param int  $chunkZ
	 * @param bool $create
	 *
	 * @return Chunk
	 */
	public function getChunk($chunkX, $chunkZ, $create = false){
		$index = Level::chunkHash($chunkX, $chunkZ);
		if(isset($this->chunks[$index])){
			return $this->chunks[$index];
		}else{
			$this->loadChunk($chunkX, $chunkZ, $create);

			return isset($this->chunks[$index]) ? $this->chunks[$index] : null;
		}
	}

	public function setChunk($chunkX, $chunkZ, FullChunk $chunk){
		if(!($chunk instanceof Chunk)){
			throw new ChunkException("Invalid Chunk class");
		}

		$chunk->setProvider($this);

		self::getRegionIndex($chunkX, $chunkZ, $regionX, $regionZ);
		$this->loadRegion($regionX, $regionZ);

		$chunk->setX($chunkX);
		$chunk->setZ($chunkZ);


		if(isset($this->chunks[$index = Level::chunkHash($chunkX, $chunkZ)]) and $this->chunks[$index] !== $chunk){
			$this->unloadChunk($chunkX, $chunkZ, false);
		}

		$this->chunks[$index] = $chunk;
	}

	public static function createChunkSection($Y){
		return null;
	}

	public function isChunkGenerated($chunkX, $chunkZ){
		if(($region = $this->getRegion($chunkX >> 5, $chunkZ >> 5)) !== null){
			return $region->chunkExists($chunkX - $region->getX() * 32, $chunkZ - $region->getZ() * 32) and $this->getChunk($chunkX - $region->getX() * 32, $chunkZ - $region->getZ() * 32, true)->isGenerated();
		}

		return false;
	}

	public function isChunkPopulated($chunkX, $chunkZ){
		$chunk = $this->getChunk($chunkX, $chunkZ);
		if($chunk !== null){
			return $chunk->isPopulated();
		}else{
			return false;
		}
	}

	protected function loadRegion($x, $z){
		if(!isset($this->regions[$index = Level::chunkHash($x, $z)])){
			$this->regions[$index] = new RegionLoader($this, $x, $z);
		}
	}

	public function close(){
		$this->unloadChunks();
		foreach($this->regions as $index => $region){
			$region->close();
			unset($this->regions[$index]);
		}
		$this->level = null;
	}
}