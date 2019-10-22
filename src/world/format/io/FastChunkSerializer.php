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
use pocketmine\world\format\Chunk;
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

	private function __construct(){
		//NOOP
	}

	/**
	 * Fast-serializes the chunk for passing between threads
	 * TODO: tiles and entities
	 *
	 * @param Chunk $chunk
	 *
	 * @return string
	 */
	public static function serialize(Chunk $chunk) : string{
		$stream = new BinaryStream();
		$stream->putInt($chunk->getX());
		$stream->putInt($chunk->getZ());
		$stream->putByte(($chunk->isLightPopulated() ? 4 : 0) | ($chunk->isPopulated() ? 2 : 0) | ($chunk->isGenerated() ? 1 : 0));
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
					$serialPalette = pack("N*", ...$palette);
					$stream->putInt(strlen($serialPalette));
					$stream->put($serialPalette);
				}

				if($chunk->isLightPopulated()){
					$stream->put($subChunk->getBlockSkyLightArray()->getData());
					$stream->put($subChunk->getBlockLightArray()->getData());
				}
			}

			//biomes
			$stream->put($chunk->getBiomeIdArray());
			if($chunk->isLightPopulated()){
				$stream->put(pack("v*", ...$chunk->getHeightMapArray()));
			}
		}

		return $stream->getBuffer();
	}

	/**
	 * Deserializes a fast-serialized chunk
	 *
	 * @param string $data
	 *
	 * @return Chunk
	 */
	public static function deserialize(string $data) : Chunk{
		$stream = new BinaryStream($data);

		$x = $stream->getInt();
		$z = $stream->getInt();
		$flags = $stream->getByte();
		$lightPopulated = (bool) ($flags & 4);
		$terrainPopulated = (bool) ($flags & 2);
		$terrainGenerated = (bool) ($flags & 1);

		$subChunks = [];
		$biomeIds = "";
		$heightMap = [];
		if($terrainGenerated){
			$count = $stream->getByte();
			for($subCount = 0; $subCount < $count; ++$subCount){
				$y = $stream->getByte();

				/** @var PalettedBlockArray[] $layers */
				$layers = [];
				for($i = 0, $layerCount = $stream->getByte(); $i < $layerCount; ++$i){
					$bitsPerBlock = $stream->getByte();
					$words = $stream->get(PalettedBlockArray::getExpectedWordArraySize($bitsPerBlock));
					$palette = array_values(unpack("N*", $stream->get($stream->getInt())));

					$layers[] = PalettedBlockArray::fromData($bitsPerBlock, $words, $palette);
				}
				$subChunks[$y] = new SubChunk(
					BlockLegacyIds::AIR << 4, $layers, $lightPopulated ? new LightArray($stream->get(2048)) : null, $lightPopulated ? new LightArray($stream->get(2048)) : null
				);
			}

			$biomeIds = $stream->get(256);
			if($lightPopulated){
				$heightMap = array_values(unpack("v*", $stream->get(512)));
			}
		}

		$chunk = new Chunk($x, $z, $subChunks, null, null, $biomeIds, $heightMap);
		$chunk->setGenerated($terrainGenerated);
		$chunk->setPopulated($terrainPopulated);
		$chunk->setLightPopulated($lightPopulated);

		return $chunk;
	}
}
