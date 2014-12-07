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

namespace pocketmine\level\generator;

use pocketmine\level\format\FullChunk;
use pocketmine\level\Level;
use pocketmine\Server;
use pocketmine\utils\ChunkException;

class GenerationInstanceManager extends GenerationRequestManager{

	/** @var Server */
	protected $server;
	/** @var GenerationManager */
	protected $generationManager;

	/**
	 * @param Server $server
	 */
	public function __construct(Server $server){
		$this->server = $server;
		$this->generationManager = new GenerationLevelManager($this->server, $this);
	}

	public function process(){
		$this->generationManager->process();
	}

	public function shutdown(){
		$this->generationManager->shutdown();
	}

	/**
	 * @param Level  $level
	 * @param string $generator
	 * @param array  $options
	 */
	public function openLevel(Level $level, $generator, array $options = []){
		$this->generationManager->openLevel($level->getId(), $level->getSeed(), $generator, $options);
	}

	/**
	 * @param Level $level
	 */
	public function closeLevel(Level $level){
		$this->generationManager->closeLevel($level->getId());
	}

	public function addNamespace($namespace, $path){

	}

	public function requestChunk(Level $level, $chunkX, $chunkZ){
		$this->generationManager->enqueueChunk($level->getId(), $chunkX, $chunkZ);
	}

	public function getChunk($levelID, $chunkX, $chunkZ){
		if(($level = $this->server->getLevel($levelID)) instanceof Level){
			$chunk = $level->getChunk($chunkX, $chunkZ, true);
			if($chunk instanceof FullChunk){
				return $chunk;
			}else{
				throw new ChunkException("Invalid Chunk given");
			}
		}else{
			$this->generationManager->closeLevel($levelID);
			return null;
		}
	}

	public function receiveChunk($levelID, FullChunk $chunk){
		if(($level = $this->server->getLevel($levelID)) instanceof Level){
			$level->generateChunkCallback($chunk->getX(), $chunk->getZ(), $chunk);
		}else{
			$this->generationManager->closeLevel($levelID);
		}
	}


}