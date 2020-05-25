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

namespace pocketmine\world\format\io;

use pocketmine\block\BlockLegacyIds;
use pocketmine\utils\BinaryStream;
use pocketmine\world\format\BiomeArray;
use pocketmine\world\format\Chunk;
use pocketmine\world\format\HeightArray;
use pocketmine\world\format\LightArray;
use pocketmine\world\format\PalettedBlockArray;
use pocketmine\world\format\SubChunk;
use function array_values;
use function count;
use function pack;
use function strlen;
use function unpack;

/**
 * This class provides a serializer used for transmitting chunks between threads.
 * The serialization format **is not intended for permanent storage** and may change without warning.
 */
final class FastChunkSerializer{
	private const FLAG_GENERATED = 1 << 0;
	private const FLAG_POPULATED = 1 << 1;
	private const FLAG_HAS_LIGHT = 1 << 2;

	private function __construct(){
		//NOOP
	}

	public static function serializeWithoutLight(Chunk $chunk) : string{
		return self::serialize($chunk, false);
	}

	/**
	 * Fast-serializes the chunk for passing between threads
	 * TODO: tiles and entities
	 */
	public static function serialize(Chunk $chunk, bool $includeLight = true) : string{
		$includeLight = $includeLight && $chunk->isLightPopulated();

		$stream = new BinaryStream();
		$stream->putInt($chunk->getX());
		$stream->putInt($chunk->getZ());
		$stream->putByte(
			($includeLight ? self::FLAG_HAS_LIGHT : 0) |
			($chunk->isPopulated() ? self::FLAG_POPULATED : 0) |
			($chunk->isGenerated() ? self::FLAG_GENERATED : 0)
		);
		if($chunk->isGenerated()){
			//subchunks
			$subChunks = $chunk->getSubChunks();
			$count = $subChunks->count();
			$stream->putByte($count);

			foreach($subChunks as $y => $subChunk){
				$stream->putByte($y);
				$layers = $subChunk->getBlockLayers();
				$stream->putByte(count($layers));
				foreach($layers as $blocks){
					$wordArray = $blocks->getWordArray();
					$palette = $blocks->getPalette();

					$stream->putByte($blocks->getBitsPerBlock());
					$stream->put($wordArray);
					$serialPalette = pack("L*", ...$palette);
					$stream->putInt(strlen($serialPalette));
					$stream->put($serialPalette);
				}

				if($includeLight){
					$stream->put($subChunk->getBlockSkyLightArray()->getData());
					$stream->put($subChunk->getBlockLightArray()->getData());
				}
			}

			//biomes
			$stream->put($chunk->getBiomeIdArray());
			if($includeLight){
				$stream->put(pack("S*", ...$chunk->getHeightMapArray()));
			}
		}

		return $stream->getBuffer();
	}

	/**
	 * Deserializes a fast-serialized chunk
	 */
	public static function deserialize(string $data) : Chunk{
		$stream = new BinaryStream($data);

		$x = $stream->getInt();
		$z = $stream->getInt();
		$flags = $stream->getByte();
		$lightPopulated = (bool) ($flags & self::FLAG_HAS_LIGHT);
		$terrainPopulated = (bool) ($flags & self::FLAG_POPULATED);
		$terrainGenerated = (bool) ($flags & self::FLAG_GENERATED);

		$subChunks = [];
		$biomeIds = null;
		$heightMap = null;
		if($terrainGenerated){
			$count = $stream->getByte();
			for($subCount = 0; $subCount < $count; ++$subCount){
				$y = $stream->getByte();

				/** @var PalettedBlockArray[] $layers */
				$layers = [];
				for($i = 0, $layerCount = $stream->getByte(); $i < $layerCount; ++$i){
					$bitsPerBlock = $stream->getByte();
					$words = $stream->get(PalettedBlockArray::getExpectedWordArraySize($bitsPerBlock));
					$palette = array_values(unpack("L*", $stream->get($stream->getInt())));

					$layers[] = PalettedBlockArray::fromData($bitsPerBlock, $words, $palette);
				}
				$subChunks[$y] = new SubChunk(
					BlockLegacyIds::AIR << 4, $layers, $lightPopulated ? new LightArray($stream->get(2048)) : null, $lightPopulated ? new LightArray($stream->get(2048)) : null
				);
			}

			$biomeIds = new BiomeArray($stream->get(256));
			if($lightPopulated){
				$heightMap = new HeightArray(array_values(unpack("S*", $stream->get(512))));
			}
		}

		$chunk = new Chunk($x, $z, $subChunks, null, null, $biomeIds, $heightMap);
		$chunk->setGenerated($terrainGenerated);
		$chunk->setPopulated($terrainPopulated);
		$chunk->setLightPopulated($lightPopulated);

		return $chunk;
	}
}
