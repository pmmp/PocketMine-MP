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
use pocketmine\Server;
use pocketmine\tile\Spawnable;

class ChunkRequestTask extends AsyncTask{
	/** @var string */
	protected $chunk;
	/** @var int */
	protected $chunkX;
	/** @var int */
	protected $chunkZ;
	/** @var string */
	protected $tiles;
	/** @var int */
	protected $compressionLevel;

	public function __construct(int $chunkX, int $chunkZ, Chunk $chunk, CompressBatchPromise $promise){
		$this->compressionLevel = NetworkCompression::$LEVEL;

		$this->chunk = $chunk->fastSerialize();
		$this->chunkX = $chunkX;
		$this->chunkZ = $chunkZ;

		//TODO: serialize tiles with chunks
		$tiles = "";
		foreach($chunk->getTiles() as $tile){
			if($tile instanceof Spawnable){
				$tiles .= $tile->getSerializedSpawnCompound();
			}
		}

		$this->tiles = $tiles;

		$this->storeLocal($promise);
	}

	public function onRun() : void{
		$chunk = Chunk::fastDeserialize($this->chunk);

		$pk = new FullChunkDataPacket();
		$pk->chunkX = $this->chunkX;
		$pk->chunkZ = $this->chunkZ;
		$pk->data = $chunk->networkSerialize() . $this->tiles;

		$stream = new PacketStream();
		$stream->putPacket($pk);

		$this->setResult(NetworkCompression::compress($stream->buffer, $this->compressionLevel), false);
	}

	public function onCompletion(Server $server) : void{
		/** @var CompressBatchPromise $promise */
		$promise = $this->fetchLocal();
		$promise->resolve($this->getResult());
	}
}
