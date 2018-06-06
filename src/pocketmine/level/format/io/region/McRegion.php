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

declare(strict_types=1);

namespace pocketmine\level\format\io\region;

use pocketmine\level\format\Chunk;
use pocketmine\level\format\ChunkException;
use pocketmine\level\format\io\BaseLevelProvider;
use pocketmine\level\format\io\ChunkUtils;
use pocketmine\level\format\SubChunk;
use pocketmine\level\generator\Generator;
use pocketmine\level\Level;
use pocketmine\nbt\BigEndianNBTStream;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\{
	ByteArrayTag, ByteTag, CompoundTag, IntArrayTag, IntTag, ListTag, LongTag, StringTag
};
use pocketmine\utils\MainLogger;

class McRegion extends BaseLevelProvider{

	public const REGION_FILE_EXTENSION = "mcr";

	/** @var RegionLoader[] */
	protected $regions = [];

	/**
	 * @param Chunk $chunk
	 *
	 * @return string
	 */
	protected function nbtSerialize(Chunk $chunk) : string{
		$nbt = new CompoundTag("Level", []);
		$nbt->setInt("xPos", $chunk->getX());
		$nbt->setInt("zPos", $chunk->getZ());

		$nbt->setLong("LastUpdate", 0); //TODO
		$nbt->setByte("TerrainPopulated", $chunk->isPopulated() ? 1 : 0);
		$nbt->setByte("LightPopulated", $chunk->isLightPopulated() ? 1 : 0);

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
					$skyLight .= $subChunk->getBlockSkyLightColumn($x, $z);
					$blockLight .= $subChunk->getBlockLightColumn($x, $z);
				}
			}
		}

		$nbt->setByteArray("Blocks", $ids);
		$nbt->setByteArray("Data", $data);
		$nbt->setByteArray("SkyLight", $skyLight);
		$nbt->setByteArray("BlockLight", $blockLight);

		$nbt->setByteArray("Biomes", $chunk->getBiomeIdArray()); //doesn't exist in regular McRegion, this is here for PocketMine-MP only
		$nbt->setByteArray("HeightMap", pack("C*", ...$chunk->getHeightMapArray())); //this is ByteArray in McRegion, but IntArray in Anvil (due to raised build height)

		$entities = [];

		foreach($chunk->getSavableEntities() as $entity){
			$entity->saveNBT();
			$entities[] = $entity->namedtag;
		}

		$nbt->setTag(new ListTag("Entities", $entities, NBT::TAG_Compound));

		$tiles = [];
		foreach($chunk->getTiles() as $tile){
			$tile->saveNBT($tileTag = new CompoundTag());
			$tiles[] = $tileTag;
		}

		$nbt->setTag(new ListTag("TileEntities", $tiles, NBT::TAG_Compound));

		$writer = new BigEndianNBTStream();
		return $writer->writeCompressed(new CompoundTag("", [$nbt]), ZLIB_ENCODING_DEFLATE, RegionLoader::$COMPRESSION_LEVEL);
	}

	/**
	 * @param string $data
	 *
	 * @return Chunk|null
	 */
	protected function nbtDeserialize(string $data){
		$nbt = new BigEndianNBTStream();
		$chunk = $nbt->readCompressed($data);
		if(!($chunk instanceof CompoundTag) or !$chunk->hasTag("Level")){
			throw new ChunkException("Invalid NBT format");
		}

		$chunk = $chunk->getCompoundTag("Level");

		$subChunks = [];
		$fullIds = $chunk->hasTag("Blocks", ByteArrayTag::class) ? $chunk->getByteArray("Blocks") : str_repeat("\x00", 32768);
		$fullData = $chunk->hasTag("Data", ByteArrayTag::class) ? $chunk->getByteArray("Data") : str_repeat("\x00", 16384);
		$fullSkyLight = $chunk->hasTag("SkyLight", ByteArrayTag::class) ? $chunk->getByteArray("SkyLight") : str_repeat("\xff", 16384);
		$fullBlockLight = $chunk->hasTag("BlockLight", ByteArrayTag::class) ? $chunk->getByteArray("BlockLight") : str_repeat("\x00", 16384);

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

		if($chunk->hasTag("BiomeColors", IntArrayTag::class)){
			$biomeIds = ChunkUtils::convertBiomeColors($chunk->getIntArray("BiomeColors")); //Convert back to original format
		}elseif($chunk->hasTag("Biomes", ByteArrayTag::class)){
			$biomeIds = $chunk->getByteArray("Biomes");
		}else{
			$biomeIds = "";
		}

		$heightMap = [];
		if($chunk->hasTag("HeightMap", ByteArrayTag::class)){
			$heightMap = array_values(unpack("C*", $chunk->getByteArray("HeightMap")));
		}elseif($chunk->hasTag("HeightMap", IntArrayTag::class)){
			$heightMap = $chunk->getIntArray("HeightMap"); #blameshoghicp
		}

		$result = new Chunk(
			$chunk->getInt("xPos"),
			$chunk->getInt("zPos"),
			$subChunks,
			$chunk->hasTag("Entities", ListTag::class) ? $chunk->getListTag("Entities")->getValue() : [],
			$chunk->hasTag("TileEntities", ListTag::class) ? $chunk->getListTag("TileEntities")->getValue() : [],
			$biomeIds,
			$heightMap
		);
		$result->setLightPopulated($chunk->getByte("LightPopulated", 0) !== 0);
		$result->setPopulated($chunk->getByte("TerrainPopulated", 0) !== 0);
		$result->setGenerated(true);
		return $result;
	}

	public static function getProviderName() : string{
		return "mcregion";
	}

	/**
	 * Returns the storage version as per Minecraft PC world formats.
	 * @return int
	 */
	public static function getPcWorldFormatVersion() : int{
		return 19132; //mcregion
	}

	public function getWorldHeight() : int{
		//TODO: add world height options
		return 128;
	}

	public static function isValid(string $path) : bool{
		$isValid = (file_exists($path . "/level.dat") and is_dir($path . "/region/"));

		if($isValid){
			$files = array_filter(scandir($path . "/region/", SCANDIR_SORT_NONE), function($file){
				return substr($file, strrpos($file, ".") + 1, 2) === "mc"; //region file
			});

			foreach($files as $f){
				if(substr($f, strrpos($f, ".") + 1) !== static::REGION_FILE_EXTENSION){
					$isValid = false;
					break;
				}
			}
		}

		return $isValid;
	}

	public static function generate(string $path, string $name, int $seed, string $generator, array $options = []){
		if(!file_exists($path)){
			mkdir($path, 0777, true);
		}

		if(!file_exists($path . "/region")){
			mkdir($path . "/region", 0777);
		}
		//TODO, add extra details
		$levelData = new CompoundTag("Data", [
			new ByteTag("hardcore", ($options["hardcore"] ?? false) === true ? 1 : 0),
			new ByteTag("Difficulty", Level::getDifficultyFromString((string) ($options["difficulty"] ?? "normal"))),
			new ByteTag("initialized", 1),
			new IntTag("GameType", 0),
			new IntTag("generatorVersion", 1), //2 in MCPE
			new IntTag("SpawnX", 256),
			new IntTag("SpawnY", 70),
			new IntTag("SpawnZ", 256),
			new IntTag("version", static::getPcWorldFormatVersion()),
			new IntTag("DayTime", 0),
			new LongTag("LastPlayed", (int) (microtime(true) * 1000)),
			new LongTag("RandomSeed", $seed),
			new LongTag("SizeOnDisk", 0),
			new LongTag("Time", 0),
			new StringTag("generatorName", Generator::getGeneratorName($generator)),
			new StringTag("generatorOptions", $options["preset"] ?? ""),
			new StringTag("LevelName", $name),
			new CompoundTag("GameRules", [])
		]);
		$nbt = new BigEndianNBTStream();
		$buffer = $nbt->writeCompressed(new CompoundTag("", [
			$levelData
		]));
		file_put_contents($path . "level.dat", $buffer);
	}

	public function getGenerator() : string{
		return $this->levelData->getString("generatorName", "DEFAULT");
	}

	public function getGeneratorOptions() : array{
		return ["preset" => $this->levelData->getString("generatorOptions", "")];
	}

	public function getDifficulty() : int{
		return $this->levelData->getByte("Difficulty", Level::DIFFICULTY_NORMAL);
	}

	public function setDifficulty(int $difficulty){
		$this->levelData->setByte("Difficulty", $difficulty);
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
	 * @param int &$regionX
	 * @param int &$regionZ
	 */
	public static function getRegionIndex(int $chunkX, int $chunkZ, &$regionX, &$regionZ){
		$regionX = $chunkX >> 5;
		$regionZ = $chunkZ >> 5;
	}

	/**
	 * @param int $regionX
	 * @param int $regionZ
	 *
	 * @return RegionLoader|null
	 */
	protected function getRegion(int $regionX, int $regionZ){
		return $this->regions[Level::chunkHash($regionX, $regionZ)] ?? null;
	}

	/**
	 * Returns the path to a specific region file based on its X/Z coordinates
	 *
	 * @param int $regionX
	 * @param int $regionZ
	 *
	 * @return string
	 */
	protected function pathToRegion(int $regionX, int $regionZ) : string{
		return $this->path . "region/r.$regionX.$regionZ." . static::REGION_FILE_EXTENSION;
	}

	/**
	 * @param int $regionX
	 * @param int $regionZ
	 */
	protected function loadRegion(int $regionX, int $regionZ){
		if(!isset($this->regions[$index = Level::chunkHash($regionX, $regionZ)])){
			$path = $this->pathToRegion($regionX, $regionZ);

			$region = new RegionLoader($path, $regionX, $regionZ);
			try{
				$region->open();
			}catch(CorruptedRegionException $e){
				$logger = MainLogger::getLogger();
				$logger->error("Corrupted region file detected: " . $e->getMessage());

				$region->close(false); //Do not write anything to the file

				$backupPath = $path . ".bak." . time();
				rename($path, $backupPath);
				$logger->error("Corrupted region file has been backed up to " . $backupPath);

				$region = new RegionLoader($path, $regionX, $regionZ);
				$region->open(); //this will create a new empty region to replace the corrupted one
			}

			$this->regions[$index] = $region;
		}
	}

	public function close(){
		foreach($this->regions as $index => $region){
			$region->close();
			unset($this->regions[$index]);
		}
	}

	protected function readChunk(int $chunkX, int $chunkZ) : ?Chunk{
		$regionX = $regionZ = null;
		self::getRegionIndex($chunkX, $chunkZ, $regionX, $regionZ);
		assert(is_int($regionX) and is_int($regionZ));

		$this->loadRegion($regionX, $regionZ);

		$chunkData = $this->getRegion($regionX, $regionZ)->readChunk($chunkX & 0x1f, $chunkZ & 0x1f);
		if($chunkData !== null){
			return $this->nbtDeserialize($chunkData);
		}

		return null;
	}

	protected function writeChunk(Chunk $chunk) : void{
		$chunkX = $chunk->getX();
		$chunkZ = $chunk->getZ();

		self::getRegionIndex($chunkX, $chunkZ, $regionX, $regionZ);
		$this->loadRegion($regionX, $regionZ);

		$this->getRegion($regionX, $regionZ)->writeChunk($chunkX & 0x1f, $chunkZ & 0x1f, $this->nbtSerialize($chunk));
	}
}
