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
use pocketmine\level\format\io\ChunkUtils;
use pocketmine\level\format\io\exception\CorruptedChunkException;
use pocketmine\level\format\SubChunk;
use pocketmine\nbt\BigEndianNBTStream;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteArrayTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntArrayTag;
use pocketmine\nbt\tag\ListTag;
use function zlib_decode;

class Anvil extends McRegion{

	public const REGION_FILE_EXTENSION = "mca";

	protected function nbtSerialize(Chunk $chunk) : string{
		$nbt = new CompoundTag("Level", []);
		$nbt->setInt("xPos", $chunk->getX());
		$nbt->setInt("zPos", $chunk->getZ());

		$nbt->setByte("V", 1);
		$nbt->setLong("LastUpdate", 0); //TODO
		$nbt->setLong("InhabitedTime", 0); //TODO
		$nbt->setByte("TerrainPopulated", $chunk->isPopulated() ? 1 : 0);
		$nbt->setByte("LightPopulated", $chunk->isLightPopulated() ? 1 : 0);

		$subChunks = [];
		foreach($chunk->getSubChunks() as $y => $subChunk){
			if(!($subChunk instanceof SubChunk) or $subChunk->isEmpty()){
				continue;
			}

			$tag = $this->serializeSubChunk($subChunk);
			$tag->setByte("Y", $y);
			$subChunks[] = $tag;
		}
		$nbt->setTag(new ListTag("Sections", $subChunks, NBT::TAG_Compound));

		$nbt->setByteArray("Biomes", $chunk->getBiomeIdArray());
		$nbt->setIntArray("HeightMap", $chunk->getHeightMapArray());

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

		//TODO: TileTicks

		$writer = new BigEndianNBTStream();
		return $writer->writeCompressed(new CompoundTag("", [$nbt]), ZLIB_ENCODING_DEFLATE, RegionLoader::$COMPRESSION_LEVEL);
	}

	protected function serializeSubChunk(SubChunk $subChunk) : CompoundTag{
		return new CompoundTag("", [
			new ByteArrayTag("Blocks", ChunkUtils::reorderByteArray($subChunk->getBlockIdArray())), //Generic in-memory chunks are currently always XZY
			new ByteArrayTag("Data", ChunkUtils::reorderNibbleArray($subChunk->getBlockDataArray())),
			new ByteArrayTag("SkyLight", ChunkUtils::reorderNibbleArray($subChunk->getBlockSkyLightArray(), "\xff")),
			new ByteArrayTag("BlockLight", ChunkUtils::reorderNibbleArray($subChunk->getBlockLightArray()))
		]);
	}

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
		$subChunksTag = $chunk->getListTag("Sections") ?? [];
		foreach($subChunksTag as $subChunk){
			if($subChunk instanceof CompoundTag){
				$subChunks[$subChunk->getByte("Y")] = $this->deserializeSubChunk($subChunk);
			}
		}

		if($chunk->hasTag("BiomeColors", IntArrayTag::class)){
			$biomeIds = ChunkUtils::convertBiomeColors($chunk->getIntArray("BiomeColors")); //Convert back to original format
		}else{
			$biomeIds = $chunk->getByteArray("Biomes", "", true);
		}

		$result = new Chunk(
			$chunk->getInt("xPos"),
			$chunk->getInt("zPos"),
			$subChunks,
			$chunk->hasTag("Entities", ListTag::class) ? self::getCompoundList("Entities", $chunk->getListTag("Entities")) : [],
			$chunk->hasTag("TileEntities", ListTag::class) ? self::getCompoundList("TileEntities", $chunk->getListTag("TileEntities")) : [],
			$biomeIds,
			$chunk->getIntArray("HeightMap", [])
		);
		$result->setLightPopulated($chunk->getByte("LightPopulated", 0) !== 0);
		$result->setPopulated($chunk->getByte("TerrainPopulated", 0) !== 0);
		$result->setGenerated();
		$result->setChanged(false);
		return $result;
	}

	protected function deserializeSubChunk(CompoundTag $subChunk) : SubChunk{
		return new SubChunk(
			ChunkUtils::reorderByteArray($subChunk->getByteArray("Blocks")),
			ChunkUtils::reorderNibbleArray($subChunk->getByteArray("Data")),
			ChunkUtils::reorderNibbleArray($subChunk->getByteArray("SkyLight"), "\xff"),
			ChunkUtils::reorderNibbleArray($subChunk->getByteArray("BlockLight"))
		);
	}

	public static function getProviderName() : string{
		return "anvil";
	}

	public static function getPcWorldFormatVersion() : int{
		return 19133; //anvil
	}

	public function getWorldHeight() : int{
		//TODO: add world height options
		return 256;
	}
}
