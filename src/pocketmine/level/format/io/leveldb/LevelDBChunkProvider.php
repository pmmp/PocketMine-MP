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

use pocketmine\level\format\Chunk;
use pocketmine\level\format\io\ChunkUtils;
use pocketmine\level\format\io\exception\UnsupportedChunkFormatException;
use pocketmine\level\format\io\InternalChunkProvider;
use pocketmine\level\format\SubChunk;
use pocketmine\nbt\LittleEndianNBTStream;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\utils\Binary;
use pocketmine\utils\BinaryStream;
use pocketmine\utils\MainLogger;

class LevelDBChunkProvider implements InternalChunkProvider{
	/** @var string */
	private $path;
	/** @var \LevelDB */
	private $db;

	public function __construct(string $path){
		$this->path = $path;

		$this->db = new \LevelDB($this->path . "/db", [
			"compression" => LEVELDB_ZLIB_RAW_COMPRESSION
		]);
	}

	public function doGarbageCollection() : void{

	}

	/**
	 * @param int $chunkX
	 * @param int $chunkZ
	 *
	 * @return Chunk|null
	 */
	public function readChunk(int $chunkX, int $chunkZ) : ?Chunk{
		$index = self::chunkIndex($chunkX, $chunkZ);

		if(!$this->chunkExists($chunkX, $chunkZ)){
			return null;
		}

		try{
			/** @var SubChunk[] $subChunks */
			$subChunks = [];

			/** @var bool $lightPopulated */
			$lightPopulated = true;

			$chunkVersion = ord($this->db->get($index . LevelDB::TAG_VERSION));

			$binaryStream = new BinaryStream();

			switch($chunkVersion){
				case 7: //MCPE 1.2 (???)
				case 4: //MCPE 1.1
					//TODO: check beds
				case 3: //MCPE 1.0
					for($y = 0; $y < Chunk::MAX_SUBCHUNKS; ++$y){
						if(($data = $this->db->get($index . LevelDB::TAG_SUBCHUNK_PREFIX . chr($y))) === false){
							continue;
						}

						$binaryStream->setBuffer($data, 0);
						$subChunkVersion = $binaryStream->getByte();

						switch($subChunkVersion){
							case 0:
								$blocks = $binaryStream->get(4096);
								$blockData = $binaryStream->get(2048);
								if($chunkVersion < 4){
									$blockSkyLight = $binaryStream->get(2048);
									$blockLight = $binaryStream->get(2048);
								}else{
									//Mojang didn't bother changing the subchunk version when they stopped saving sky light -_-
									$blockSkyLight = "";
									$blockLight = "";
									$lightPopulated = false;
								}

								$subChunks[$y] = new SubChunk($blocks, $blockData, $blockSkyLight, $blockLight);
								break;
							default:
								throw new UnsupportedChunkFormatException("don't know how to decode LevelDB subchunk format version $subChunkVersion");
						}
					}

					$binaryStream->setBuffer($this->db->get($index . LevelDB::TAG_DATA_2D), 0);

					$heightMap = array_values(unpack("v*", $binaryStream->get(512)));
					$biomeIds = $binaryStream->get(256);
					break;
				case 2: // < MCPE 1.0
					$binaryStream->setBuffer($this->db->get($index . LevelDB::TAG_LEGACY_TERRAIN));
					$fullIds = $binaryStream->get(32768);
					$fullData = $binaryStream->get(16384);
					$fullSkyLight = $binaryStream->get(16384);
					$fullBlockLight = $binaryStream->get(16384);

					for($yy = 0; $yy < 8; ++$yy){
						$subOffset = ($yy << 4);
						$ids = "";
						for($i = 0; $i < 256; ++$i){
							$ids .= substr($fullIds, $subOffset, 16);
							$subOffset += 128;
						}
						$data = "";
						$subOffset = ($yy << 3);
						for($i = 0; $i < 256; ++$i){
							$data .= substr($fullData, $subOffset, 8);
							$subOffset += 64;
						}
						$skyLight = "";
						$subOffset = ($yy << 3);
						for($i = 0; $i < 256; ++$i){
							$skyLight .= substr($fullSkyLight, $subOffset, 8);
							$subOffset += 64;
						}
						$blockLight = "";
						$subOffset = ($yy << 3);
						for($i = 0; $i < 256; ++$i){
							$blockLight .= substr($fullBlockLight, $subOffset, 8);
							$subOffset += 64;
						}
						$subChunks[$yy] = new SubChunk($ids, $data, $skyLight, $blockLight);
					}

					$heightMap = array_values(unpack("C*", $binaryStream->get(256)));
					$biomeIds = ChunkUtils::convertBiomeColors(array_values(unpack("N*", $binaryStream->get(1024))));
					break;
				default:
					throw new UnsupportedChunkFormatException("don't know how to decode chunk format version $chunkVersion");
			}

			$nbt = new LittleEndianNBTStream();

			$entities = [];
			if(($entityData = $this->db->get($index . LevelDB::TAG_ENTITY)) !== false and strlen($entityData) > 0){
				$nbt->read($entityData, true);
				$entities = $nbt->getData();
				if(!is_array($entities)){
					$entities = [$entities];
				}
			}

			foreach($entities as $entityNBT){
				if($entityNBT->id instanceof IntTag){
					$entityNBT["id"] &= 0xff;
				}
			}

			$tiles = [];
			if(($tileData = $this->db->get($index . LevelDB::TAG_BLOCK_ENTITY)) !== false and strlen($tileData) > 0){
				$nbt->read($tileData, true);
				$tiles = $nbt->getData();
				if(!is_array($tiles)){
					$tiles = [$tiles];
				}
			}

			$extraData = [];
			if(($extraRawData = $this->db->get($index . LevelDB::TAG_BLOCK_EXTRA_DATA)) !== false and strlen($extraRawData) > 0){
				$binaryStream->setBuffer($extraRawData, 0);
				$count = $binaryStream->getLInt();
				for($i = 0; $i < $count; ++$i){
					$key = $binaryStream->getLInt();
					$value = $binaryStream->getLShort();
					$extraData[$key] = $value;
				}
			}

			$chunk = new Chunk(
				$chunkX,
				$chunkZ,
				$subChunks,
				$entities,
				$tiles,
				$biomeIds,
				$heightMap,
				$extraData
			);

			//TODO: tile ticks, biome states (?)

			$chunk->setGenerated(true);
			$chunk->setPopulated(true);
			$chunk->setLightPopulated($lightPopulated);

			return $chunk;
		}catch(UnsupportedChunkFormatException $e){
			//TODO: set chunks read-only so the version on disk doesn't get overwritten

			$logger = MainLogger::getLogger();
			$logger->error("Failed to decode LevelDB chunk: " . $e->getMessage());

			return null;
		}catch(\Throwable $t){
			$logger = MainLogger::getLogger();
			$logger->error("LevelDB chunk decode error");
			$logger->logException($t);

			return null;

		}
	}

