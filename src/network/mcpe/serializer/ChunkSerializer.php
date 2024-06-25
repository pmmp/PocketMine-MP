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

namespace pocketmine\network\mcpe\serializer;

use pocketmine\block\tile\Spawnable;
use pocketmine\data\bedrock\BiomeIds;
use pocketmine\data\bedrock\LegacyBiomeIdToStringIdMap;
use pocketmine\nbt\TreeRoot;
use pocketmine\network\mcpe\convert\BlockTranslator;
use pocketmine\network\mcpe\protocol\serializer\NetworkNbtSerializer;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;
use pocketmine\network\mcpe\protocol\types\DimensionIds;
use pocketmine\utils\Binary;
use pocketmine\utils\BinaryStream;
use pocketmine\world\format\Chunk;
use pocketmine\world\format\PalettedBlockArray;
use pocketmine\world\format\SubChunk;
use function count;

final class ChunkSerializer{
	private function __construct(){
		//NOOP
	}

	/**
	 * Returns the min/max subchunk index expected in the protocol.
	 * This has no relation to the world height supported by PM.
	 *
	 * @phpstan-param DimensionIds::* $dimensionId
	 * @return int[]
	 * @phpstan-return array{int, int}
	 */
	public static function getDimensionChunkBounds(int $dimensionId) : array{
		return match($dimensionId){
			DimensionIds::OVERWORLD => [-4, 19],
			DimensionIds::NETHER => [0, 7],
			DimensionIds::THE_END => [0, 15],
			default => throw new \InvalidArgumentException("Unknown dimension ID $dimensionId"),
		};
	}

	/**
	 * Returns the number of subchunks that will be sent from the given chunk.
	 * Chunks are sent in a stack, so every chunk below the top non-empty one must be sent.
	 *
	 * @phpstan-param DimensionIds::* $dimensionId
	 */
	public static function getSubChunkCount(Chunk $chunk, int $dimensionId) : int{
		//if the protocol world bounds ever exceed the PM supported bounds again in the future, we might need to
		//polyfill some stuff here
		[$minSubChunkIndex, $maxSubChunkIndex] = self::getDimensionChunkBounds($dimensionId);
		for($y = $maxSubChunkIndex, $count = $maxSubChunkIndex - $minSubChunkIndex + 1; $y >= $minSubChunkIndex; --$y, --$count){
			if($chunk->getSubChunk($y)->isEmptyFast()){
				continue;
			}
			return $count;
		}

		return 0;
	}

	/**
	 * @phpstan-param DimensionIds::* $dimensionId
	 */
	public static function serializeFullChunk(Chunk $chunk, int $dimensionId, BlockTranslator $blockTranslator, ?string $tiles = null) : string{
		$stream = PacketSerializer::encoder();

		$subChunkCount = self::getSubChunkCount($chunk, $dimensionId);
		$writtenCount = 0;

		[$minSubChunkIndex, $maxSubChunkIndex] = self::getDimensionChunkBounds($dimensionId);
		for($y = $minSubChunkIndex; $writtenCount < $subChunkCount; ++$y, ++$writtenCount){
			self::serializeSubChunk($chunk->getSubChunk($y), $blockTranslator, $stream, false);
		}

		$biomeIdMap = LegacyBiomeIdToStringIdMap::getInstance();
		//all biomes must always be written :(
		for($y = $minSubChunkIndex; $y <= $maxSubChunkIndex; ++$y){
			self::serializeBiomePalette($chunk->getSubChunk($y)->getBiomeArray(), $biomeIdMap, $stream);
		}

		$stream->putByte(0); //border block array count
		//Border block entry format: 1 byte (4 bits X, 4 bits Z). These are however useless since they crash the regular client.

		if($tiles !== null){
			$stream->put($tiles);
		}else{
			$stream->put(self::serializeTiles($chunk));
		}
		return $stream->getBuffer();
	}

	public static function serializeSubChunk(SubChunk $subChunk, BlockTranslator $blockTranslator, PacketSerializer $stream, bool $persistentBlockStates) : void{
		$layers = $subChunk->getBlockLayers();
		$stream->putByte(8); //version

		$stream->putByte(count($layers));

		$blockStateDictionary = $blockTranslator->getBlockStateDictionary();

		foreach($layers as $blocks){
			$bitsPerBlock = $blocks->getBitsPerBlock();
			$words = $blocks->getWordArray();
			$stream->putByte(($bitsPerBlock << 1) | ($persistentBlockStates ? 0 : 1));
			$stream->put($words);
			$palette = $blocks->getPalette();

			if($bitsPerBlock !== 0){
				//these LSHIFT by 1 uvarints are optimizations: the client expects zigzag varints here
				//but since we know they are always unsigned, we can avoid the extra fcall overhead of
				//zigzag and just shift directly.
				$stream->putUnsignedVarInt(count($palette) << 1); //yes, this is intentionally zigzag
			}
			if($persistentBlockStates){
				$nbtSerializer = new NetworkNbtSerializer();
				foreach($palette as $p){
					//TODO: introduce a binary cache for this
					$state = $blockStateDictionary->generateDataFromStateId($blockTranslator->internalIdToNetworkId($p));
					if($state === null){
						$state = $blockTranslator->getFallbackStateData();
					}

					$stream->put($nbtSerializer->write(new TreeRoot($state->toNbt())));
				}
			}else{
				foreach($palette as $p){
					$stream->put(Binary::writeUnsignedVarInt($blockTranslator->internalIdToNetworkId($p) << 1));
				}
			}
		}
	}

	private static function serializeBiomePalette(PalettedBlockArray $biomePalette, LegacyBiomeIdToStringIdMap $biomeIdMap, PacketSerializer $stream) : void{
		$biomePaletteBitsPerBlock = $biomePalette->getBitsPerBlock();
		$stream->putByte(($biomePaletteBitsPerBlock << 1) | 1); //the last bit is non-persistence (like for blocks), though it has no effect on biomes since they always use integer IDs
		$stream->put($biomePalette->getWordArray());

		//these LSHIFT by 1 uvarints are optimizations: the client expects zigzag varints here
		//but since we know they are always unsigned, we can avoid the extra fcall overhead of
		//zigzag and just shift directly.
		$biomePaletteArray = $biomePalette->getPalette();
		if($biomePaletteBitsPerBlock !== 0){
			$stream->putUnsignedVarInt(count($biomePaletteArray) << 1);
		}

		foreach($biomePaletteArray as $p){
			if($biomeIdMap->legacyToString($p) === null){
				//make sure we aren't sending bogus biomes - the 1.18.0 client crashes if we do this
				$p = BiomeIds::OCEAN;
			}
			$stream->put(Binary::writeUnsignedVarInt($p << 1));
		}
	}

	public static function serializeTiles(Chunk $chunk) : string{
		$stream = new BinaryStream();
		foreach($chunk->getTiles() as $tile){
			if($tile instanceof Spawnable){
				$stream->put($tile->getSerializedSpawnCompound()->getEncodedNbt());
			}
		}

		return $stream->getBuffer();
	}
}
