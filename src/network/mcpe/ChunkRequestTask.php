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

use pocketmine\network\mcpe\compression\CompressBatchPromise;
use pocketmine\network\mcpe\compression\Compressor;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\LevelChunkPacket;
use pocketmine\network\mcpe\protocol\serializer\PacketBatch;
use pocketmine\network\mcpe\protocol\types\ChunkPosition;
use pocketmine\network\mcpe\protocol\types\DimensionIds;
use pocketmine\network\mcpe\serializer\ChunkSerializer;
use pocketmine\scheduler\AsyncTask;
use pocketmine\thread\NonThreadSafeValue;
use pocketmine\utils\BinaryStream;
use pocketmine\world\format\Chunk;
use pocketmine\world\format\io\FastChunkSerializer;
use function chr;

class ChunkRequestTask extends AsyncTask{
	private const TLS_KEY_PROMISE = "promise";

	protected string $chunk;
	protected int $chunkX;
	protected int $chunkZ;
	/** @phpstan-var DimensionIds::* */
	private int $dimensionId;
	/** @phpstan-var NonThreadSafeValue<Compressor> */
	protected NonThreadSafeValue $compressor;
	private string $tiles;

	/**
	 * @phpstan-param DimensionIds::* $dimensionId
	 */
	public function __construct(int $chunkX, int $chunkZ, int $dimensionId, Chunk $chunk, CompressBatchPromise $promise, Compressor $compressor){
		$this->compressor = new NonThreadSafeValue($compressor);

		$this->chunk = FastChunkSerializer::serializeTerrain($chunk);
		$this->chunkX = $chunkX;
		$this->chunkZ = $chunkZ;
		$this->dimensionId = $dimensionId;
		$this->tiles = ChunkSerializer::serializeTiles($chunk);

		$this->storeLocal(self::TLS_KEY_PROMISE, $promise);
	}

	public function onRun() : void{
		$chunk = FastChunkSerializer::deserializeTerrain($this->chunk);
		$dimensionId = $this->dimensionId;

		$subCount = ChunkSerializer::getSubChunkCount($chunk, $dimensionId);
		$converter = TypeConverter::getInstance();
		$payload = ChunkSerializer::serializeFullChunk($chunk, $dimensionId, $converter->getBlockTranslator(), $this->tiles);

		$stream = new BinaryStream();
		PacketBatch::encodePackets($stream, [LevelChunkPacket::create(new ChunkPosition($this->chunkX, $this->chunkZ), $dimensionId, $subCount, false, null, $payload)]);

		$compressor = $this->compressor->deserialize();
		$this->setResult(chr($compressor->getNetworkId()) . $compressor->compress($stream->getBuffer()));
	}

	public function onCompletion() : void{
		/** @var CompressBatchPromise $promise */
		$promise = $this->fetchLocal(self::TLS_KEY_PROMISE);
		$promise->resolve($this->getResult());
	}
}
