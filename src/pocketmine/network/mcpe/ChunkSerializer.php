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

namespace pocketmine\network\mcpe;

use pocketmine\block\tile\Spawnable;
use pocketmine\network\mcpe\protocol\types\RuntimeBlockMapping;
use pocketmine\world\format\Chunk;
use pocketmine\world\format\PalettedBlockArray;
use pocketmine\world\format\SubChunk;
use function assert;
use function ceil;
use function count;
use function floor;
use function pack;
use function unpack;

final class ChunkSerializer{
	private const CURRENT_CHUNK_VERSION = 8;

	private function __construct(){
		//NOOP
	}

	/**
	 * @param Chunk $chunk
	 *
	 * @return string
	 */
	public static function serialize(Chunk $chunk) : string{
		$stream = new NetworkBinaryStream();
		$subChunkCount = $chunk->getSubChunkSendCount();
		$stream->putByte($subChunkCount);

		for($y = 0; $y < $subChunkCount; ++$y){
			$layers = $chunk->getSubChunk($y)->getBlockLayers();
			$stream->putByte(static::CURRENT_CHUNK_VERSION); //version

			$stream->putByte(count($layers));

			foreach($layers as $blocks){
				$stream->putByte(($blocks->getBitsPerBlock() << 1) | 1); //last 1-bit means "network format", but seems pointless
				$stream->put($blocks->getWordArray());
				$palette = $blocks->getPalette();
				$stream->putVarInt(count($palette)); //yes, this is intentionally zigzag
				foreach($palette as $p){
					$stream->putVarInt(RuntimeBlockMapping::toStaticRuntimeId($p >> 4, $p & 0xf));
				}
			}
		}
		$stream->put(pack("v*", ...$chunk->getHeightMapArray()));
		$stream->put($chunk->getBiomeIdArray());
		$stream->putByte(0); //border block array count
		//Border block entry format: 1 byte (4 bits X, 4 bits Z). These are however useless since they crash the regular client.

		foreach($chunk->getTiles() as $tile){
			if($tile instanceof Spawnable){
				$stream->put($tile->getSerializedSpawnCompound());
			}
		}

		return $stream->getBuffer();
	}

	public static function deserialize(int $chunkX, int $chunkZ, string $buffer) : Chunk{
		$stream = new NetworkBinaryStream($buffer);

		$subChunkCount = $stream->getByte();
		/** @var SubChunk[] $subChunks */
		$subChunks = [];

		for($y = 0; $y < $subChunkCount; ++$y){
			$version = $stream->getByte();
			assert($version === static::CURRENT_CHUNK_VERSION);
			/** @var PalettedBlockArray[] $layers */
			$layers = [];

			$layersCount = $stream->getByte();
			for($i = 0; $i < $layersCount; ++$i){
				$bitsPerBlock = ($stream->getByte() ^ 1) >> 1;
				$blocksPerWord = (int) floor(32 / $bitsPerBlock);
				$wordCount = (int) ceil(4096 / $blocksPerWord) << 2;

				$wordArray = $stream->get($wordCount);

				$palette = [];
				$paletteCount = $stream->getVarInt();

				for($i = 0; $i < $paletteCount; ++$i){
					list($id, $meta) = RuntimeBlockMapping::fromStaticRuntimeId($stream->getVarInt());
					$palette[] = $id << 4 | $meta & 0xf;
				}

				$layers[] = PalettedBlockArray::fromData($bitsPerBlock, $wordArray, $palette);
			}

			$subChunks[] = new SubChunk($layers);
		}

		$heightMap = unpack("v*", $stream->get(512));
		$biomeIds = $stream->get(256);

		$stream->getByte(); //border block array count

		//TODO: tiles

		return new Chunk($chunkX, $chunkZ, $subChunks, [], [], $biomeIds, $heightMap);
	}
}
