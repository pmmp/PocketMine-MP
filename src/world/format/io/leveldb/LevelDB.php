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

namespace pocketmine\world\format\io\leveldb;

use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\data\bedrock\BiomeIds;
use pocketmine\data\bedrock\block\BlockStateData;
use pocketmine\data\bedrock\block\BlockStateDeserializeException;
use pocketmine\data\bedrock\block\BlockTypeNames;
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\nbt\NbtDataException;
use pocketmine\nbt\NbtException;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\TreeRoot;
use pocketmine\utils\Binary;
use pocketmine\utils\BinaryDataException;
use pocketmine\utils\BinaryStream;
use pocketmine\world\format\BiomeArray;
use pocketmine\world\format\Chunk;
use pocketmine\world\format\io\BaseWorldProvider;
use pocketmine\world\format\io\ChunkData;
use pocketmine\world\format\io\ChunkUtils;
use pocketmine\world\format\io\data\BedrockWorldData;
use pocketmine\world\format\io\exception\CorruptedChunkException;
use pocketmine\world\format\io\exception\CorruptedWorldException;
use pocketmine\world\format\io\exception\UnsupportedWorldFormatException;
use pocketmine\world\format\io\GlobalBlockStateHandlers;
use pocketmine\world\format\io\WorldData;
use pocketmine\world\format\io\WritableWorldProvider;
use pocketmine\world\format\PalettedBlockArray;
use pocketmine\world\format\SubChunk;
use pocketmine\world\WorldCreationOptions;
use Webmozart\PathUtil\Path;
use function array_map;
use function array_values;
use function chr;
use function count;
use function defined;
use function extension_loaded;
use function file_exists;
use function is_dir;
use function mkdir;
use function ord;
use function str_repeat;
use function strlen;
use function substr;
use function trim;
use function unpack;
use const LEVELDB_ZLIB_RAW_COMPRESSION;

class LevelDB extends BaseWorldProvider implements WritableWorldProvider{

	protected const FINALISATION_NEEDS_INSTATICKING = 0;
	protected const FINALISATION_NEEDS_POPULATION = 1;
	protected const FINALISATION_DONE = 2;

	protected const ENTRY_FLAT_WORLD_LAYERS = "game_flatworldlayers";

	protected const CURRENT_LEVEL_CHUNK_VERSION = ChunkVersion::v1_18_30;
	protected const CURRENT_LEVEL_SUBCHUNK_VERSION = SubChunkVersion::PALETTED_MULTI;

	private const CAVES_CLIFFS_EXPERIMENTAL_SUBCHUNK_KEY_OFFSET = 4;

	protected \LevelDB $db;

	private static function checkForLevelDBExtension() : void{
		if(!extension_loaded('leveldb')){
			throw new UnsupportedWorldFormatException("The leveldb PHP extension is required to use this world format");
		}

		if(!defined('LEVELDB_ZLIB_RAW_COMPRESSION')){
			throw new UnsupportedWorldFormatException("Given version of php-leveldb doesn't support zlib raw compression");
		}
	}

	/**
	 * @throws \LevelDBException
	 */
	private static function createDB(string $path) : \LevelDB{
		return new \LevelDB(Path::join($path, "db"), [
			"compression" => LEVELDB_ZLIB_RAW_COMPRESSION,
			"block_size" => 64 * 1024 //64KB, big enough for most chunks
		]);
	}

	public function __construct(string $path){
		self::checkForLevelDBExtension();
		parent::__construct($path);

		try{
			$this->db = self::createDB($path);
		}catch(\LevelDBException $e){
			//we can't tell the difference between errors caused by bad permissions and actual corruption :(
			throw new CorruptedWorldException(trim($e->getMessage()), 0, $e);
		}
	}

	protected function loadLevelData() : WorldData{
		return new BedrockWorldData(Path::join($this->getPath(), "level.dat"));
	}

	public function getWorldMinY() : int{
		return -64;
	}

	public function getWorldMaxY() : int{
		return 320;
	}

	public static function isValid(string $path) : bool{
		return file_exists(Path::join($path, "level.dat")) && is_dir(Path::join($path, "db"));
	}

	public static function generate(string $path, string $name, WorldCreationOptions $options) : void{
		self::checkForLevelDBExtension();

		$dbPath = Path::join($path, "db");
		if(!file_exists($dbPath)){
			mkdir($dbPath, 0777, true);
		}

		BedrockWorldData::generate($path, $name, $options);
	}

