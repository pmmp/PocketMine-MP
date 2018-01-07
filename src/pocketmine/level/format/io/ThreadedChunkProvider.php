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
use pocketmine\Thread;
use pocketmine\utils\MainLogger;

class ThreadedChunkProvider extends Thread{
	/** @var \Threaded */
	private $loadRequestBuffer;
	/** @var \Threaded */
	private $saveRequestBuffer;
	/** @var \Threaded */
	private $loadedChunksBuffer;
	/** @var string */
	private $chunkProviderClass;
	/** @var mixed[] */
	private $chunkProviderCtorArgs;

	/**
	 * ThreadedChunkProvider constructor.
	 *
	 * @param string  $chunkProviderClass Class which implements InternalChunkProvider
	 * @param mixed[] ...$chunkProviderCtorArgs Arguments to pass to the InternalChunkProvider's constructor
	 */
	public function __construct(string $chunkProviderClass, ...$chunkProviderCtorArgs){
		$this->loadRequestBuffer = new \Threaded();
		$this->saveRequestBuffer = new \Threaded();

		$this->loadedChunksBuffer = new \Threaded();
		$this->chunkProviderClass = $chunkProviderClass;
		$this->chunkProviderCtorArgs = serialize($chunkProviderCtorArgs);

		$this->setClassLoader();
	}

	public function requestChunkLoad(int $chunkX, int $chunkZ){
		if($this->isKilled){
			throw new \RuntimeException("Tried to load a chunk after thread quit");
		}
		$this->loadRequestBuffer[] = Level::chunkHash($chunkX, $chunkZ);
		$this->notify();
	}

	public function requestChunkSave(Chunk $chunk) : void{
		if($this->isKilled){
			throw new \RuntimeException("Tried to save a chunk after thread quit");
		}

		$this->saveRequestBuffer[] = serialize($chunk->getDeinitCopy());
		$this->notify();
	}

	public function readChunkFromBuffer() : ?Chunk{
		$bytes = $this->loadedChunksBuffer->shift();
		assert(is_string($bytes));

		return unserialize($bytes);
	}

	public function hasChunksInBuffer() : bool{
		return $this->loadedChunksBuffer->count() > 0;
	}

	public function run(){
		try{
			$this->registerClassLoader();
			/** @var InternalChunkProvider $chunkProvider */
			$chunkProvider = new $this->chunkProviderClass(...unserialize($this->chunkProviderCtorArgs));

			$nextGarbageCollectionTime = time() + 300;

			while(!$this->isKilled){
				$this->processChunkLoads($chunkProvider);
				$this->processChunkSaves($chunkProvider);

				if($this->isKilled){
					break;
				}

				$this->synchronized(function() use ($nextGarbageCollectionTime){
					$this->wait(($nextGarbageCollectionTime - time() + 1) * 1000000);
				});

				if(($time = time()) >= $nextGarbageCollectionTime){
					MainLogger::getLogger()->debug("Doing garbage collection");
					$chunkProvider->doGarbageCollection();
					$nextGarbageCollectionTime = $time + 300;
				}
			}

			if(($count = $this->saveRequestBuffer->count()) > 0){
				MainLogger::getLogger()->debug("Still $count chunks left to save, saving all before stopping");
				$this->processChunkSaves($chunkProvider);
			}

			$chunkProvider->close();
		}catch(\Throwable $e){
			echo PHP_EOL . "Exception on " . $this->getThreadName() . " thread: " . $e->getMessage() . PHP_EOL . PHP_EOL;
		}
	}

	private function processChunkLoads(InternalChunkProvider $provider) : void{
		while(($request = $this->loadRequestBuffer->shift()) !== null){
			Level::getXZ($request, $chunkX, $chunkZ);

			$chunk = $provider->readChunk($chunkX, $chunkZ);
			$this->loadedChunksBuffer[] = serialize($chunk ?? new Chunk($chunkX, $chunkZ));
		}
	}

	private function processChunkSaves(InternalChunkProvider $provider) : void{
		while(($chunkBytes = $this->saveRequestBuffer->shift()) !== null){
			assert(is_string($chunkBytes));
			$chunk = unserialize($chunkBytes);
			$provider->writeChunk($chunk);
		}
	}
}
