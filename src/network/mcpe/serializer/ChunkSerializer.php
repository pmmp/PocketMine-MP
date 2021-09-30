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
use pocketmine\nbt\TreeRoot;
use pocketmine\network\mcpe\convert\RuntimeBlockMapping;
use pocketmine\network\mcpe\protocol\serializer\NetworkNbtSerializer;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializerContext;
use pocketmine\utils\Binary;
use pocketmine\utils\BinaryStream;
use pocketmine\world\format\Chunk;
use pocketmine\world\format\SubChunk;
use function count;

final class ChunkSerializer{

	private function __construct(){
		//NOOP
	}

	/**
	 * Returns the number of subchunks that will be sent from the given chunk.
	 * Chunks are sent in a stack, so every chunk below the top non-empty one must be sent.
	 */
	public static function getSubChunkCount(Chunk $chunk) : int{
		for($count = $chunk->getSubChunks()->count(); $count > 0; --$count){
			if($chunk->getSubChunk($count - 1)->isEmptyFast()){
				continue;
			}
			return $count;
		}

		return 0;
	}

	public static function serializeFullChunk(Chunk $chunk, RuntimeBlockMapping $blockMapper, PacketSerializerContext $encoderContext, ?string $tiles = null) : string{
		$stream = PacketSerializer::encoder($encoderContext);
		$subChunkCount = self::getSubChunkCount($chunk);
		for($y = 0; $y < $subChunkCount; ++$y){
			self::serializeSubChunk($chunk->getSubChunk($y), $blockMapper, $stream, false);
		}
		$stream->put($chunk->getBiomeIdArray());
		$stream->putByte(0); //border block array count
		//Border block entry format: 1 byte (4 bits X, 4 bits Z). These are however useless since they crash the regular client.

		if($tiles !== null){
			$stream->put($tiles);
		}else{
			$stream->put(self::serializeTiles($chunk));
		}
		return $stream->getBuffer();
	}

	public static function serializeSubChunk(SubChunk $subChunk, RuntimeBlockMapping $blockMapper, PacketSerializer $stream, bool $persistentBlockStates) : void{
		$layers = $subChunk->getBlockLayers();
		$stream->putByte(8); //version

		$stream->putByte(count($layers));

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
					$stream->put($nbtSerializer->write(new TreeRoot($blockMapper->getBedrockKnownStates()[$blockMapper->toRuntimeId($p)])));
				}
			}else{
				foreach($palette as $p){
					$stream->put(Binary::writeUnsignedVarInt($blockMapper->toRuntimeId($p) << 1));
				}
			}
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
