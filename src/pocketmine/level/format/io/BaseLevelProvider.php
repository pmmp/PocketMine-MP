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
use pocketmine\level\generator\Generator;
use pocketmine\level\Level;
use pocketmine\level\LevelException;
use pocketmine\math\Vector3;
use pocketmine\nbt\BigEndianNBTStream;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;

abstract class BaseLevelProvider implements LevelProvider{
	/** @var string */
	protected $path;
	/** @var CompoundTag */
	protected $levelData;
	/** @var Chunk[] */
	protected $chunks = [];

	public function __construct(string $path){
		$this->path = $path;
		if(!file_exists($this->path)){
			mkdir($this->path, 0777, true);
		}
		$nbt = new BigEndianNBTStream();
		$nbt->readCompressed(file_get_contents($this->getPath() . "level.dat"));
		$levelData = $nbt->getData()->getCompoundTag("Data");
		if($levelData !== null){
			$this->levelData = $levelData;
		}else{
			throw new LevelException("Invalid level.dat");
		}

		if(!$this->levelData->hasTag("generatorName", StringTag::class)){
			$this->levelData->setString("generatorName", (string) Generator::getGenerator("DEFAULT"), true);
		}

		if(!$this->levelData->hasTag("generatorOptions", StringTag::class)){
			$this->levelData->setString("generatorOptions", "");
		}
	}

	public function getPath() : string{
		return $this->path;
	}

	public function getName() : string{
		return $this->levelData->getString("LevelName");
	}

	public function getTime() : int{
		return $this->levelData->getLong("Time", 0, true);
	}

	public function setTime(int $value){
		$this->levelData->setLong("Time", $value, true); //some older PM worlds had this in the wrong format
	}

	public function getSeed() : int{
		return $this->levelData->getLong("RandomSeed");
	}

	public function setSeed(int $value){
		$this->levelData->setLong("RandomSeed", $value);
	}

	public function getSpawn() : Vector3{
		return new Vector3($this->levelData->getInt("SpawnX"), $this->levelData->getInt("SpawnY"), $this->levelData->getInt("SpawnZ"));
	}

	public function setSpawn(Vector3 $pos){
		$this->levelData->setInt("SpawnX", (int) $pos->x);
		$this->levelData->setInt("SpawnY", (int) $pos->y);
		$this->levelData->setInt("SpawnZ", (int) $pos->z);
	}

	public function doGarbageCollection(){

	}

	/**
	 * @return CompoundTag
	 */
	public function getLevelData() : CompoundTag{
		return $this->levelData;
	}

	public function saveLevelData(){
		$nbt = new BigEndianNBTStream();
		$nbt->setData(new CompoundTag("", [
			$this->levelData
		]));
		$buffer = $nbt->writeCompressed();
		file_put_contents($this->getPath() . "level.dat", $buffer);
	}

	public function getChunk(int $chunkX, int $chunkZ){
		return $this->chunks[Level::chunkHash($chunkX, $chunkZ)] ?? null;
	}

	public function isChunkLoaded(int $chunkX, int $chunkZ) : bool{
		return isset($this->chunks[Level::chunkHash($chunkX, $chunkZ)]);
	}

	public function getLoadedChunks() : array{
		return $this->chunks;
	}

	public function loadChunk(int $chunkX, int $chunkZ, bool $create = false) : bool{
		$index = Level::chunkHash($chunkX, $chunkZ);
		if(isset($this->chunks[$index])){
			return true;
		}

		$chunk = $this->readChunk($chunkX, $chunkZ);
		if($chunk === null and $create){
			$chunk = new Chunk($chunkX, $chunkZ);
		}

		if($chunk !== null){
			$this->chunks[$index] = $chunk;

			return true;
		}else{
			return false;
		}
	}

	public function setChunk(int $chunkX, int $chunkZ, Chunk $chunk){
		$chunk->setX($chunkX);
		$chunk->setZ($chunkZ);

		if(isset($this->chunks[$index = Level::chunkHash($chunkX, $chunkZ)]) and $this->chunks[$index] !== $chunk){
			$this->unloadChunk($chunkX, $chunkZ, false);
		}

		$this->chunks[$index] = $chunk;
	}

	public function saveChunks(){
		foreach($this->chunks as $chunk){
			$this->saveChunk($chunk->getX(), $chunk->getZ());
		}
	}

	public function saveChunk(int $chunkX, int $chunkZ) : bool{
		if($this->isChunkLoaded($chunkX, $chunkZ)){
			$chunk = $this->getChunk($chunkX, $chunkZ);
			if(!$chunk->isGenerated()){
				throw new \InvalidStateException("Cannot save un-generated chunk");
			}
			$this->writeChunk($chunk);

			return true;
		}

		return false;
	}

	public function unloadChunk(int $chunkX, int $chunkZ, bool $safe = true) : bool{
		$chunk = $this->chunks[$index = Level::chunkHash($chunkX, $chunkZ)] ?? null;
		if($chunk instanceof Chunk and $chunk->unload($safe)){
			unset($this->chunks[$index]);
			return true;
		}

		return false;
	}

	public function unloadChunks(){
		foreach($this->chunks as $chunk){
			$this->unloadChunk($chunk->getX(), $chunk->getZ(), false);
		}
		$this->chunks = [];
	}

	abstract protected function readChunk(int $chunkX, int $chunkZ) : ?Chunk;

	abstract protected function writeChunk(Chunk $chunk) : void;
}
