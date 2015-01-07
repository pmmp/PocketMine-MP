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

class GenerationLevelManager extends GenerationManager{

	/** @var GenerationChunkManager[] */
	protected $levels = [];

	/** @var array */
	protected $requestQueue = [];

	/** @var Server */
	protected $server;

	/** @var GenerationInstanceManager */
	protected $manager;

	protected $maxCount;

	protected $splitCount;

	protected $count;

	/**
	 * @param Server                    $server
	 * @param GenerationInstanceManager $manager
	 */
	public function __construct(Server $server, GenerationInstanceManager $manager){
		$this->server = $server;
		$this->manager = $manager;
		$this->maxCount = $this->server->getProperty("chunk-generation.per-tick", 1);

		if($this->maxCount < 1){
			$this->splitCount = $this->maxCount;
			$this->maxCount = 1;
		}else{
			$this->splitCount = 1;
		}

		$this->count = 0;
	}

	public function openLevel($levelID, $seed, $class, array $options){
		if(!isset($this->levels[$levelID])){
			$this->levels[$levelID] = new GenerationChunkManager($this, $levelID, $seed, $class, $options);
		}
	}

	public function generateChunk($levelID, $chunkX, $chunkZ){
		if(isset($this->levels[$levelID])){
			$this->levels[$levelID]->populateChunk($chunkX, $chunkZ); //Request population directly
			if(isset($this->levels[$levelID])){
				foreach($this->levels[$levelID]->getChangedChunks() as $index => $chunk){
					$this->sendChunk($levelID, $chunk);
					$this->levels[$levelID]->cleanChangedChunk($index);
				}

				$this->levels[$levelID]->doGarbageCollection();
				$this->levels[$levelID]->cleanChangedChunks();
			}
		}
	}

	public function process(){
		if(count($this->requestQueue) > 0){
			if($this->splitCount < 1){
				$this->count += $this->splitCount;
				if($this->count < 1){
					return;
				}else{
					$this->count = 0;
				}
			}

			$count = 0;
			foreach($this->requestQueue as $levelID => $chunks){
				if($count >= $this->maxCount){
					break;
				}

				if(count($chunks) === 0){
					unset($this->requestQueue[$levelID]);
				}else{
					$key = key($chunks);
					Level::getXZ($key, $chunkX, $chunkZ);
					unset($this->requestQueue[$levelID][$key]);
					$this->generateChunk($levelID, $chunkX, $chunkZ);
					++$count;
				}
			}
		}
	}

	public function shutdown(){
		foreach($this->levels as $level){
			$level->shutdown();
		}
		$this->levels = [];
	}

	public function closeLevel($levelID){
		if(isset($this->levels[$levelID])){
			$this->levels[$levelID]->shutdown();
			unset($this->levels[$levelID]);
		}
	}

	public function enqueueChunk($levelID, $chunkX, $chunkZ){
		if(!isset($this->requestQueue[$levelID])){
			$this->requestQueue[$levelID] = [];
		}
		if(!isset($this->requestQueue[$levelID][$index = Level::chunkHash($chunkX, $chunkZ)])){
			$this->requestQueue[$levelID][$index] = 1;
		}else{
			$this->requestQueue[$levelID][$index]++;
			arsort($this->requestQueue[$levelID]);
		}
	}

	/**
	 * @param $levelID
	 * @param $chunkX
	 * @param $chunkZ
	 *
	 * @return FullChunk
	 */
	public function requestChunk($levelID, $chunkX, $chunkZ){
		return $this->manager->getChunk($levelID, $chunkX, $chunkZ);
	}

	public function sendChunk($levelID, FullChunk $chunk){
		$this->manager->receiveChunk($levelID, $chunk);
	}

	/**
	 * @return \Logger
	 */
	public function getLogger(){
		return $this->server->getLogger();
	}

}