	/**
	 * @throws CorruptedChunkException
	 */
	protected function deserializePaletted(BinaryStream $stream) : PalettedBlockArray{
		$bitsPerBlock = $stream->getByte() >> 1;

		try{
			$words = $stream->get(PalettedBlockArray::getExpectedWordArraySize($bitsPerBlock));
		}catch(\InvalidArgumentException $e){
			throw new CorruptedChunkException("Failed to deserialize paletted storage: " . $e->getMessage(), 0, $e);
		}
		$nbt = new LittleEndianNbtSerializer();
		$palette = [];

		$paletteSize = $bitsPerBlock === 0 ? 1 : $stream->getLInt();

		$blockDataUpgrader = GlobalBlockStateHandlers::getUpgrader();
		$blockStateDeserializer = GlobalBlockStateHandlers::getDeserializer();
		for($i = 0; $i < $paletteSize; ++$i){
			try{
				$offset = $stream->getOffset();

				$blockStateNbt = $nbt->read($stream->getBuffer(), $offset)->mustGetCompoundTag();
				$blockStateData = $blockDataUpgrader->upgradeBlockStateNbt($blockStateNbt);
				if($blockStateData === null){
					//upgrading blockstates should always succeed, regardless of whether they've been implemented or not
					throw new BlockStateDeserializeException("Invalid or improperly mapped legacy blockstate: " . $blockStateNbt->toString());
				}
				$stream->setOffset($offset);

				try{
					$palette[] = $blockStateDeserializer->deserialize($blockStateData);
				}catch(BlockStateDeserializeException){
					//TODO: remember data for unknown states so we can implement them later
					//TODO: this is slow; we need to cache this
					//TODO: log this
					$palette[] = $blockStateDeserializer->deserialize(new BlockStateData(BlockTypeNames::INFO_UPDATE, CompoundTag::create(), BlockStateData::CURRENT_VERSION));
				}
			}catch(NbtException | BlockStateDeserializeException $e){
				throw new CorruptedChunkException("Invalid blockstate NBT at offset $i in paletted storage: " . $e->getMessage(), 0, $e);
			}
		}

		//TODO: exceptions
		return PalettedBlockArray::fromData($bitsPerBlock, $words, $palette);
	}

	protected static function deserializeExtraDataKey(int $chunkVersion, int $key, ?int &$x, ?int &$y, ?int &$z) : void{
		if($chunkVersion >= ChunkVersion::v1_0_0){
			$x = ($key >> 12) & 0xf;
			$z = ($key >> 8) & 0xf;
			$y = $key & 0xff;
		}else{ //pre-1.0, 7 bits were used because the build height limit was lower
			$x = ($key >> 11) & 0xf;
			$z = ($key >> 7) & 0xf;
			$y = $key & 0x7f;
		}
	}

	/**
	 * @return PalettedBlockArray[]
	 */
	protected function deserializeLegacyExtraData(string $index, int $chunkVersion) : array{
		if(($extraRawData = $this->db->get($index . ChunkDataKey::LEGACY_BLOCK_EXTRA_DATA)) === false || $extraRawData === ""){
			return [];
		}

		/** @var PalettedBlockArray[] $extraDataLayers */
		$extraDataLayers = [];
		$binaryStream = new BinaryStream($extraRawData);
		$count = $binaryStream->getLInt();

		$blockDataUpgrader = GlobalBlockStateHandlers::getUpgrader();
		$blockStateDeserializer = GlobalBlockStateHandlers::getDeserializer();
		for($i = 0; $i < $count; ++$i){
			$key = $binaryStream->getLInt();
			$value = $binaryStream->getLShort();

			self::deserializeExtraDataKey($chunkVersion, $key, $x, $fullY, $z);

			$ySub = ($fullY >> SubChunk::COORD_BIT_SIZE);
			$y = $key & SubChunk::COORD_MASK;

			$blockId = $value & 0xff;
			$blockData = ($value >> 8) & 0xf;
			$blockStateData = $blockDataUpgrader->upgradeIntIdMeta($blockId, $blockData);
			if($blockStateData === null){
				//TODO: we could preserve this in case it's supported in the future, but this was historically only
				//used for grass anyway, so we probably don't need to care
				continue;
			}
			$blockStateId = $blockStateDeserializer->deserialize($blockStateData);

			if(!isset($extraDataLayers[$ySub])){
				$extraDataLayers[$ySub] = new PalettedBlockArray(BlockTypeIds::AIR << Block::INTERNAL_STATE_DATA_BITS);
			}
			$extraDataLayers[$ySub]->set($x, $y, $z, $blockStateId);
		}

		return $extraDataLayers;
	}

