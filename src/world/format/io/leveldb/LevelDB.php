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
use pocketmine\block\BlockLegacyIds;
use pocketmine\data\bedrock\LegacyBlockIdToStringIdMap;
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\nbt\NbtDataException;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\TreeRoot;
use pocketmine\utils\Binary;
use pocketmine\utils\BinaryDataException;
use pocketmine\utils\BinaryStream;
use pocketmine\world\format\BiomeArray;
use pocketmine\world\format\Chunk;
use pocketmine\world\format\io\BaseWorldProvider;
use pocketmine\world\format\io\ChunkUtils;
use pocketmine\world\format\io\data\BedrockWorldData;
use pocketmine\world\format\io\exception\CorruptedChunkException;
use pocketmine\world\format\io\exception\CorruptedWorldException;
use pocketmine\world\format\io\exception\UnsupportedWorldFormatException;
use pocketmine\world\format\io\SubChunkConverter;
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

	//According to Tomasso, these aren't supposed to be readable anymore. Thankfully he didn't change the readable ones...
	protected const TAG_DATA_2D = "\x2d";
	protected const TAG_DATA_2D_LEGACY = "\x2e";
	protected const TAG_SUBCHUNK_PREFIX = "\x2f";
	protected const TAG_LEGACY_TERRAIN = "0";
	protected const TAG_BLOCK_ENTITY = "1";
	protected const TAG_ENTITY = "2";
	protected const TAG_PENDING_TICK = "3";
	protected const TAG_BLOCK_EXTRA_DATA = "4";
	protected const TAG_BIOME_STATE = "5";
	protected const TAG_STATE_FINALISATION = "6";

	protected const TAG_BORDER_BLOCKS = "8";
	protected const TAG_HARDCODED_SPAWNERS = "9";

	protected const FINALISATION_NEEDS_INSTATICKING = 0;
	protected const FINALISATION_NEEDS_POPULATION = 1;
	protected const FINALISATION_DONE = 2;

	protected const TAG_VERSION = "v";

	protected const ENTRY_FLAT_WORLD_LAYERS = "game_flatworldlayers";

	protected const CURRENT_LEVEL_CHUNK_VERSION = 7;
	protected const CURRENT_LEVEL_SUBCHUNK_VERSION = 8;

	/** @var \LevelDB */
	protected $db;

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
		return 0;
	}

	public function getWorldMaxY() : int{
		return 256;
	}

	public static function isValid(string $path) : bool{
		return file_exists(Path::join($path, "level.dat")) and is_dir(Path::join($path, "db"));
	}

	public static function generate(string $path, string $name, WorldCreationOptions $options) : void{
		self::checkForLevelDBExtension();

		$dbPath = Path::join($path, "db");
		if(!file_exists($dbPath)){
			mkdir($dbPath, 0777, true);
		}

		BedrockWorldData::generate($path, $name, $options);
	}

	protected function deserializePaletted(BinaryStream $stream) : PalettedBlockArray{
		$bitsPerBlock = $stream->getByte() >> 1;

		try{
			$words = $stream->get(PalettedBlockArray::getExpectedWordArraySize($bitsPerBlock));
		}catch(\InvalidArgumentException $e){
			throw new CorruptedChunkException("Failed to deserialize paletted storage: " . $e->getMessage(), 0, $e);
		}
		$nbt = new LittleEndianNbtSerializer();
		$palette = [];
		$idMap = LegacyBlockIdToStringIdMap::getInstance();
		for($i = 0, $paletteSize = $stream->getLInt(); $i < $paletteSize; ++$i){
			$offset = $stream->getOffset();
			$tag = $nbt->read($stream->getBuffer(), $offset)->mustGetCompoundTag();
			$stream->setOffset($offset);

			$id = $idMap->stringToLegacy($tag->getString("name")) ?? BlockLegacyIds::INFO_UPDATE;
			$data = $tag->getShort("val");
			if($id === BlockLegacyIds::AIR){
				//TODO: quick and dirty hack for artifacts left behind by broken world editors
				//we really need a proper state fixer, but this is a pressing issue.
				$data = 0;
			}
			$palette[] = ($id << Block::INTERNAL_METADATA_BITS) | $data;
		}

		//TODO: exceptions
		return PalettedBlockArray::fromData($bitsPerBlock, $words, $palette);
	}

	protected static function deserializeExtraDataKey(int $chunkVersion, int $key, ?int &$x, ?int &$y, ?int &$z) : void{
		if($chunkVersion >= 3){
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
		if(($extraRawData = $this->db->get($index . self::TAG_BLOCK_EXTRA_DATA)) === false or $extraRawData === ""){
			return [];
		}

		/** @var PalettedBlockArray[] $extraDataLayers */
		$extraDataLayers = [];
		$binaryStream = new BinaryStream($extraRawData);
		$count = $binaryStream->getLInt();
		for($i = 0; $i < $count; ++$i){
			$key = $binaryStream->getLInt();
			$value = $binaryStream->getLShort();

			self::deserializeExtraDataKey($chunkVersion, $key, $x, $fullY, $z);

			$ySub = ($fullY >> 4) & 0xf;
			$y = $key & 0xf;

			$blockId = $value & 0xff;
			$blockData = ($value >> 8) & 0xf;
			if(!isset($extraDataLayers[$ySub])){
				$extraDataLayers[$ySub] = new PalettedBlockArray(BlockLegacyIds::AIR << Block::INTERNAL_METADATA_BITS);
			}
			$extraDataLayers[$ySub]->set($x, $y, $z, ($blockId << Block::INTERNAL_METADATA_BITS) | $blockData);
		}

		return $extraDataLayers;
	}

	/**
	 * @throws CorruptedChunkException
	 */
	public function loadChunk(int $chunkX, int $chunkZ) : ?Chunk{
		$index = LevelDB::chunkIndex($chunkX, $chunkZ);

		$chunkVersionRaw = $this->db->get($index . self::TAG_VERSION);
		if($chunkVersionRaw === false){
			return null;
		}

		/** @var SubChunk[] $subChunks */
		$subChunks = [];

		/** @var BiomeArray|null $biomeArray */
		$biomeArray = null;

		$chunkVersion = ord($chunkVersionRaw);
		$hasBeenUpgraded = $chunkVersion < self::CURRENT_LEVEL_CHUNK_VERSION;

		switch($chunkVersion){
			case 15: //MCPE 1.12.0.4 beta (???)
			case 14: //MCPE 1.11.1.2 (???)
			case 13: //MCPE 1.11.0.4 beta (???)
			case 12: //MCPE 1.11.0.3 beta (???)
			case 11: //MCPE 1.11.0.1 beta (???)
			case 10: //MCPE 1.9 (???)
			case 9: //MCPE 1.8 (???)
			case 7: //MCPE 1.2 (???)
			case 6: //MCPE 1.2.0.2 beta (???)
			case 4: //MCPE 1.1
				//TODO: check beds
			case 3: //MCPE 1.0
				$convertedLegacyExtraData = $this->deserializeLegacyExtraData($index, $chunkVersion);

				for($y = 0; $y < Chunk::MAX_SUBCHUNKS; ++$y){
					if(($data = $this->db->get($index . self::TAG_SUBCHUNK_PREFIX . chr($y))) === false){
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
						case 0:
						case 2: //these are all identical to version 0, but vanilla respects these so we should also
						case 3:
						case 4:
						case 5:
						case 6:
						case 7:
							try{
								$blocks = $binaryStream->get(4096);
								$blockData = $binaryStream->get(2048);

								if($chunkVersion < 4){
									$binaryStream->get(4096); //legacy light info, discard it
									$hasBeenUpgraded = true;
								}
							}catch(BinaryDataException $e){
								throw new CorruptedChunkException($e->getMessage(), 0, $e);
							}

							$storages = [SubChunkConverter::convertSubChunkXZY($blocks, $blockData)];
							if(isset($convertedLegacyExtraData[$y])){
								$storages[] = $convertedLegacyExtraData[$y];
							}

							$subChunks[$y] = new SubChunk(BlockLegacyIds::AIR << Block::INTERNAL_METADATA_BITS, $storages);
							break;
						case 1: //paletted v1, has a single blockstorage
							$storages = [$this->deserializePaletted($binaryStream)];
							if(isset($convertedLegacyExtraData[$y])){
								$storages[] = $convertedLegacyExtraData[$y];
							}
							$subChunks[$y] = new SubChunk(BlockLegacyIds::AIR << Block::INTERNAL_METADATA_BITS, $storages);
							break;
						case 8:
							//legacy extradata layers intentionally ignored because they aren't supposed to exist in v8
							$storageCount = $binaryStream->getByte();
							if($storageCount > 0){
								$storages = [];

								for($k = 0; $k < $storageCount; ++$k){
									$storages[] = $this->deserializePaletted($binaryStream);
								}
								$subChunks[$y] = new SubChunk(BlockLegacyIds::AIR << Block::INTERNAL_METADATA_BITS, $storages);
							}
							break;
						default:
							//TODO: set chunks read-only so the version on disk doesn't get overwritten
							throw new CorruptedChunkException("don't know how to decode LevelDB subchunk format version $subChunkVersion");
					}
				}

				if(($maps2d = $this->db->get($index . self::TAG_DATA_2D)) !== false){
					$binaryStream = new BinaryStream($maps2d);

					try{
						$binaryStream->get(512); //heightmap, discard it
						$biomeArray = new BiomeArray($binaryStream->get(256)); //never throws
					}catch(BinaryDataException $e){
						throw new CorruptedChunkException($e->getMessage(), 0, $e);
					}
				}
				break;
			case 2: // < MCPE 1.0
			case 1:
			case 0: //MCPE 0.9.0.1 beta (first version)
				$convertedLegacyExtraData = $this->deserializeLegacyExtraData($index, $chunkVersion);

				$legacyTerrain = $this->db->get($index . self::TAG_LEGACY_TERRAIN);
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
					$storages = [SubChunkConverter::convertSubChunkFromLegacyColumn($fullIds, $fullData, $yy)];
					if(isset($convertedLegacyExtraData[$yy])){
						$storages[] = $convertedLegacyExtraData[$yy];
					}
					$subChunks[$yy] = new SubChunk(BlockLegacyIds::AIR << Block::INTERNAL_METADATA_BITS, $storages);
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
		if(($entityData = $this->db->get($index . self::TAG_ENTITY)) !== false and $entityData !== ""){
			try{
				$entities = array_map(function(TreeRoot $root) : CompoundTag{ return $root->mustGetCompoundTag(); }, $nbt->readMultiple($entityData));
			}catch(NbtDataException $e){
				throw new CorruptedChunkException($e->getMessage(), 0, $e);
			}
		}

		/** @var CompoundTag[] $tiles */
		$tiles = [];
		if(($tileData = $this->db->get($index . self::TAG_BLOCK_ENTITY)) !== false and $tileData !== ""){
			try{
				$tiles = array_map(function(TreeRoot $root) : CompoundTag{ return $root->mustGetCompoundTag(); }, $nbt->readMultiple($tileData));
			}catch(NbtDataException $e){
				throw new CorruptedChunkException($e->getMessage(), 0, $e);
			}
		}

		$chunk = new Chunk(
			$subChunks,
			$entities,
			$tiles,
			$biomeArray
		);

		//TODO: tile ticks, biome states (?)

		$chunk->setPopulated();
		if($hasBeenUpgraded){
			$chunk->setDirty(); //trigger rewriting chunk to disk if it was converted from an older format
		}

		return $chunk;
	}

	public function saveChunk(int $chunkX, int $chunkZ, Chunk $chunk) : void{
		$idMap = LegacyBlockIdToStringIdMap::getInstance();
		$index = LevelDB::chunkIndex($chunkX, $chunkZ);

		$write = new \LevelDBWriteBatch();
		$write->put($index . self::TAG_VERSION, chr(self::CURRENT_LEVEL_CHUNK_VERSION));

		if($chunk->getDirtyFlag(Chunk::DIRTY_FLAG_TERRAIN)){
			$subChunks = $chunk->getSubChunks();
			foreach($subChunks as $y => $subChunk){
				$key = $index . self::TAG_SUBCHUNK_PREFIX . chr($y);
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
						$subStream->putLInt(count($palette));
						$tags = [];
						foreach($palette as $p){
							$tags[] = new TreeRoot(CompoundTag::create()
								->setString("name", $idMap->legacyToString($p >> Block::INTERNAL_METADATA_BITS) ?? "minecraft:info_update")
								->setInt("oldid", $p >> Block::INTERNAL_METADATA_BITS) //PM only (debugging), vanilla doesn't have this
								->setShort("val", $p & Block::INTERNAL_METADATA_MASK));
						}

						$subStream->put((new LittleEndianNbtSerializer())->writeMultiple($tags));
					}

					$write->put($key, $subStream->getBuffer());
				}
			}
		}

		if($chunk->getDirtyFlag(Chunk::DIRTY_FLAG_BIOMES)){
			$write->put($index . self::TAG_DATA_2D, str_repeat("\x00", 512) . $chunk->getBiomeIdArray());
		}

		//TODO: use this properly
		$write->put($index . self::TAG_STATE_FINALISATION, chr(self::FINALISATION_DONE));

		$this->writeTags($chunk->getNBTtiles(), $index . self::TAG_BLOCK_ENTITY, $write);
		$this->writeTags($chunk->getNBTentities(), $index . self::TAG_ENTITY, $write);

		$write->delete($index . self::TAG_DATA_2D_LEGACY);
		$write->delete($index . self::TAG_LEGACY_TERRAIN);

		$this->db->write($write);
	}

	/**
	 * @param CompoundTag[]      $targets
	 */
	private function writeTags(array $targets, string $index, \LevelDBWriteBatch $write) : void{
		if(count($targets) > 0){
			$nbt = new LittleEndianNbtSerializer();
			$write->put($index, $nbt->writeMultiple(array_map(function(CompoundTag $tag) : TreeRoot{ return new TreeRoot($tag); }, $targets)));
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
			if(strlen($key) === 9 and substr($key, -1) === self::TAG_VERSION){
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
			if(strlen($key) === 9 and substr($key, -1) === self::TAG_VERSION){
				$count++;
			}
		}
		return $count;
	}
}
