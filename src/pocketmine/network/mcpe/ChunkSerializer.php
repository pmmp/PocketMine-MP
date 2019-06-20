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
use function count;
use function pack;

final class ChunkSerializer{

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
			$stream->putByte(8); //version

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
}
