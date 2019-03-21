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

namespace pocketmine\level\format\io\leveldb;

use pocketmine\block\BlockIds;
use pocketmine\level\format\Chunk;
use pocketmine\level\format\io\BaseLevelProvider;
use pocketmine\level\format\io\ChunkUtils;
use pocketmine\level\format\io\data\BedrockLevelData;
use pocketmine\level\format\io\exception\CorruptedChunkException;
use pocketmine\level\format\io\exception\UnsupportedChunkFormatException;
use pocketmine\level\format\io\exception\UnsupportedLevelFormatException;
use pocketmine\level\format\io\LevelData;
use pocketmine\level\format\io\SubChunkConverter;
use pocketmine\level\format\io\WritableLevelProvider;
use pocketmine\level\format\PalettedBlockArray;
use pocketmine\level\format\SubChunk;
use pocketmine\level\generator\Generator;
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\nbt\NbtDataException;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\TreeRoot;
use pocketmine\utils\Binary;
use pocketmine\utils\BinaryDataException;
use pocketmine\utils\BinaryStream;
use pocketmine\utils\Utils;
use function array_flip;
use function array_map;
use function array_values;
use function chr;
use function count;
use function defined;
use function extension_loaded;
use function file_exists;
use function file_get_contents;
use function is_dir;
use function json_decode;
use function mkdir;
use function ord;
use function str_repeat;
use function strlen;
use function substr;
use function unpack;
use const LEVELDB_ZLIB_RAW_COMPRESSION;

