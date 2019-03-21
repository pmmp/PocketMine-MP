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
use pocketmine\nbt\NbtDataException;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntArrayTag;
use pocketmine\nbt\tag\ListTag;

/**
 * Trait containing I/O methods for handling legacy Anvil-style chunks.
 *
 * Motivation: In the future PMAnvil will become a legacy read-only format, but Anvil will continue to exist for the sake
 * of handling worlds in the PC 1.13 format. Thus, we don't want PMAnvil getting accidentally influenced by changes
 * happening to the underlying Anvil, because it only uses the legacy part.
 *
 * TODO: When the formats are deprecated, the write parts of this trait can be eliminated.
 *
 * @internal
 */
trait LegacyAnvilChunkTrait{

	protected function serializeChunk(Chunk $chunk) : string{
		throw new \RuntimeException("Unsupported");
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
			$chunk = $nbt->readCompressed($data)->getTag();
		}catch(NbtDataException $e){
			throw new CorruptedChunkException($e->getMessage(), 0, $e);
		}
		if(!$chunk->hasTag("Level")){
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
			$chunk->hasTag("Entities", ListTag::class) ? $chunk->getListTag("Entities")->getValue() : [],
			$chunk->hasTag("TileEntities", ListTag::class) ? $chunk->getListTag("TileEntities")->getValue() : [],
			$biomeIds
		);
		$result->setPopulated($chunk->getByte("TerrainPopulated", 0) !== 0);
		$result->setGenerated();
		return $result;
	}

	abstract protected function deserializeSubChunk(CompoundTag $subChunk) : SubChunk;

}