	private function readVersion(int $chunkX, int $chunkZ) : ?int{
		$index = self::chunkIndex($chunkX, $chunkZ);
		$chunkVersionRaw = $this->db->get($index . ChunkDataKey::NEW_VERSION);
		if($chunkVersionRaw === false){
			$chunkVersionRaw = $this->db->get($index . ChunkDataKey::OLD_VERSION);
			if($chunkVersionRaw === false){
				return null;
			}
		}

		return ord($chunkVersionRaw);
	}

	private static function hasOffsetCavesAndCliffsSubChunks(int $chunkVersion) : bool{
		return $chunkVersion >= ChunkVersion::v1_16_220_50_unused && $chunkVersion <= ChunkVersion::v1_16_230_50_unused;
	}

	/**
	 * @throws CorruptedChunkException
	 */
	public function loadChunk(int $chunkX, int $chunkZ) : ?ChunkData{
		$index = LevelDB::chunkIndex($chunkX, $chunkZ);

		$chunkVersion = $this->readVersion($chunkX, $chunkZ);
		if($chunkVersion === null){
			//TODO: this might be a slightly-corrupted chunk with a missing version field
			return null;
		}

		/** @var SubChunk[] $subChunks */
		$subChunks = [];

		/** @var BiomeArray|null $biomeArray */
		$biomeArray = null;

		$hasBeenUpgraded = $chunkVersion < self::CURRENT_LEVEL_CHUNK_VERSION;

		$subChunkKeyOffset = self::hasOffsetCavesAndCliffsSubChunks($chunkVersion) ? self::CAVES_CLIFFS_EXPERIMENTAL_SUBCHUNK_KEY_OFFSET : 0;

		switch($chunkVersion){
			case ChunkVersion::v1_18_30:
			case ChunkVersion::v1_18_0_25_beta:
			case ChunkVersion::v1_18_0_24_unused:
			case ChunkVersion::v1_18_0_24_beta:
			case ChunkVersion::v1_18_0_22_unused:
			case ChunkVersion::v1_18_0_22_beta:
			case ChunkVersion::v1_18_0_20_unused:
			case ChunkVersion::v1_18_0_20_beta:
			case ChunkVersion::v1_17_40_unused:
			case ChunkVersion::v1_17_40_20_beta_experimental_caves_cliffs:
			case ChunkVersion::v1_17_30_25_unused:
			case ChunkVersion::v1_17_30_25_beta_experimental_caves_cliffs:
			case ChunkVersion::v1_17_30_23_unused:
			case ChunkVersion::v1_17_30_23_beta_experimental_caves_cliffs:
			case ChunkVersion::v1_16_230_50_unused:
			case ChunkVersion::v1_16_230_50_beta_experimental_caves_cliffs:
			case ChunkVersion::v1_16_220_50_unused:
			case ChunkVersion::v1_16_220_50_beta_experimental_caves_cliffs:
			case ChunkVersion::v1_16_210:
			case ChunkVersion::v1_16_100_57_beta:
			case ChunkVersion::v1_16_100_52_beta:
			case ChunkVersion::v1_16_0:
			case ChunkVersion::v1_16_0_51_beta:
				//TODO: check walls
			case ChunkVersion::v1_12_0_unused2:
			case ChunkVersion::v1_12_0_unused1:
			case ChunkVersion::v1_12_0_4_beta:
			case ChunkVersion::v1_11_1:
			case ChunkVersion::v1_11_0_4_beta:
			case ChunkVersion::v1_11_0_3_beta:
			case ChunkVersion::v1_11_0_1_beta:
			case ChunkVersion::v1_9_0:
			case ChunkVersion::v1_8_0:
			case ChunkVersion::v1_2_13:
			case ChunkVersion::v1_2_0:
			case ChunkVersion::v1_2_0_2_beta:
			case ChunkVersion::v1_1_0_converted_from_console:
			case ChunkVersion::v1_1_0:
				//TODO: check beds
			case ChunkVersion::v1_0_0:
				$convertedLegacyExtraData = $this->deserializeLegacyExtraData($index, $chunkVersion);

				for($y = Chunk::MIN_SUBCHUNK_INDEX; $y <= Chunk::MAX_SUBCHUNK_INDEX; ++$y){
					if(($data = $this->db->get($index . ChunkDataKey::SUBCHUNK . chr($y + $subChunkKeyOffset))) === false){
						continue;
					}

					$binaryStream = new BinaryStream($data);
					if($binaryStream->feof()){
						throw new CorruptedChunkException("Unexpected empty data for subchunk $y");
					}
					$subChunkVersion = $binaryStream->getByte();
					if($subChunkVersion < self::CURRENT_LEVEL_SUBCHUNK_VERSION){
						$hasBeenUpgraded = true;
					}

					switch($subChunkVersion){
						case SubChunkVersion::CLASSIC:
						case SubChunkVersion::CLASSIC_BUG_2: //these are all identical to version 0, but vanilla respects these so we should also
						case SubChunkVersion::CLASSIC_BUG_3:
						case SubChunkVersion::CLASSIC_BUG_4:
						case SubChunkVersion::CLASSIC_BUG_5:
						case SubChunkVersion::CLASSIC_BUG_6:
						case SubChunkVersion::CLASSIC_BUG_7:
							try{
								$blocks = $binaryStream->get(4096);
								$blockData = $binaryStream->get(2048);

								if($chunkVersion < ChunkVersion::v1_1_0){
									$binaryStream->get(4096); //legacy light info, discard it
									$hasBeenUpgraded = true;
								}
							}catch(BinaryDataException $e){
								throw new CorruptedChunkException($e->getMessage(), 0, $e);
							}

							$storages = [$this->palettizeLegacySubChunkXZY($blocks, $blockData)];
							if(isset($convertedLegacyExtraData[$y])){
								$storages[] = $convertedLegacyExtraData[$y];
							}

							$subChunks[$y] = new SubChunk(BlockTypeIds::AIR << Block::INTERNAL_STATE_DATA_BITS, $storages);
							break;
						case SubChunkVersion::PALETTED_SINGLE:
							$storages = [$this->deserializePaletted($binaryStream)];
							if(isset($convertedLegacyExtraData[$y])){
								$storages[] = $convertedLegacyExtraData[$y];
							}
							$subChunks[$y] = new SubChunk(BlockTypeIds::AIR << Block::INTERNAL_STATE_DATA_BITS, $storages);
							break;
						case SubChunkVersion::PALETTED_MULTI:
						case SubChunkVersion::PALETTED_MULTI_WITH_OFFSET:
							//legacy extradata layers intentionally ignored because they aren't supposed to exist in v8
							$storageCount = $binaryStream->getByte();
							if($subChunkVersion >= SubChunkVersion::PALETTED_MULTI_WITH_OFFSET){
								//height ignored; this seems pointless since this is already in the key anyway
								$binaryStream->getByte();
							}
							if($storageCount > 0){
								$storages = [];

								for($k = 0; $k < $storageCount; ++$k){
									$storages[] = $this->deserializePaletted($binaryStream);
								}
								$subChunks[$y] = new SubChunk(BlockTypeIds::AIR << Block::INTERNAL_STATE_DATA_BITS, $storages);
							}
							break;
						default:
							//TODO: set chunks read-only so the version on disk doesn't get overwritten
							throw new CorruptedChunkException("don't know how to decode LevelDB subchunk format version $subChunkVersion");
					}
				}

				if(($maps2d = $this->db->get($index . ChunkDataKey::HEIGHTMAP_AND_2D_BIOMES)) !== false){
					$binaryStream = new BinaryStream($maps2d);

					try{
						$binaryStream->get(512); //heightmap, discard it
						$biomeArray = new BiomeArray($binaryStream->get(256)); //never throws
					}catch(BinaryDataException $e){
						throw new CorruptedChunkException($e->getMessage(), 0, $e);
					}
				}
				break;
			case ChunkVersion::v0_9_5:
			case ChunkVersion::v0_9_2:
			case ChunkVersion::v0_9_0:
				$convertedLegacyExtraData = $this->deserializeLegacyExtraData($index, $chunkVersion);

				$legacyTerrain = $this->db->get($index . ChunkDataKey::LEGACY_TERRAIN);
				if($legacyTerrain === false){
					throw new CorruptedChunkException("Missing expected LEGACY_TERRAIN tag for format version $chunkVersion");
				}
				$binaryStream = new BinaryStream($legacyTerrain);
				try{
					$fullIds = $binaryStream->get(32768);
					$fullData = $binaryStream->get(16384);
					$binaryStream->get(32768); //legacy light info, discard it
				}catch(BinaryDataException $e){
					throw new CorruptedChunkException($e->getMessage(), 0, $e);
				}

				for($yy = 0; $yy < 8; ++$yy){
					$storages = [$this->palettizeLegacySubChunkFromColumn($fullIds, $fullData, $yy)];
					if(isset($convertedLegacyExtraData[$yy])){
						$storages[] = $convertedLegacyExtraData[$yy];
					}
					$subChunks[$yy] = new SubChunk(BlockTypeIds::AIR << Block::INTERNAL_STATE_DATA_BITS, $storages);
				}

				try{
					$binaryStream->get(256); //heightmap, discard it
					/** @var int[] $unpackedBiomeArray */
					$unpackedBiomeArray = unpack("N*", $binaryStream->get(1024)); //unpack() will never fail here
					$biomeArray = new BiomeArray(ChunkUtils::convertBiomeColors(array_values($unpackedBiomeArray))); //never throws
				}catch(BinaryDataException $e){
					throw new CorruptedChunkException($e->getMessage(), 0, $e);
				}
				break;
			default:
				//TODO: set chunks read-only so the version on disk doesn't get overwritten
				throw new CorruptedChunkException("don't know how to decode chunk format version $chunkVersion");
		}

		$nbt = new LittleEndianNbtSerializer();

		/** @var CompoundTag[] $entities */
		$entities = [];
		if(($entityData = $this->db->get($index . ChunkDataKey::ENTITIES)) !== false && $entityData !== ""){
			try{
				$entities = array_map(fn(TreeRoot $root) => $root->mustGetCompoundTag(), $nbt->readMultiple($entityData));
			}catch(NbtDataException $e){
				throw new CorruptedChunkException($e->getMessage(), 0, $e);
			}
		}

		/** @var CompoundTag[] $tiles */
		$tiles = [];
		if(($tileData = $this->db->get($index . ChunkDataKey::BLOCK_ENTITIES)) !== false && $tileData !== ""){
			try{
				$tiles = array_map(fn(TreeRoot $root) => $root->mustGetCompoundTag(), $nbt->readMultiple($tileData));
			}catch(NbtDataException $e){
				throw new CorruptedChunkException($e->getMessage(), 0, $e);
			}
		}

		$finalisationChr = $this->db->get($index . ChunkDataKey::FINALIZATION);
		if($finalisationChr !== false){
			$finalisation = ord($finalisationChr);
			$terrainPopulated = $finalisation === self::FINALISATION_DONE;
		}else{ //older versions didn't have this tag
			$terrainPopulated = true;
		}

		//TODO: tile ticks, biome states (?)

		$chunk = new Chunk(
			$subChunks,
			$biomeArray ?? BiomeArray::fill(BiomeIds::OCEAN), //TODO: maybe missing biomes should be an error?
			$terrainPopulated
		);

		if($hasBeenUpgraded){
			$chunk->setTerrainDirty(); //trigger rewriting chunk to disk if it was converted from an older format
		}

		return new ChunkData($chunk, $entities, $tiles);
	}

