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

declare(strict_types = 1);

namespace pocketmine\level\format\io\region;

use pocketmine\level\format\Chunk;
use pocketmine\level\format\io\BaseLevelProvider;
use pocketmine\level\format\io\ChunkException;
use pocketmine\level\format\io\ChunkUtils;
use pocketmine\level\format\SubChunk;
use pocketmine\level\generator\Generator;
use pocketmine\level\Level;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\{
	ByteArrayTag, ByteTag, CompoundTag, IntArrayTag, IntTag, ListTag, LongTag, StringTag
};
use pocketmine\Player;
use pocketmine\utils\MainLogger;

class McRegion extends BaseLevelProvider{

	const REGION_FILE_EXTENSION = "mcr";

	/** @var RegionLoader[] */
	protected $regions = [];

	/** @var Chunk[] */
	protected $chunks = [];

	/**
	 * @param Chunk $chunk
	 *
	 * @return string
	 */
	public function nbtSerialize(Chunk $chunk) : string{
		$nbt = new CompoundTag("Level", []);
		$nbt->xPos = new IntTag("xPos", $chunk->getX());
		$nbt->zPos = new IntTag("zPos", $chunk->getZ());

		$nbt->V = new ByteTag("V", 0); //guess
		$nbt->LastUpdate = new LongTag("LastUpdate", 0); //TODO
		$nbt->InhabitedTime = new LongTag("InhabitedTime", 0); //TODO
		$nbt->TerrainPopulated = new ByteTag("TerrainPopulated", $chunk->isPopulated());
		$nbt->LightPopulated = new ByteTag("LightPopulated", $chunk->isLightPopulated());

		$ids = "";
		$data = "";
		$skyLight = "";
		$blockLight = "";
		$subChunks = $chunk->getSubChunks();
		for($x = 0; $x < 16; ++$x){
			for($z = 0; $z < 16; ++$z){
				for($y = 0; $y < 8; ++$y){
					$subChunk = $subChunks[$y];
					$ids .= $subChunk->getBlockIdColumn($x, $z);
					$data .= $subChunk->getBlockDataColumn($x, $z);
					$skyLight .= $subChunk->getSkyLightColumn($x, $z);
					$blockLight .= $subChunk->getBlockLightColumn($x, $z);
				}
			}
		}

		$nbt->Blocks = new ByteArrayTag("Blocks", $ids);
		$nbt->Data = new ByteArrayTag("Data", $data);
		$nbt->SkyLight = new ByteArrayTag("SkyLight", $skyLight);
		$nbt->BlockLight = new ByteArrayTag("BlockLight", $blockLight);

		$nbt->Biomes = new ByteArrayTag("Biomes", $chunk->getBiomeIdArray());
		$nbt->HeightMap = new ByteArrayTag("HeightMap", pack("C*", ...$chunk->getHeightMapArray()));

		$entities = [];

		foreach($chunk->getEntities() as $entity){
			if(!($entity instanceof Player) and !$entity->closed){
				$entity->saveNBT();
				$entities[] = $entity->namedtag;
			}
		}

		$nbt->Entities = new ListTag("Entities", $entities);
		$nbt->Entities->setTagType(NBT::TAG_Compound);

		$tiles = [];
		foreach($chunk->getTiles() as $tile){
			$tile->saveNBT();
			$tiles[] = $tile->namedtag;
		}

		$nbt->TileEntities = new ListTag("TileEntities", $tiles);
		$nbt->TileEntities->setTagType(NBT::TAG_Compound);

		//TODO: TileTicks

		$writer = new NBT(NBT::BIG_ENDIAN);
		$nbt->setName("Level");
		$writer->setData(new CompoundTag("", ["Level" => $nbt]));

		return $writer->writeCompressed(ZLIB_ENCODING_DEFLATE, RegionLoader::$COMPRESSION_LEVEL);
	}

