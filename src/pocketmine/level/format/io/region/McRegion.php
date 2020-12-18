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
use pocketmine\level\format\io\BaseLevelProvider;
use pocketmine\level\format\io\ChunkUtils;
use pocketmine\level\format\io\exception\CorruptedChunkException;
use pocketmine\level\format\SubChunk;
use pocketmine\level\generator\GeneratorManager;
use pocketmine\level\Level;
use pocketmine\nbt\BigEndianNBTStream;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteArrayTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntArrayTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\LongTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\utils\MainLogger;
use function array_filter;
use function array_values;
use function assert;
use function file_exists;
use function file_put_contents;
use function is_dir;
use function is_int;
use function microtime;
use function mkdir;
use function pack;
use function rename;
use function scandir;
use function str_repeat;
use function strrpos;
use function substr;
use function time;
use function unpack;
use function zlib_decode;
use const SCANDIR_SORT_NONE;

class McRegion extends BaseLevelProvider{

	public const REGION_FILE_EXTENSION = "mcr";

	/** @var RegionLoader[] */
	protected $regions = [];

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
			$tiles[] = $tile->saveNBT();
		}

		$nbt->setTag(new ListTag("TileEntities", $tiles, NBT::TAG_Compound));

		$writer = new BigEndianNBTStream();
		return $writer->writeCompressed(new CompoundTag("", [$nbt]), ZLIB_ENCODING_DEFLATE, RegionLoader::$COMPRESSION_LEVEL);
	}

	/**
	 * @throws CorruptedChunkException
	 */
	protected function nbtDeserialize(string $data) : Chunk{
		$data = @zlib_decode($data);
		if($data === false){
			throw new CorruptedChunkException("Failed to decompress chunk data");
		}
		$nbt = new BigEndianNBTStream();
		$chunk = $nbt->read($data);
		if(!($chunk instanceof CompoundTag) or !$chunk->hasTag("Level")){
			throw new CorruptedChunkException("'Level' key is missing from chunk NBT");
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
			/** @var int[] $unpackedHeightMap */
			$unpackedHeightMap = unpack("C*", $chunk->getByteArray("HeightMap")); //unpack() will never fail here
			$heightMap = array_values($unpackedHeightMap);
		}elseif($chunk->hasTag("HeightMap", IntArrayTag::class)){
			$heightMap = $chunk->getIntArray("HeightMap"); #blameshoghicp
		}

		$result = new Chunk(
			$chunk->getInt("xPos"),
			$chunk->getInt("zPos"),
			$subChunks,
			$chunk->hasTag("Entities", ListTag::class) ? self::getCompoundList("Entities", $chunk->getListTag("Entities")) : [],
			$chunk->hasTag("TileEntities", ListTag::class) ? self::getCompoundList("TileEntities", $chunk->getListTag("TileEntities")) : [],
			$biomeIds,
			$heightMap
		);
		$result->setLightPopulated($chunk->getByte("LightPopulated", 0) !== 0);
		$result->setPopulated($chunk->getByte("TerrainPopulated", 0) !== 0);
		$result->setGenerated(true);
		return $result;
	}

	/**
	 * @return CompoundTag[]
	 * @throws CorruptedChunkException
	 */
	protected static function getCompoundList(string $context, ListTag $list) : array{
		if($list->count() === 0){ //empty lists might have wrong types, we don't care
			return [];
		}
		if($list->getTagType() !== NBT::TAG_Compound){
			throw new CorruptedChunkException("Expected TAG_List<TAG_Compound> for '$context'");
		}
		$result = [];
		foreach($list as $tag){
			if(!($tag instanceof CompoundTag)){
				//this should never happen, but it's still possible due to lack of native type safety
				throw new CorruptedChunkException("Expected TAG_List<TAG_Compound> for '$context'");
			}
			$result[] = $tag;
		}
		return $result;
	}

	public static function getProviderName() : string{
		return "mcregion";
	}

	/**
	 * Returns the storage version as per Minecraft PC world formats.
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
			$files = array_filter(scandir($path . "/region/", SCANDIR_SORT_NONE), function(string $file) : bool{
				$extPos = strrpos($file, ".");
				return $extPos !== false && substr($file, $extPos + 1, 2) === "mc"; //region file
			});

			foreach($files as $f){
				$extPos = strrpos($f, ".");
				if($extPos !== false && substr($f, $extPos + 1) !== static::REGION_FILE_EXTENSION){
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
			new StringTag("generatorName", GeneratorManager::getGeneratorName($generator)),
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
	 * @param int $regionX reference parameter
	 * @param int $regionZ reference parameter
	 *
	 * @return void
	 */
	public static function getRegionIndex(int $chunkX, int $chunkZ, &$regionX, &$regionZ){
		$regionX = $chunkX >> 5;
		$regionZ = $chunkZ >> 5;
	}

	/**
	 * @return RegionLoader|null
	 */
	protected function getRegion(int $regionX, int $regionZ){
		return $this->regions[Level::chunkHash($regionX, $regionZ)] ?? null;
	}

	/**
	 * Returns the path to a specific region file based on its X/Z coordinates
	 */
	protected function pathToRegion(int $regionX, int $regionZ) : string{
		return $this->path . "region/r.$regionX.$regionZ." . static::REGION_FILE_EXTENSION;
	}

	/**
	 * @return void
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

	/**
	 * @throws CorruptedChunkException
	 */
	protected function readChunk(int $chunkX, int $chunkZ) : ?Chunk{
		$regionX = $regionZ = null;
		self::getRegionIndex($chunkX, $chunkZ, $regionX, $regionZ);
		assert(is_int($regionX) and is_int($regionZ));

		if(!file_exists($this->pathToRegion($regionX, $regionZ))){
			return null;
		}
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