class LevelDB extends BaseLevelProvider implements WritableLevelProvider{

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
			throw new UnsupportedLevelFormatException("The leveldb PHP extension is required to use this world format");
		}

		if(!defined('LEVELDB_ZLIB_RAW_COMPRESSION')){
			throw new UnsupportedLevelFormatException("Given version of php-leveldb doesn't support zlib raw compression");
		}
	}

	private static function createDB(string $path) : \LevelDB{
		return new \LevelDB($path . "/db", [
			"compression" => LEVELDB_ZLIB_RAW_COMPRESSION
		]);
	}

	public function __construct(string $path){
		self::checkForLevelDBExtension();
		parent::__construct($path);

		$this->db = self::createDB($path);
	}

	protected function loadLevelData() : LevelData{
		return new BedrockLevelData($this->getPath() . "level.dat");
	}

	public function getWorldHeight() : int{
		return 256;
	}

	public static function isValid(string $path) : bool{
		return file_exists($path . "/level.dat") and is_dir($path . "/db/");
	}

	public static function generate(string $path, string $name, int $seed, string $generator, array $options = []) : void{
		Utils::testValidInstance($generator, Generator::class);
		self::checkForLevelDBExtension();

		if(!file_exists($path . "/db")){
			mkdir($path . "/db", 0777, true);
		}

		BedrockLevelData::generate($path, $name, $seed, $generator, $options);
	}

	protected function deserializePaletted(BinaryStream $stream) : PalettedBlockArray{
		static $stringToLegacyId = null;
		if($stringToLegacyId === null){
			$stringToLegacyId = json_decode(file_get_contents(\pocketmine\RESOURCE_PATH . '/legacy_id_map.json'), true);
		}

		$bitsPerBlock = $stream->getByte() >> 1;

		try{
			$words = $stream->get(PalettedBlockArray::getExpectedWordArraySize($bitsPerBlock));
		}catch(\InvalidArgumentException $e){
			throw new CorruptedChunkException("Failed to deserialize paletted storage: " . $e->getMessage(), 0, $e);
		}
		$nbt = new LittleEndianNbtSerializer();
		$palette = [];
		for($i = 0, $paletteSize = $stream->getLInt(); $i < $paletteSize; ++$i){
			$offset = $stream->getOffset();
			$tag = $nbt->read($stream->getBuffer(), $offset)->getTag();
			$stream->setOffset($offset);

			$id = $stringToLegacyId[$tag->getString("name")] ?? BlockIds::INFO_UPDATE;
			$data = $tag->getShort("val");
			$palette[] = ($id << 4) | $data;
		}

		//TODO: exceptions
		return PalettedBlockArray::fromData($bitsPerBlock, $words, $palette);
	}

	/**
	 * @param int $chunkX
	 * @param int $chunkZ
	 *
	 * @return Chunk|null
	 * @throws CorruptedChunkException
	 * @throws UnsupportedChunkFormatException
	 */
	protected function readChunk(int $chunkX, int $chunkZ) : ?Chunk{
		$index = LevelDB::chunkIndex($chunkX, $chunkZ);

		if(!$this->chunkExists($chunkX, $chunkZ)){
			return null;
		}

		/** @var SubChunk[] $subChunks */
		$subChunks = [];

		/** @var string $biomeIds */
		$biomeIds = "";

		$chunkVersion = ord($this->db->get($index . self::TAG_VERSION));
		$hasBeenUpgraded = $chunkVersion < self::CURRENT_LEVEL_CHUNK_VERSION;

		$binaryStream = new BinaryStream();

		switch($chunkVersion){
			case 10: //MCPE 1.9 (???)
			case 9: //MCPE 1.8 (???)
			case 7: //MCPE 1.2 (???)
			case 4: //MCPE 1.1
				//TODO: check beds
			case 3: //MCPE 1.0
				for($y = 0; $y < Chunk::MAX_SUBCHUNKS; ++$y){
					if(($data = $this->db->get($index . self::TAG_SUBCHUNK_PREFIX . chr($y))) === false){
						continue;
					}

					$binaryStream->setBuffer($data);
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

							$subChunks[$y] = new SubChunk([SubChunkConverter::convertSubChunkXZY($blocks, $blockData)]);
							break;
						case 1: //paletted v1, has a single blockstorage
							$subChunks[$y] = new SubChunk([$this->deserializePaletted($binaryStream)]);
							break;
						case 8:
							$storages = [];
							for($k = 0, $storageCount = $binaryStream->getByte(); $k < $storageCount; ++$k){
								$storages[] = $this->deserializePaletted($binaryStream);
							}
							$subChunks[$y] = new SubChunk($storages);
							break;
						default:
							//TODO: set chunks read-only so the version on disk doesn't get overwritten
							throw new UnsupportedChunkFormatException("don't know how to decode LevelDB subchunk format version $subChunkVersion");
					}
				}

				if(($maps2d = $this->db->get($index . self::TAG_DATA_2D)) !== false){
					$binaryStream->setBuffer($maps2d);

					try{
						$binaryStream->get(512); //heightmap, discard it
						$biomeIds = $binaryStream->get(256);
					}catch(BinaryDataException $e){
						throw new CorruptedChunkException($e->getMessage(), 0, $e);
					}
				}
				break;
			case 2: // < MCPE 1.0
				$legacyTerrain = $this->db->get($index . self::TAG_LEGACY_TERRAIN);
				if($legacyTerrain === false){
					throw new CorruptedChunkException("Missing expected LEGACY_TERRAIN tag for format version $chunkVersion");
				}
				$binaryStream->setBuffer($legacyTerrain);
				try{
					$fullIds = $binaryStream->get(32768);
					$fullData = $binaryStream->get(16384);
					$binaryStream->get(32768); //legacy light info, discard it
				}catch(BinaryDataException $e){
					throw new CorruptedChunkException($e->getMessage(), 0, $e);
				}

				for($yy = 0; $yy < 8; ++$yy){
					$subChunks[$yy] = new SubChunk([SubChunkConverter::convertSubChunkFromLegacyColumn($fullIds, $fullData, $yy)]);
				}

				try{
					$binaryStream->get(256); //heightmap, discard it
					$biomeIds = ChunkUtils::convertBiomeColors(array_values(unpack("N*", $binaryStream->get(1024))));
				}catch(BinaryDataException $e){
					throw new CorruptedChunkException($e->getMessage(), 0, $e);
				}
				break;
			default:
				//TODO: set chunks read-only so the version on disk doesn't get overwritten
				throw new UnsupportedChunkFormatException("don't know how to decode chunk format version $chunkVersion");
		}

		$nbt = new LittleEndianNbtSerializer();

		/** @var CompoundTag[] $entities */
		$entities = [];
		if(($entityData = $this->db->get($index . self::TAG_ENTITY)) !== false and $entityData !== ""){
			try{
				$entities = array_map(function(TreeRoot $root) : CompoundTag{ return $root->getTag(); }, $nbt->readMultiple($entityData));
			}catch(NbtDataException $e){
				throw new CorruptedChunkException($e->getMessage(), 0, $e);
			}
		}

		/** @var CompoundTag[] $tiles */
		$tiles = [];
		if(($tileData = $this->db->get($index . self::TAG_BLOCK_ENTITY)) !== false and $tileData !== ""){
			try{
				$tiles = array_map(function(TreeRoot $root) : CompoundTag{ return $root->getTag(); }, $nbt->readMultiple($tileData));
			}catch(NbtDataException $e){
				throw new CorruptedChunkException($e->getMessage(), 0, $e);
			}
		}

		//TODO: extra data should be converted into blockstorage layers (first they need to be implemented!)
		/*
		$extraData = [];
		if(($extraRawData = $this->db->get($index . self::TAG_BLOCK_EXTRA_DATA)) !== false and $extraRawData !== ""){
			$binaryStream->setBuffer($extraRawData, 0);
			$count = $binaryStream->getLInt();
			for($i = 0; $i < $count; ++$i){
				$key = $binaryStream->getLInt();
				$value = $binaryStream->getLShort();
				$extraData[$key] = $value;
			}
		}*/

		$chunk = new Chunk(
			$chunkX,
			$chunkZ,
			$subChunks,
			$entities,
			$tiles,
			$biomeIds
		);

		//TODO: tile ticks, biome states (?)

		$chunk->setGenerated();
		$chunk->setPopulated();
		$chunk->setChanged($hasBeenUpgraded); //trigger rewriting chunk to disk if it was converted from an older format

		return $chunk;
	}

	protected function writeChunk(Chunk $chunk) : void{
		static $idMap = null;
		if($idMap === null){
			$idMap = array_flip(json_decode(file_get_contents(\pocketmine\RESOURCE_PATH . '/legacy_id_map.json'), true));
		}
		$index = LevelDB::chunkIndex($chunk->getX(), $chunk->getZ());

		$write = new \LevelDBWriteBatch();
		$write->put($index . self::TAG_VERSION, chr(self::CURRENT_LEVEL_CHUNK_VERSION));

		$subChunks = $chunk->getSubChunks();
		foreach($subChunks as $y => $subChunk){
			$key = $index . self::TAG_SUBCHUNK_PREFIX . chr($y);
			if($subChunk->isEmpty(false)){ //MCPE doesn't save light anymore as of 1.1
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
							->setString("name", $idMap[$p >> 4] ?? "minecraft:info_update")
							->setInt("oldid", $p >> 4) //PM only (debugging), vanilla doesn't have this
							->setShort("val", $p & 0xf));
					}

					$subStream->put((new LittleEndianNbtSerializer())->writeMultiple($tags));
				}

				$write->put($key, $subStream->getBuffer());
			}
		}

		$write->put($index . self::TAG_DATA_2D, str_repeat("\x00", 512) . $chunk->getBiomeIdArray());

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
	 * @param string             $index
	 * @param \LevelDBWriteBatch $write
	 */
	private function writeTags(array $targets, string $index, \LevelDBWriteBatch $write) : void{
		if(!empty($targets)){
			$nbt = new LittleEndianNbtSerializer();
			$write->put($index, $nbt->writeMultiple(array_map(function(CompoundTag $tag) : TreeRoot{ return new TreeRoot($tag); }, $targets)));
		}else{
			$write->delete($index);
		}
	}

	/**
	 * @return \LevelDB
	 */
	public function getDatabase() : \LevelDB{
		return $this->db;
	}

	public static function chunkIndex(int $chunkX, int $chunkZ) : string{
		return Binary::writeLInt($chunkX) . Binary::writeLInt($chunkZ);
	}

	private function chunkExists(int $chunkX, int $chunkZ) : bool{
		return $this->db->get(LevelDB::chunkIndex($chunkX, $chunkZ) . self::TAG_VERSION) !== false;
	}

	public function doGarbageCollection() : void{

	}

	public function close() : void{
		$this->db->close();
	}

	public function getAllChunks(bool $skipCorrupted = false, ?\Logger $logger = null) : \Generator{
		foreach($this->db->getIterator() as $key => $_){
			if(strlen($key) === 9 and substr($key, -1) === self::TAG_VERSION){
				$chunkX = Binary::readLInt(substr($key, 0, 4));
				$chunkZ = Binary::readLInt(substr($key, 4, 4));
				try{
					if(($chunk = $this->loadChunk($chunkX, $chunkZ)) !== null){
						yield $chunk;
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