	/**
	 * @param string $data
	 *
	 * @return Chunk|null
	 */
	public function nbtDeserialize(string $data){
		$nbt = new NBT(NBT::BIG_ENDIAN);
		try{
			$nbt->readCompressed($data, ZLIB_ENCODING_DEFLATE);

			$chunk = $nbt->getData();

			if(!isset($chunk->Level) or !($chunk->Level instanceof CompoundTag)){
				throw new ChunkException("Invalid NBT format");
			}

			$chunk = $chunk->Level;

			$subChunks = [];
			$fullIds = isset($chunk->Blocks) ? $chunk->Blocks->getValue() : str_repeat("\x00", 32768);
			$fullData = isset($chunk->Data) ? $chunk->Data->getValue() : (str_repeat("\x00", 16384));
			$fullSkyLight = isset($chunk->SkyLight) ? $chunk->SkyLight->getValue() : str_repeat("\xff", 16384);
			$fullBlockLight = isset($chunk->BlockLight) ? $chunk->BlockLight->getValue() : (str_repeat("\x00", 16384));

			for($y = 0; $y < 8; ++$y){
				$offset = ($y << 4);
				$ids = "";
				for($i = 0; $i < 256; ++$i){
					$ids .= substr($fullIds, $offset, 16);
					$offset += 128;
				}
				$data = "";
				$offset = ($y << 3);
				for($i = 0; $i < 256; ++$i){
					$data .= substr($fullData, $offset, 8);
					$offset += 64;
				}
				$skyLight = "";
				$offset = ($y << 3);
				for($i = 0; $i < 256; ++$i){
					$skyLight .= substr($fullSkyLight, $offset, 8);
					$offset += 64;
				}
				$blockLight = "";
				$offset = ($y << 3);
				for($i = 0; $i < 256; ++$i){
					$blockLight .= substr($fullBlockLight, $offset, 8);
					$offset += 64;
				}
				$subChunks[$y] = new SubChunk($ids, $data, $skyLight, $blockLight);
			}

			if(isset($chunk->BiomeColors)){
				$biomeIds = ChunkUtils::convertBiomeColors($chunk->BiomeColors->getValue()); //Convert back to original format
			}elseif(isset($chunk->Biomes)){
				$biomeIds = $chunk->Biomes->getValue();
			}else{
				$biomeIds = "";
			}

			$heightMap = [];
			if(isset($chunk->HeightMap)){
				if($chunk->HeightMap instanceof ByteArrayTag){
					$heightMap = array_values(unpack("C*", $chunk->HeightMap->getValue()));
				}elseif($chunk->HeightMap instanceof IntArrayTag){
					$heightMap = $chunk->HeightMap->getValue(); #blameshoghicp
				}
			}

			$result = new Chunk(
				$this,
				$chunk["xPos"],
				$chunk["zPos"],
				$subChunks,
				isset($chunk->Entities) ? $chunk->Entities->getValue() : [],
				isset($chunk->TileEntities) ? $chunk->TileEntities->getValue() : [],
				$biomeIds,
				$heightMap
			);
			$result->setLightPopulated(isset($chunk->LightPopulated) ? ((bool) $chunk->LightPopulated->getValue()) : false);
			$result->setPopulated(isset($chunk->TerrainPopulated) ? ((bool) $chunk->TerrainPopulated->getValue()) : false);
			$result->setGenerated(true);
			return $result;
		}catch(\Throwable $e){
			MainLogger::getLogger()->logException($e);
			return null;
		}
	}

	public static function getProviderName() : string{
		return "mcregion";
	}

	public function getWorldHeight() : int{
		//TODO: add world height options
		return 128;
	}

	public static function isValid(string $path) : bool{
		$isValid = (file_exists($path . "/level.dat") and is_dir($path . "/region/"));

		if($isValid){
			$files = glob($path . "/region/*.mc*");
			if(empty($files)){ //possible glob() issue on some systems
				$files = array_filter(scandir($path . "/region/"), function($file){
					return substr($file, strrpos($file, ".") + 1, 2) === "mc"; //region file
				});
			}

			foreach($files as $f){
				if(substr($f, strrpos($f, ".") + 1) !== static::REGION_FILE_EXTENSION){
					$isValid = false;
					break;
				}
			}
		}

		return $isValid;
	}

	public static function generate(string $path, string $name, $seed, string $generator, array $options = []){
		if(!file_exists($path)){
			mkdir($path, 0777, true);
		}

		if(!file_exists($path . "/region")){
			mkdir($path . "/region", 0777);
		}
		//TODO, add extra details
		$levelData = new CompoundTag("Data", [
			"hardcore" => new ByteTag("hardcore", 0),
			"initialized" => new ByteTag("initialized", 1),
			"GameType" => new IntTag("GameType", 0),
			"generatorVersion" => new IntTag("generatorVersion", 1), //2 in MCPE
			"SpawnX" => new IntTag("SpawnX", 256),
			"SpawnY" => new IntTag("SpawnY", 70),
			"SpawnZ" => new IntTag("SpawnZ", 256),
			"version" => new IntTag("version", 19133),
			"DayTime" => new IntTag("DayTime", 0),
			"LastPlayed" => new LongTag("LastPlayed", microtime(true) * 1000),
			"RandomSeed" => new LongTag("RandomSeed", $seed),
			"SizeOnDisk" => new LongTag("SizeOnDisk", 0),
			"Time" => new LongTag("Time", 0),
			"generatorName" => new StringTag("generatorName", Generator::getGeneratorName($generator)),
			"generatorOptions" => new StringTag("generatorOptions", isset($options["preset"]) ? $options["preset"] : ""),
			"LevelName" => new StringTag("LevelName", $name),
			"GameRules" => new CompoundTag("GameRules", [])
		]);
		$nbt = new NBT(NBT::BIG_ENDIAN);
		$nbt->setData(new CompoundTag("", [
			"Data" => $levelData
		]));
		$buffer = $nbt->writeCompressed();
		file_put_contents($path . "level.dat", $buffer);
	}

