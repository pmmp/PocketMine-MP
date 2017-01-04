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

declare(strict_types = 1);

namespace pocketmine\level\format\region;

use pocketmine\level\format\generic\GenericChunk;
use pocketmine\level\format\generic\SubChunk;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\{
	ByteArrayTag,
	ByteTag,
	CompoundTag,
	IntArrayTag,
	IntTag,
	ListTag,
	LongTag
};
use pocketmine\Player;
use pocketmine\utils\ChunkException;
use pocketmine\utils\MainLogger;

class Anvil extends McRegion{

	const REGION_FILE_EXTENSION = "mca";

	public function nbtSerialize(GenericChunk $chunk) : string{
		$nbt = new CompoundTag("Level", []);
		$nbt->xPos = new IntTag("xPos", $chunk->getX());
		$nbt->zPos = new IntTag("zPos", $chunk->getZ());

		$nbt->V = new ByteTag("V", 1);
		$nbt->LastUpdate = new LongTag("LastUpdate", 0); //TODO
		$nbt->InhabitedTime = new LongTag("InhabitedTime", 0); //TODO
		$nbt->TerrainPopulated = new ByteTag("TerrainPopulated", $chunk->isPopulated());
		$nbt->LightPopulated = new ByteTag("LightPopulated", $chunk->isLightPopulated());

		$nbt->Sections = new ListTag("Sections", []);
		$nbt->Sections->setTagType(NBT::TAG_Compound);
		$subChunks = -1;
		foreach($chunk->getSubChunks() as $y => $subChunk){
			if($subChunk->isEmpty()){
				continue;
			}
			$nbt->Sections[++$subChunks] = new CompoundTag(null, [
				"Y"          => new ByteTag("Y", $y),
				"Blocks"     => new ByteArrayTag("Blocks",     GenericChunk::reorderByteArray($subChunk->getBlockIdArray())), //Generic in-memory chunks are currently always XZY
				"Data"       => new ByteArrayTag("Data",       GenericChunk::reorderNibbleArray($subChunk->getBlockDataArray())),
				"SkyLight"   => new ByteArrayTag("SkyLight",   GenericChunk::reorderNibbleArray($subChunk->getSkyLightArray())),
				"BlockLight" => new ByteArrayTag("BlockLight", GenericChunk::reorderNibbleArray($subChunk->getBlockLightArray()))
			]);
		}

		$nbt->Biomes = new ByteArrayTag("Biomes", $chunk->getBiomeIdArray());
		$nbt->HeightMap = new IntArrayTag("HeightMap", $chunk->getHeightMapArray());

		$entities = [];

		foreach($chunk->getEntities() as $entity){
			if(!($entity instanceof Player) and !$entity->closed){
				$entity->saveNBT();
				$entities[] = $entity->namedtag;
			}
		}

		$nbt->Entities = new ListTag("Entities", $entities);
		$nbt->Entities->setTagType(NBT::TAG_Compound);

		$tiles = [];
		foreach($chunk->getTiles() as $tile){
			$tile->saveNBT();
			$tiles[] = $tile->namedtag;
		}

		$nbt->TileEntities = new ListTag("TileEntities", $tiles);
		$nbt->TileEntities->setTagType(NBT::TAG_Compound);

		//TODO: TileTicks

		$writer = new NBT(NBT::BIG_ENDIAN);
		$nbt->setName("Level");
		$writer->setData(new CompoundTag("", ["Level" => $nbt]));

		return $writer->writeCompressed(ZLIB_ENCODING_DEFLATE, RegionLoader::$COMPRESSION_LEVEL);
	}

	public function nbtDeserialize(string $data){
		$nbt = new NBT(NBT::BIG_ENDIAN);
		try{
			$nbt->readCompressed($data, ZLIB_ENCODING_DEFLATE);

			$chunk = $nbt->getData();

			if(!isset($chunk->Level) or !($chunk->Level instanceof CompoundTag)){
				throw new ChunkException("Invalid NBT format");
			}

			$chunk = $chunk->Level;

			$subChunks = [];
			if($chunk->Sections instanceof ListTag){
				foreach($chunk->Sections as $subChunk){
					if($subChunk instanceof CompoundTag){
						$subChunks[$subChunk->Y->getValue()] = new SubChunk(
							GenericChunk::reorderByteArray($subChunk->Blocks->getValue()),
							GenericChunk::reorderNibbleArray($subChunk->Data->getValue()),
							GenericChunk::reorderNibbleArray($subChunk->SkyLight->getValue()),
							GenericChunk::reorderNibbleArray($subChunk->BlockLight->getValue())
						);
					}
				}
			}

			if(isset($chunk->BiomeColors)){
				$biomeIds = GenericChunk::convertBiomeColours($chunk->BiomeColors->getValue()); //Convert back to PC format (RIP colours D:)
			}elseif(isset($chunk->Biomes)){
				$biomeIds = $chunk->Biomes->getValue();
			}else{
				$biomeIds = "";
			}

			$result = new GenericChunk(
				$this,
				$chunk["xPos"],
				$chunk["zPos"],
				$subChunks,
				isset($chunk->Entities) ? $chunk->Entities->getValue() : [],
				isset($chunk->TileEntities) ? $chunk->TileEntities->getValue() : [],
				$biomeIds,
				isset($chunk->HeightMap) ? $chunk->HeightMap->getValue() : []
			);
			$result->setLightPopulated(isset($chunk->LightPopulated) ? ((bool) $chunk->LightPopulated->getValue()) : false);
			$result->setPopulated(isset($chunk->TerrainPopulated) ? ((bool) $chunk->TerrainPopulated->getValue()) : false);
			$result->setGenerated(true);
			return $result;
		}catch(\Throwable $e){
			MainLogger::getLogger()->logException($e);
			return null;
		}
	}

	public static function getProviderName() : string{
		return "anvil";
	}

	public function getWorldHeight() : int{
		//TODO: add world height options
		return 256;
	}

}