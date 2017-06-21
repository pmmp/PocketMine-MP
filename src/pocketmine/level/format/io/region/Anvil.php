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
use pocketmine\level\format\io\ChunkException;
use pocketmine\level\format\io\ChunkUtils;
use pocketmine\level\format\SubChunk;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\{
	ByteArrayTag, ByteTag, CompoundTag, IntArrayTag, IntTag, ListTag, LongTag
};
use pocketmine\Player;
use pocketmine\utils\MainLogger;

class Anvil extends McRegion{

	const REGION_FILE_EXTENSION = "mca";

	public function nbtSerialize(Chunk $chunk) : string{
		$nbt = new CompoundTag("Level", []);
		$nbt->setTag(new IntTag("xPos", $chunk->getX()));
		$nbt->setTag(new IntTag("zPos", $chunk->getZ()));

		$nbt->setTag(new ByteTag("V", 1));
		$nbt->setTag(new LongTag("LastUpdate", 0)); //TODO
		$nbt->setTag(new LongTag("InhabitedTime", 0)); //TODO
		$nbt->setTag(new ByteTag("TerrainPopulated", $chunk->isPopulated() ? 1 : 0));
		$nbt->setTag(new ByteTag("LightPopulated", $chunk->isLightPopulated() ? 1 : 0));

		$sections = [];
		foreach($chunk->getSubChunks() as $y => $subChunk){
			if($subChunk->isEmpty()){
				continue;
			}

			$subChunkTag = $this->writeSection($subChunk);
			$subChunkTag->putTag(new ByteTag("Y", $y));
			$sections[] = $subChunkTag;
		}
		$nbt->setTag(new ListTag("Sections", $sections, NBT::TAG_Compound));

		$nbt->setTag(new ByteArrayTag("Biomes", $chunk->getBiomeIdArray()));
		$nbt->setTag(new IntArrayTag("HeightMap", $chunk->getHeightMapArray()));

		$entities = [];

		foreach($chunk->getEntities() as $entity){
			if(!($entity instanceof Player) and !$entity->closed){
				$entity->saveNBT();
				$entities[] = $entity->namedtag;
			}
		}

		$nbt->setTag(new ListTag("Entities", $entities, NBT::TAG_Compound));

		$tiles = [];
		foreach($chunk->getTiles() as $tile){
			$tile->saveNBT();
			$tiles[] = $tile->namedtag;
		}

		$nbt->setTag(new ListTag("TileEntities", $tiles, NBT::TAG_Compound));

		//TODO: TileTicks

		$writer = new NBT(NBT::BIG_ENDIAN);
		$nbt->setName("Level");
		$writer->setData(new CompoundTag("", [$nbt]));

		return $writer->writeCompressed(ZLIB_ENCODING_DEFLATE, RegionLoader::$COMPRESSION_LEVEL);
	}

	public function nbtDeserialize(string $data){
		$nbt = new NBT(NBT::BIG_ENDIAN);
		try{
			$nbt->readCompressed($data);

			$chunk = $nbt->getData()->getTag("Level");

			if(!($chunk instanceof CompoundTag)){
				throw new ChunkException("Invalid NBT format");
			}

			$subChunks = [];

			$sectionsTag = $chunk->getTag("Sections");
			if($sectionsTag instanceof ListTag){
				foreach($sectionsTag as $subChunk){
					if($subChunk instanceof CompoundTag){
						$subChunks[$subChunk->getTag("Y")->getValue()] = $this->readSection($subChunk);
					}
				}
			}

			if($chunk->exists("BiomeColors")){
				$biomeIds = ChunkUtils::convertBiomeColors($chunk->getTag("BiomeColors")->getValue()); //Convert back to original format
			}elseif($chunk->exists("Biomes")){
				$biomeIds = $chunk->getTag("Biomes")->getValue();
			}else{
				$biomeIds = "";
			}

			$result = new Chunk(
				$chunk->getTag("xPos")->getValue(),
				$chunk->getTag("zPos")->getValue(),
				$subChunks,
				$chunk->exists("Entities") ? $chunk->getTag("Entities")->getValue() : [],
				$chunk->exists("TileEntities") ? $chunk->getTag("TileEntities")->getValue() : [],
				$biomeIds,
				$chunk->exists("HeightMap") ? $chunk->getTag("HeightMap")->getValue() : []
			);
			$result->setLightPopulated($chunk->exists("LightPopulated") ? $chunk->getTag("LightPopulated")->getValue() !== 0 : false);
			$result->setPopulated($chunk->exists("TerrainPopulated") ? $chunk->getTag("TerrainPopulated")->getValue() !== 0 : false);
			$result->setGenerated(true);
			return $result;
		}catch(\Throwable $e){
			MainLogger::getLogger()->logException($e);
			return null;
		}
	}

	protected function readSection(CompoundTag $tag) : SubChunk{
		return new SubChunk(
			ChunkUtils::reorderByteArray($tag->getTag("Blocks")->getValue()),
			ChunkUtils::reorderNibbleArray($tag->getTag("Data")->getValue()),
			ChunkUtils::reorderNibbleArray($tag->getTag("SkyLight")->getValue(), "\xff"),
			ChunkUtils::reorderNibbleArray($tag->getTag("BlockLight")->getValue())
		);
	}

	protected function writeSection(SubChunk $subChunk) : CompoundTag{
		return new CompoundTag("", [
			new ByteArrayTag("Blocks", ChunkUtils::reorderByteArray($subChunk->getBlockIdArray())),
			new ByteArrayTag("Data", ChunkUtils::reorderNibbleArray($subChunk->getBlockDataArray())),
			new ByteArrayTag("SkyLight", ChunkUtils::reorderNibbleArray($subChunk->getBlockSkyLightArray())),
			new ByteArrayTag("BlockLight", ChunkUtils::reorderNibbleArray($subChunk->getBlockLightArray()))
		]);
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