	public function getGenerator() : string{
		return (string) $this->levelData["generatorName"];
	}

	public function getGeneratorOptions() : array{
		return ["preset" => $this->levelData["generatorOptions"]];
	}

	public function getChunk(int $chunkX, int $chunkZ, bool $create = false){
		$index = Level::chunkHash($chunkX, $chunkZ);
		if(isset($this->chunks[$index])){
			return $this->chunks[$index];
		}else{
			$this->loadChunk($chunkX, $chunkZ, $create);

			return $this->chunks[$index] ?? null;
		}
	}

	public function setChunk(int $chunkX, int $chunkZ, Chunk $chunk){

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

	public function saveChunk(int $chunkX, int $chunkZ) : bool{
		if($this->isChunkLoaded($chunkX, $chunkZ)){
			$this->getRegion($chunkX >> 5, $chunkZ >> 5)->writeChunk($this->getChunk($chunkX, $chunkZ));

			return true;
		}

		return false;
	}

	public function saveChunks(){
		foreach($this->chunks as $chunk){
			$this->saveChunk($chunk->getX(), $chunk->getZ());
		}
	}

	public function loadChunk(int $chunkX, int $chunkZ, bool $create = false) : bool{
		$index = Level::chunkHash($chunkX, $chunkZ);
		if(isset($this->chunks[$index])){
			return true;
		}
		$regionX = $regionZ = null;
		self::getRegionIndex($chunkX, $chunkZ, $regionX, $regionZ);
		/** @noinspection PhpStrictTypeCheckingInspection */
		$this->loadRegion($regionX, $regionZ);
		$this->level->timings->syncChunkLoadDataTimer->startTiming();
		/** @noinspection PhpStrictTypeCheckingInspection */
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

	public function unloadChunk(int $chunkX, int $chunkZ, bool $safe = true) : bool{
		$chunk = $this->chunks[$index = Level::chunkHash($chunkX, $chunkZ)] ?? null;
		if($chunk instanceof Chunk and $chunk->unload(false, $safe)){
			unset($this->chunks[$index]);
			return true;
		}

		return false;
	}

	public function unloadChunks(){
		foreach($this->chunks as $chunk){
			$this->unloadChunk($chunk->getX(), $chunk->getZ(), false);
		}
		$this->chunks = [];
	}

	public function isChunkLoaded(int $chunkX, int $chunkZ) : bool{
		return isset($this->chunks[Level::chunkHash($chunkX, $chunkZ)]);
	}

	public function isChunkGenerated(int $chunkX, int $chunkZ) : bool{
		if(($region = $this->getRegion($chunkX >> 5, $chunkZ >> 5)) !== null){
			return $region->chunkExists($chunkX - $region->getX() * 32, $chunkZ - $region->getZ() * 32) and $this->getChunk($chunkX - $region->getX() * 32, $chunkZ - $region->getZ() * 32, true)->isGenerated();
		}

		return false;
	}

	public function isChunkPopulated(int $chunkX, int $chunkZ) : bool{
		$chunk = $this->getChunk($chunkX, $chunkZ);
		if($chunk !== null){
			return $chunk->isPopulated();
		}else{
			return false;
		}
	}

	public function getLoadedChunks() : array{
		return $this->chunks;
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

	/**
	 * @param int $chunkX
	 * @param int $chunkZ
	 * @param int &$x
	 * @param int &$z
	 */
	public static function getRegionIndex(int $chunkX, int $chunkZ, &$x, &$z){
		$x = $chunkX >> 5;
		$z = $chunkZ >> 5;
	}

	/**
	 * @param int $chunkX
	 * @param int $chunkZ
	 *
	 * @return Chunk
	 */
	public function getEmptyChunk(int $chunkX, int $chunkZ){
		return Chunk::getEmptyChunk($chunkX, $chunkZ, $this);
	}

	/**
	 * @param int $x
	 * @param int $z
	 *
	 * @return RegionLoader
	 */
	protected function getRegion(int $x, int $z){
		return $this->regions[Level::chunkHash($x, $z)] ?? null;
	}

	/**
	 * @param int $x
	 * @param int $z
	 */
	protected function loadRegion(int $x, int $z){
		if(!isset($this->regions[$index = Level::chunkHash($x, $z)])){
			$this->regions[$index] = new RegionLoader($this, $x, $z, static::REGION_FILE_EXTENSION);
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