	public function writeChunk(Chunk $chunk) : void{
		$index = self::chunkIndex($chunk->getX(), $chunk->getZ());
		$this->db->put($index . LevelDB::TAG_VERSION, chr(LevelDB::CURRENT_LEVEL_CHUNK_VERSION));

		$subChunks = $chunk->getSubChunks();
		foreach($subChunks as $y => $subChunk){
			$key = $index . LevelDB::TAG_SUBCHUNK_PREFIX . chr($y);
			if($subChunk->isEmpty(false)){ //MCPE doesn't save light anymore as of 1.1
				$this->db->delete($key);
			}else{
				$this->db->put($key,
							   chr(LevelDB::CURRENT_LEVEL_SUBCHUNK_VERSION) .
							   $subChunk->getBlockIdArray() .
							   $subChunk->getBlockDataArray()
				);
			}
		}

		$this->db->put($index . LevelDB::TAG_DATA_2D, pack("v*", ...$chunk->getHeightMapArray()) . $chunk->getBiomeIdArray());

		$extraData = $chunk->getBlockExtraDataArray();
		if(count($extraData) > 0){
			$stream = new BinaryStream();
			$stream->putLInt(count($extraData));
			foreach($extraData as $key => $value){
				$stream->putLInt($key);
				$stream->putLShort($value);
			}

			$this->db->put($index . LevelDB::TAG_BLOCK_EXTRA_DATA, $stream->getBuffer());
		}else{
			$this->db->delete($index . LevelDB::TAG_BLOCK_EXTRA_DATA);
		}

		//TODO: use this properly
		$this->db->put($index . LevelDB::TAG_STATE_FINALISATION, chr(LevelDB::FINALISATION_DONE));

		$this->writeTags($chunk->NBTtiles, $index . LevelDB::TAG_BLOCK_ENTITY);
		$this->writeTags($chunk->NBTentities, $index . LevelDB::TAG_ENTITY);

		$this->db->delete($index . LevelDB::TAG_DATA_2D_LEGACY);
		$this->db->delete($index . LevelDB::TAG_LEGACY_TERRAIN);
	}

	/**
	 * @param CompoundTag[] $targets
	 * @param string        $index
	 */
	private function writeTags(array $targets, string $index){
		if(!empty($targets)){
			$nbt = new LittleEndianNBTStream();
			$nbt->setData($targets);
			$this->db->put($index, $nbt->write());
		}else{
			$this->db->delete($index);
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
		return $this->db->get(self::chunkIndex($chunkX, $chunkZ) . LevelDB::TAG_VERSION) !== false;
	}

	public function close() : void{
		$this->db->close();
	}
}
