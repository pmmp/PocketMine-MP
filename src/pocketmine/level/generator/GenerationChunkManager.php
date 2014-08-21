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

use pocketmine\level\ChunkManager;
use pocketmine\level\format\FullChunk;
use pocketmine\level\Level;
use pocketmine\utils\Random;

class GenerationChunkManager implements ChunkManager{

	protected $levelID;

	/** @var FullChunk[] */
	protected $chunks = [];

	/** @var Generator */
	protected $generator;

	/** @var GenerationManager */
	protected $manager;

	protected $seed;

	protected $changes = [];

	public function __construct(GenerationManager $manager, $levelID, $seed, $class, array $options){
		if(!is_subclass_of($class, "pocketmine\\level\\generator\\Generator")){
			throw new \Exception("Class is not a subclass of Generator");
		}

		$this->levelID = $levelID;
		$this->seed = $seed;
		$this->manager = $manager;

		$this->generator = new $class($options);
		$this->generator->init($this, new Random($seed));
	}

	/**
	 * @return int
	 */
	public function getSeed(){
		return $this->seed;
	}

	/**
	 * @return int
	 */
	public function getID(){
		return $this->levelID;
	}

	/**
	 * @param $chunkX
	 * @param $chunkZ
	 *
	 * @return FullChunk
	 */
	public function getChunk($chunkX, $chunkZ){
		$index = Level::chunkHash($chunkX, $chunkZ);
		$chunk = !isset($this->chunks[$index]) ? $this->requestChunk($chunkX, $chunkZ) : $this->chunks[$index];
		$this->changes[$index] = $chunk;

		return $chunk;
	}

	/**
	 * @return FullChunk[]
	 */
	public function getChangedChunks(){
		return $this->changes;
	}

	public function cleanChangedChunks(){
		$this->changes = [];
	}

	public function cleanChangedChunk($index){
		unset($this->changes[$index]);
	}

	public function doGarbageCollection(){
		foreach($this->chunks as $index => $chunk){
			if(!isset($this->changes[$index]) or $chunk->isPopulated()){
				unset($this->chunks[$index]);
				unset($this->changes[$index]);
			}
		}

		gc_collect_cycles();
	}

	public function generateChunk($chunkX, $chunkZ){
		$this->getChunk($chunkX, $chunkZ);
		$this->generator->generateChunk($chunkX, $chunkZ);
		$this->setChunkGenerated($chunkX, $chunkZ);
	}

	public function populateChunk($chunkX, $chunkZ){
		if(!$this->isChunkGenerated($chunkX, $chunkZ)){
			$this->generateChunk($chunkX, $chunkZ);
		}

		for($z = $chunkZ - 1; $z <= $chunkZ + 1; ++$z){
			for($x = $chunkX - 1; $x <= $chunkX + 1; ++$x){
				if(!$this->isChunkGenerated($x, $z)){
					$this->generateChunk($x, $z);
				}
			}
		}

		$this->generator->populateChunk($chunkX, $chunkZ);
		$this->setChunkPopulated($chunkX, $chunkZ);
	}

	public function isChunkGenerated($chunkX, $chunkZ){
		return $this->getChunk($chunkX, $chunkZ)->isGenerated();
	}

	public function isChunkPopulated($chunkX, $chunkZ){
		return $this->getChunk($chunkX, $chunkZ)->isPopulated();
	}

	public function setChunkGenerated($chunkX, $chunkZ){
		$chunk = $this->getChunk($chunkX, $chunkZ);
		$chunk->setGenerated(true);
	}

	public function setChunkPopulated($chunkX, $chunkZ){
		$chunk = $this->getChunk($chunkX, $chunkZ);
		$chunk->setPopulated(true);
	}

	protected function requestChunk($chunkX, $chunkZ){
		$chunk = $this->manager->requestChunk($this->levelID, $chunkX, $chunkZ);
		$this->chunks[$index = Level::chunkHash($chunkX, $chunkZ)] = $chunk;

		return $chunk;
	}

	/**
	 * @param int       $chunkX
	 * @param int       $chunkZ
	 * @param FullChunk $chunk
	 */
	public function setChunk($chunkX, $chunkZ, FullChunk $chunk){
		$this->chunks[$index = Level::chunkHash($chunkX, $chunkZ)] = $chunk;
		$this->changes[$index] = $chunk;
		if($chunk->isPopulated()){
			//TODO: Queue to be sent
		}
	}

	/**
	 * Gets the raw block id.
	 *
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 *
	 * @return int 0-255
	 */
	public function getBlockIdAt($x, $y, $z){
		return $this->getChunk($x >> 4, $z >> 4)->getBlockId($x & 0x0f, $y & 0x7f, $z & 0x0f);
	}

	/**
	 * Sets the raw block id.
	 *
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 * @param int $id 0-255
	 */
	public function setBlockIdAt($x, $y, $z, $id){
		$this->getChunk($x >> 4, $z >> 4)->setBlockId($x & 0x0f, $y & 0x7f, $z & 0x0f, $id & 0xff);
	}

	/**
	 * Gets the raw block metadata
	 *
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 *
	 * @return int 0-15
	 */
	public function getBlockDataAt($x, $y, $z){
		return $this->getChunk($x >> 4, $z >> 4)->getBlockData($x & 0x0f, $y & 0x7f, $z & 0x0f);
	}

	/**
	 * Sets the raw block metadata.
	 *
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 * @param int $data 0-15
	 */
	public function setBlockDataAt($x, $y, $z, $data){
		$this->getChunk($x >> 4, $z >> 4)->setBlockData($x & 0x0f, $y & 0x7f, $z & 0x0f, $data & 0x0f);
	}

	public function shutdown(){
		foreach($this->chunks as $chunk){
			//TODO: send generated chunks to be saved
		}
	}


}