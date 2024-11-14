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

namespace pocketmine\world\format\io\region;

use pocketmine\block\Block;
use pocketmine\data\bedrock\BiomeIds;
use pocketmine\nbt\BigEndianNbtSerializer;
use pocketmine\nbt\NbtDataException;
use pocketmine\nbt\tag\ByteArrayTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntArrayTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\world\format\Chunk;
use pocketmine\world\format\io\ChunkData;
use pocketmine\world\format\io\ChunkUtils;
use pocketmine\world\format\io\exception\CorruptedChunkException;
use pocketmine\world\format\io\LoadedChunkData;
use pocketmine\world\format\PalettedBlockArray;
use pocketmine\world\format\SubChunk;
use function strlen;
use function zlib_decode;

class McRegion extends RegionWorldProvider{
	/**
	 * @throws CorruptedChunkException
	 */
	protected function deserializeChunk(string $data, \Logger $logger) : ?LoadedChunkData{
		$decompressed = @zlib_decode($data);
		if($decompressed === false){
			throw new CorruptedChunkException("Failed to decompress chunk NBT");
		}
		$nbt = new BigEndianNbtSerializer();
		try{
			$chunk = $nbt->read($decompressed)->mustGetCompoundTag();
		}catch(NbtDataException $e){
			throw new CorruptedChunkException($e->getMessage(), 0, $e);
		}
		$chunk = $chunk->getTag("Level");
		if(!($chunk instanceof CompoundTag)){
			throw new CorruptedChunkException("'Level' key is missing from chunk NBT");
		}

		$legacyGeneratedTag = $chunk->getTag("TerrainGenerated");
		if($legacyGeneratedTag instanceof ByteTag && $legacyGeneratedTag->getValue() === 0){
			//In legacy PM before 3.0, PM used to save MCRegion chunks even when they weren't generated. In these cases
			//(we'll see them in old worlds), some of the tags which we expect to always be present, will be missing.
			//If TerrainGenerated (PM-specific tag from the olden days) is false, toss the chunk data and don't bother
			//trying to read it.
			return null;
		}

		$makeBiomeArray = function(string $biomeIds) : PalettedBlockArray{
			if(strlen($biomeIds) !== 256){
				throw new CorruptedChunkException("Expected biome array to be exactly 256 bytes, got " . strlen($biomeIds));
			}
			return ChunkUtils::extrapolate3DBiomes($biomeIds);
		};
		if(($biomeColorsTag = $chunk->getTag("BiomeColors")) instanceof IntArrayTag){
			$biomes3d = $makeBiomeArray(ChunkUtils::convertBiomeColors($biomeColorsTag->getValue())); //Convert back to original format
		}elseif(($biomesTag = $chunk->getTag("Biomes")) instanceof ByteArrayTag){
			$biomes3d = $makeBiomeArray($biomesTag->getValue());
		}else{
			$biomes3d = new PalettedBlockArray(BiomeIds::OCEAN);
		}

		$subChunks = [];
		$fullIds = self::readFixedSizeByteArray($chunk, "Blocks", 32768);
		$fullData = self::readFixedSizeByteArray($chunk, "Data", 16384);

		for($y = 0; $y < 8; ++$y){
			$subChunks[$y] = new SubChunk(Block::EMPTY_STATE_ID, [$this->palettizeLegacySubChunkFromColumn(
				$fullIds,
				$fullData,
				$y,
				new \PrefixedLogger($logger, "Subchunk y=$y"),
			)], clone $biomes3d);
		}
		for($y = Chunk::MIN_SUBCHUNK_INDEX; $y <= Chunk::MAX_SUBCHUNK_INDEX; ++$y){
			if(!isset($subChunks[$y])){
				$subChunks[$y] = new SubChunk(Block::EMPTY_STATE_ID, [], clone $biomes3d);
			}
		}

		return new LoadedChunkData(
			data: new ChunkData(
				$subChunks,
				$chunk->getByte("TerrainPopulated", 0) !== 0,
				($entitiesTag = $chunk->getTag("Entities")) instanceof ListTag ? self::getCompoundList("Entities", $entitiesTag) : [],
				($tilesTag = $chunk->getTag("TileEntities")) instanceof ListTag ? self::getCompoundList("TileEntities", $tilesTag) : [],
			),
			upgraded: true,
			fixerFlags: LoadedChunkData::FIXER_FLAG_ALL
		);
	}

	protected static function getRegionFileExtension() : string{
		return "mcr";
	}

	protected static function getPcWorldFormatVersion() : int{
		return 19132;
	}

	public function getWorldMinY() : int{
		return 0;
	}

	public function getWorldMaxY() : int{
		//TODO: add world height options
		return 128;
	}
}
