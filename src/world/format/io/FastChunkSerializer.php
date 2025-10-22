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

use pmmp\encoding\BE;
use pmmp\encoding\Byte;
use pmmp\encoding\ByteBufferReader;
use pmmp\encoding\ByteBufferWriter;
use pocketmine\block\Block;
use pocketmine\block\BlockIdentifier;
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

	private static function serializeBiomeArray(ByteBufferWriter $stream, PalettedBlockArray $array) : void{
		$wordArray = $array->getWordArray();
		$palette = $array->getPalette();

		Byte::writeUnsigned($stream, $array->getBitsPerBlock());
		$stream->writeByteArray($wordArray);
		$serialPalette = pack("L*", ...$palette);
		BE::writeUnsignedInt($stream, strlen($serialPalette));
		$stream->writeByteArray($serialPalette);
	}

	private static function serializeBlockArray(ByteBufferWriter $stream, PalettedBlockArray $array) : void{
		Byte::writeUnsigned($stream, $array->getBitsPerBlock());
		$stream->writeByteArray($array->getWordArray());

		$palette = $array->getPalette();
		BE::writeUnsignedInt($stream, count($palette));
		//TODO: this probably won't be great for performance :(
		foreach($palette as $stateId){
			$typeNumber = $stateId >> Block::INTERNAL_STATE_DATA_BITS;
			$typeId = BlockIdentifier::lookupTypeIdFromTypeNumber($typeNumber);
			$stateData = $stateId ^ BlockIdentifier::stateIdXorMask($typeNumber);

			BE::writeUnsignedInt($stream, strlen($typeId));
			$stream->writeByteArray($typeId);
			BE::writeUnsignedInt($stream, $stateData);
		}
	}

	/**
	 * Fast-serializes the chunk for passing between threads
	 * TODO: tiles and entities
	 */
	public static function serializeTerrain(Chunk $chunk) : string{
		$stream = new ByteBufferWriter();
		Byte::writeUnsigned($stream, ($chunk->isPopulated() ? self::FLAG_POPULATED : 0));

		//subchunks
		$subChunks = $chunk->getSubChunks();
		$count = count($subChunks);
		Byte::writeUnsigned($stream, $count);

		foreach($subChunks as $y => $subChunk){
			Byte::writeSigned($stream, $y);
			BE::writeUnsignedInt($stream, $subChunk->getEmptyBlockId());

			$layers = $subChunk->getBlockLayers();
			Byte::writeUnsigned($stream, count($layers));
			foreach($layers as $blocks){
				self::serializeBlockArray($stream, $blocks);
			}
			self::serializeBiomeArray($stream, $subChunk->getBiomeArray());
		}

		return $stream->getData();
	}

	private static function deserializeBiomeArray(ByteBufferReader $stream) : PalettedBlockArray{
		$bitsPerBlock = Byte::readUnsigned($stream);
		$words = $stream->readByteArray(PalettedBlockArray::getExpectedWordArraySize($bitsPerBlock));
		$paletteSize = BE::readUnsignedInt($stream);
		/** @var int[] $unpackedPalette */
		$unpackedPalette = unpack("L*", $stream->readByteArray($paletteSize)); //unpack() will never fail here
		$palette = array_values($unpackedPalette);

		return PalettedBlockArray::fromData($bitsPerBlock, $words, $palette);
	}

	private static function deserializeBlockArray(ByteBufferReader $stream) : PalettedBlockArray{
		$bitsPerBlock = Byte::readUnsigned($stream);
		$words = $stream->readByteArray(PalettedBlockArray::getExpectedWordArraySize($bitsPerBlock));

		$palette = [];
		for($i = 0, $size = BE::readUnsignedInt($stream); $i < $size; $i++){
			$typeId = $stream->readByteArray(BE::readUnsignedInt($stream));
			$typeNumber = BlockIdentifier::lookupTypeNumberFromTypeId($typeId);
			$stateData = BE::readUnsignedInt($stream);

			$stateId = $stateData ^ BlockIdentifier::stateIdXorMask($typeNumber);
			$palette[] = $stateId;
		}

		return PalettedBlockArray::fromData($bitsPerBlock, $words, $palette);
	}

	/**
	 * Deserializes a fast-serialized chunk
	 */
	public static function deserializeTerrain(string $data) : Chunk{
		$stream = new ByteBufferReader($data);

		$flags = Byte::readUnsigned($stream);
		$terrainPopulated = (bool) ($flags & self::FLAG_POPULATED);

		$subChunks = [];

		$count = Byte::readUnsigned($stream);
		for($subCount = 0; $subCount < $count; ++$subCount){
			$y = Byte::readSigned($stream);
			//TODO: why the heck are we using big-endian here?
			$airBlockId = BE::readUnsignedInt($stream);

			$layerCount = Byte::readUnsigned($stream);
			if($layerCount > 2){
				throw new \UnexpectedValueException("Expected at most 2 layers, but got $layerCount");
			}
			$layer0 = $layerCount >= 1 ? self::deserializeBlockArray($stream) : null;
			$layer1 = $layerCount === 2 ? self::deserializeBlockArray($stream) : null;

			$biomeArray = self::deserializeBiomeArray($stream);
			$subChunks[$y] = new SubChunk($airBlockId, $layer0, $layer1, $biomeArray);
		}

		return new Chunk($subChunks, $terrainPopulated);
	}
}
