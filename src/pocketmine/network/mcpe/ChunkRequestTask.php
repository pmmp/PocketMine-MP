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

use pocketmine\level\format\Chunk;
use pocketmine\network\mcpe\protocol\FullChunkDataPacket;
use pocketmine\scheduler\AsyncTask;

class ChunkRequestTask extends AsyncTask{
	/** @var string */
	protected $chunk;
	/** @var int */
	protected $chunkX;
	/** @var int */
	protected $chunkZ;

	protected $compressionLevel;

	public function __construct(int $chunkX, int $chunkZ, Chunk $chunk, CompressBatchPromise $promise){
		$this->compressionLevel = NetworkCompression::$LEVEL;

		$this->chunk = $chunk->networkSerialize();
		$this->chunkX = $chunkX;
		$this->chunkZ = $chunkZ;

		$this->storeLocal($promise);
	}

	public function onRun() : void{
		$pk = new FullChunkDataPacket();
		$pk->chunkX = $this->chunkX;
		$pk->chunkZ = $this->chunkZ;
		$pk->data = $this->chunk;

		$stream = new PacketStream();
		$stream->putPacket($pk);

		$this->setResult(NetworkCompression::compress($stream->getBuffer(), $this->compressionLevel));
	}

	public function onCompletion() : void{
		/** @var CompressBatchPromise $promise */
		$promise = $this->fetchLocal();
		$promise->resolve($this->getResult());
	}
}