	public function saveChunk(int $chunkX, int $chunkZ, ChunkData $chunkData) : void{
		$index = LevelDB::chunkIndex($chunkX, $chunkZ);

		$write = new \LevelDBWriteBatch();

		$previousVersion = $this->readVersion($chunkX, $chunkZ);
		$write->put($index . ChunkDataKey::NEW_VERSION, chr(self::CURRENT_LEVEL_CHUNK_VERSION));

		$chunk = $chunkData->getChunk();

		if($chunk->getTerrainDirtyFlag(Chunk::DIRTY_FLAG_BLOCKS)){
			$subChunks = $chunk->getSubChunks();

			//TODO: this should not rely on globals, but in PM4 we have no other option, and it's not worse than what we
			//were doing before anyway ...
			$blockStateSerializer = GlobalBlockStateHandlers::getSerializer();
			foreach($subChunks as $y => $subChunk){
				$key = $index . ChunkDataKey::SUBCHUNK . chr($y);
				if($subChunk->isEmptyAuthoritative()){
					$write->delete($key);
				}else{
					$subStream = new BinaryStream();
					$subStream->putByte(self::CURRENT_LEVEL_SUBCHUNK_VERSION);

					$layers = $subChunk->getBlockLayers();
					$subStream->putByte(count($layers));
					foreach($layers as $blocks){
						$subStream->putByte($blocks->getBitsPerBlock() << 1);
						$subStream->put($blocks->getWordArray());

						$palette = $blocks->getPalette();
						if($blocks->getBitsPerBlock() !== 0){
							$subStream->putLInt(count($palette));
						}
						$tags = [];
						foreach($palette as $p){
							$tags[] = new TreeRoot($blockStateSerializer->serialize($p)->toNbt());
						}

						$subStream->put((new LittleEndianNbtSerializer())->writeMultiple($tags));
					}

					$write->put($key, $subStream->getBuffer());
				}
			}
		}

		if($chunk->getTerrainDirtyFlag(Chunk::DIRTY_FLAG_BIOMES)){
			$write->put($index . ChunkDataKey::HEIGHTMAP_AND_2D_BIOMES, str_repeat("\x00", 512) . $chunk->getBiomeIdArray());
		}

		//TODO: use this properly
		$write->put($index . ChunkDataKey::FINALIZATION, chr($chunk->isPopulated() ? self::FINALISATION_DONE : self::FINALISATION_NEEDS_POPULATION));

		$this->writeTags($chunkData->getTileNBT(), $index . ChunkDataKey::BLOCK_ENTITIES, $write);
		$this->writeTags($chunkData->getEntityNBT(), $index . ChunkDataKey::ENTITIES, $write);

		$write->delete($index . ChunkDataKey::HEIGHTMAP_AND_2D_BIOME_COLORS);
		$write->delete($index . ChunkDataKey::LEGACY_TERRAIN);

		$this->db->write($write);
	}

