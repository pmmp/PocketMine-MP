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

namespace pocketmine\level\format\anvil;

use pocketmine\level\format\generic\BaseLevelProvider;
use pocketmine\level\Level;
use pocketmine\Player;

class Anvil extends BaseLevelProvider{

	/** @var RegionLoader */
	protected $regions = [];

	/** @var Chunk[] */
	protected $chunks = [];


	public static function isValid($path){
		return file_exists(realpath($path) . "level.dat") and file_exists(realpath($path) . "region/");
	}

	public static function getRegionIndex($chunkX, $chunkZ, &$x, &$z){
		$x = $chunkX >> 5;
		$z = $chunkZ >> 5;
	}

	public function unloadChunks(){
		$this->chunks = [];
	}

	public function getLoadedChunks(){
		return $this->chunks;
	}

	public function isChunkLoaded($x, $z){
		return isset($this->chunks[Level::chunkHash($x, $z)]);
	}

	public function saveChunks(){
		foreach($this->chunks as $chunk){
			$this->saveChunk($chunk->getX(), $chunk->getZ());
		}
	}

	public function loadChunk($chunkX, $chunkZ){
		$index = Level::chunkHash($chunkX, $chunkZ);
		if(isset($this->chunks[$index])){
			return true;
		}
		$regionX = $regionZ = null;
		self::getRegionIndex($chunkX, $chunkZ, $regionX, $regionZ);
		$this->loadRegion($regionX, $regionZ);
		$chunk = $this->getRegion($regionX, $regionZ)->readChunk($chunkX - $regionX * 32, $chunkZ - $regionZ * 32, true); //generate empty chunk if not loaded
		if($chunk instanceof Chunk){
			$this->chunks[$index] = $chunk;
		}else{
			return false;
		}
	}

	public function unloadChunk($x, $z, $safe = true){
		if($safe === true and $this->isChunkLoaded($x, $z)){
			$chunk = $this->getChunk($x, $z);
			foreach($chunk->getEntities() as $entity){
				if($entity instanceof Player){
					return false;
				}
			}
		}

		unset($this->chunks[Level::chunkHash($x, $z)]);
		return true;
	}

	public function saveChunk($x, $z){
		if($this->isChunkLoaded($x, $z)){
			$this->getRegion($x >> 5, $z >> 5)->writeChunk($this->getChunk($x, $z));
			return true;
		}

		return false;
	}

	/**
	 * @param $x
	 * @param $z
	 *
	 * @return RegionLoader
	 */
	protected function getRegion($x, $z){
		$index = $x.":".$z;
		return isset($this->regions[$index]) ? $this->regions[$index] : null;
	}

	public function getChunk($chunkX, $chunkZ, $create = false){
		$index = Level::chunkHash($chunkX, $chunkZ);
		if(isset($this->chunks[$index])){
			return $this->chunks[$index];
		}elseif($create !== true){
			return null;
		}

		$this->loadChunk($chunkX, $chunkZ);
		return $this->getChunk($chunkX, $chunkZ, false);
	}

	public function isChunkGenerated($chunkX, $chunkZ){
		if(($region = $this->getRegion($chunkX >> 5, $chunkZ >> 5)) instanceof RegionLoader){
			return $region->chunkExists($chunkX - $region->getX() * 32, $chunkZ - $region->getZ() * 32);
		}
		return false;
	}

	protected function loadRegion($x, $z){
		$index = $x.":".$z;
		if(isset($this->regions[$index])){
			return true;
		}

		$this->regions[$index] = new RegionLoader($this, $x, $z);

		return true;
	}
}