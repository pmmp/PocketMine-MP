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
use pocketmine\nbt\BigEndianNbtSerializer;
use pocketmine\nbt\NBT;
use pocketmine\nbt\NbtDataException;
use pocketmine\nbt\tag\ByteArrayTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntArrayTag;
use pocketmine\nbt\tag\ListTag;
use function array_values;
use function pack;
use function str_repeat;
use function substr;
use function unpack;

class McRegion extends RegionLevelProvider{

	/**
	 * @param Chunk $chunk
	 *
	 * @return string
	 */
	protected function serializeChunk(Chunk $chunk) : string{
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
					$ids .= substr($subChunk->getBlockIdArray(), ($x << 8) | ($z << 4), 16);
					$data .= substr($subChunk->getBlockDataArray(), ($x << 7) | ($z << 3), 8);
					$skyLight .= substr($subChunk->getBlockSkyLightArray(), ($x << 7) | ($z << 3), 8);
					$blockLight .= substr($subChunk->getBlockLightArray(), ($x << 7) | ($z << 3), 8);
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
			$entities[] = $entity->saveNBT();
		}

		$nbt->setTag(new ListTag("Entities", $entities, NBT::TAG_Compound));

		$tiles = [];
		foreach($chunk->getTiles() as $tile){
			$tiles[] = $tile->saveNBT();
		}

		$nbt->setTag(new ListTag("TileEntities", $tiles, NBT::TAG_Compound));

		$writer = new BigEndianNbtSerializer();
		return $writer->writeCompressed(new CompoundTag("", [$nbt]), ZLIB_ENCODING_DEFLATE, RegionLoader::$COMPRESSION_LEVEL);
	}

	/**
	 * @param string $data
	 *
	 * @return Chunk
	 * @throws CorruptedChunkException
	 */
	protected function deserializeChunk(string $data) : Chunk{
		$nbt = new BigEndianNbtSerializer();
		try{
			$chunk = $nbt->readCompressed($data);
		}catch(NbtDataException $e){
			throw new CorruptedChunkException($e->getMessage(), 0, $e);
		}
		if(!$chunk->hasTag("Level")){
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

	protected static function getRegionFileExtension() : string{
		return "mcr";
	}

	protected static function getPcWorldFormatVersion() : int{
		return 19132;
	}

	public function getWorldHeight() : int{
		//TODO: add world height options
		return 128;
	}
}