	/**
	 * @param CompoundTag[]      $targets
	 */
	private function writeTags(array $targets, string $index, \LevelDBWriteBatch $write) : void{
		if(count($targets) > 0){
			$nbt = new LittleEndianNbtSerializer();
			$write->put($index, $nbt->writeMultiple(array_map(fn(CompoundTag $tag) => new TreeRoot($tag), $targets)));
		}else{
			$write->delete($index);
		}
	}

	public function getDatabase() : \LevelDB{
		return $this->db;
	}

	public static function chunkIndex(int $chunkX, int $chunkZ) : string{
		return Binary::writeLInt($chunkX) . Binary::writeLInt($chunkZ);
	}

	public function doGarbageCollection() : void{

	}

	public function close() : void{
		unset($this->db);
	}

	public function getAllChunks(bool $skipCorrupted = false, ?\Logger $logger = null) : \Generator{
		foreach($this->db->getIterator() as $key => $_){
			if(strlen($key) === 9 && ($key[8] === ChunkDataKey::NEW_VERSION || $key[8] === ChunkDataKey::OLD_VERSION)){
				$chunkX = Binary::readLInt(substr($key, 0, 4));
				$chunkZ = Binary::readLInt(substr($key, 4, 4));
				try{
					if(($chunk = $this->loadChunk($chunkX, $chunkZ)) !== null){
						yield [$chunkX, $chunkZ] => $chunk;
					}
				}catch(CorruptedChunkException $e){
					if(!$skipCorrupted){
						throw $e;
					}
					if($logger !== null){
						$logger->error("Skipped corrupted chunk $chunkX $chunkZ (" . $e->getMessage() . ")");
					}
				}
			}
		}
	}

	public function calculateChunkCount() : int{
		$count = 0;
		foreach($this->db->getIterator() as $key => $_){
			if(strlen($key) === 9 && ($key[8] === ChunkDataKey::NEW_VERSION || $key[8] === ChunkDataKey::OLD_VERSION)){
				$count++;
			}
		}
		return $count;
	}
}
