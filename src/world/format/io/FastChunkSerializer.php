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

use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\utils\Binary;
use pocketmine\utils\BinaryStream;
use pocketmine\world\format\Chunk;
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
	private const FLAG_POPULATED = 1 << 1;

	private function __construct(){
		//NOOP
	}

	private static function serializeBiomeArray(BinaryStream $stream, PalettedBlockArray $array) : void{
		$wordArray = $array->getWordArray();
		$palette = $array->getPalette();

		$stream->putByte($array->getBitsPerBlock());
		$stream->put($wordArray);
		$serialPalette = pack("L*", ...$palette);
		$stream->putInt(strlen($serialPalette));
		$stream->put($serialPalette);
	}

	private static function serializeBlockArray(BinaryStream $stream, PalettedBlockArray $array) : void{
		$stream->putByte($array->getBitsPerBlock());
		$stream->put($array->getWordArray());

		$palette = $array->getPalette();
		$stream->putInt(count($palette));
		//TODO: this probably won't be great for performance :(
		foreach($palette as $stateId){
			$typeNumber = $stateId >> Block::INTERNAL_STATE_DATA_BITS;
			$typeId = BlockTypeIds::lookupTypeIdFromTypeNumber($typeNumber);
			$stateData = $stateId ^ BlockTypeIds::stateIdXorMask($typeNumber);

			$stream->putInt(strlen($typeId));
			$stream->put($typeId);
			$stream->putInt($stateData);
		}
	}

	/**
	 * Fast-serializes the chunk for passing between threads
	 * TODO: tiles and entities
	 */
	public static function serializeTerrain(Chunk $chunk) : string{
		$stream = new BinaryStream();
		$stream->putByte(
			($chunk->isPopulated() ? self::FLAG_POPULATED : 0)
		);

		//subchunks
		$subChunks = $chunk->getSubChunks();
		$count = count($subChunks);
		$stream->putByte($count);

		foreach($subChunks as $y => $subChunk){
			$stream->putByte($y);
			$stream->putInt($subChunk->getEmptyBlockId());
			$layers = $subChunk->getBlockLayers();
			$stream->putByte(count($layers));
			foreach($layers as $blocks){
				self::serializeBlockArray($stream, $blocks);
			}
			self::serializeBiomeArray($stream, $subChunk->getBiomeArray());
		}

		return $stream->getBuffer();
	}

	private static function deserializeBiomeArray(BinaryStream $stream) : PalettedBlockArray{
		$bitsPerBlock = $stream->getByte();
		$words = $stream->get(PalettedBlockArray::getExpectedWordArraySize($bitsPerBlock));
		/** @var int[] $unpackedPalette */
		$unpackedPalette = unpack("L*", $stream->get($stream->getInt())); //unpack() will never fail here
		$palette = array_values($unpackedPalette);

		return PalettedBlockArray::fromData($bitsPerBlock, $words, $palette);
	}

	private static function deserializeBlockArray(BinaryStream $stream) : PalettedBlockArray{
		$bitsPerBlock = $stream->getByte();
		$words = $stream->get(PalettedBlockArray::getExpectedWordArraySize($bitsPerBlock));

		$palette = [];
		for($i = 0, $size = $stream->getInt(); $i < $size; $i++){
			$typeId = $stream->get($stream->getInt());
			$typeNumber = BlockTypeIds::lookupTypeNumberFromTypeId($typeId);
			$stateData = $stream->getInt();

			$stateId = $stateData ^ BlockTypeIds::stateIdXorMask($typeNumber);
			$palette[] = $stateId;
		}

		return PalettedBlockArray::fromData($bitsPerBlock, $words, $palette);
	}

	/**
	 * Deserializes a fast-serialized chunk
	 */
	public static function deserializeTerrain(string $data) : Chunk{
		$stream = new BinaryStream($data);

		$flags = $stream->getByte();
		$terrainPopulated = (bool) ($flags & self::FLAG_POPULATED);

		$subChunks = [];

		$count = $stream->getByte();
		for($subCount = 0; $subCount < $count; ++$subCount){
			$y = Binary::signByte($stream->getByte());
			$airBlockId = $stream->getInt();

			$layers = [];
			for($i = 0, $layerCount = $stream->getByte(); $i < $layerCount; ++$i){
				$layers[] = self::deserializeBlockArray($stream);
			}
			$biomeArray = self::deserializeBiomeArray($stream);
			$subChunks[$y] = new SubChunk($airBlockId, $layers, $biomeArray);
		}

		return new Chunk($subChunks, $terrainPopulated);
	}
}
