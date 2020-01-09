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

namespace pocketmine\level\format\io;

use pocketmine\level\format\Chunk;
use pocketmine\level\Level;
use pocketmine\network\mcpe\protocol\BatchPacket;
use pocketmine\network\mcpe\protocol\LevelChunkPacket;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use function assert;
use function strlen;

class ChunkRequestTask extends AsyncTask{

	/** @var int */
	protected $levelId;

	/** @var string */
	protected $chunk;
	/** @var int */
	protected $chunkX;
	/** @var int */
	protected $chunkZ;

	/** @var int */
	protected $compressionLevel;

	/** @var int */
	private $subChunkCount;

	public function __construct(Level $level, int $chunkX, int $chunkZ, Chunk $chunk){
		$this->levelId = $level->getId();
		$this->compressionLevel = $level->getServer()->networkCompressionLevel;

		$this->chunk = $chunk->networkSerialize();
		$this->chunkX = $chunkX;
		$this->chunkZ = $chunkZ;
		$this->subChunkCount = $chunk->getSubChunkSendCount();
	}

	public function onRun(){
		$pk = LevelChunkPacket::withoutCache($this->chunkX, $this->chunkZ, $this->subChunkCount, $this->chunk);

		$batch = new BatchPacket();
		$batch->addPacket($pk);
		$batch->setCompressionLevel($this->compressionLevel);
		$batch->encode();

		$this->setResult($batch->buffer);
	}

	public function onCompletion(Server $server){
		$level = $server->getLevel($this->levelId);
		if($level instanceof Level){
			if($this->hasResult()){
				$batch = new BatchPacket($this->getResult());
				assert(strlen($batch->buffer) > 0);
				$batch->isEncoded = true;
				$level->chunkRequestCallback($this->chunkX, $this->chunkZ, $batch);
			}else{
				$server->getLogger()->error("Chunk request for world #" . $this->levelId . ", x=" . $this->chunkX . ", z=" . $this->chunkZ . " doesn't have any result data");
			}
		}else{
			$server->getLogger()->debug("Dropped chunk task due to world not loaded");
		}
	}
}
