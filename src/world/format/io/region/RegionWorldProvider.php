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

namespace pocketmine\world\format\io\region;

use pocketmine\utils\Utils;
use pocketmine\world\format\Chunk;
use pocketmine\world\format\io\BaseWorldProvider;
use pocketmine\world\format\io\data\JavaWorldData;
use pocketmine\world\format\io\exception\CorruptedChunkException;
use pocketmine\world\format\io\WorldData;
use pocketmine\world\generator\Generator;
use pocketmine\world\World;
use function assert;
use function file_exists;
use function is_dir;
use function is_int;
use function mkdir;
use function rename;
use function scandir;
use function strrpos;
use function substr;
use function time;
use const SCANDIR_SORT_NONE;

abstract class RegionWorldProvider extends BaseWorldProvider{

	/**
	 * Returns the file extension used for regions in this region-based format.
	 * @return string
	 */
	abstract protected static function getRegionFileExtension() : string;

	/**
	 * Returns the storage version as per Minecraft PC world formats.
	 * @return int
	 */
	abstract protected static function getPcWorldFormatVersion() : int;

	public static function isValid(string $path) : bool{
		if(file_exists($path . "/level.dat") and is_dir($path . "/region/")){
			foreach(scandir($path . "/region/", SCANDIR_SORT_NONE) as $file){
				if(substr($file, strrpos($file, ".") + 1) === static::getRegionFileExtension()){
					//we don't care if other region types exist, we only care if this format is possible
					return true;
				}
			}
		}

		return false;
	}

	public static function generate(string $path, string $name, int $seed, string $generator, array $options = []) : void{
		Utils::testValidInstance($generator, Generator::class);
		if(!file_exists($path)){
			mkdir($path, 0777, true);
		}

		if(!file_exists($path . "/region")){
			mkdir($path . "/region", 0777);
		}

		JavaWorldData::generate($path, $name, $seed, $generator, $options, static::getPcWorldFormatVersion());
	}

	/** @var RegionLoader[] */
	protected $regions = [];

	protected function loadLevelData() : WorldData{
		return new JavaWorldData($this->getPath() . "level.dat");
	}

	public function doGarbageCollection() : void{
		$limit = time() - 300;
		foreach($this->regions as $index => $region){
			if($region->lastUsed <= $limit){
				$region->close();
				unset($this->regions[$index]);
			}
		}
	}

	/**
	 * @param int $chunkX
	 * @param int $chunkZ
	 * @param int &$regionX
	 * @param int &$regionZ
	 */
	public static function getRegionIndex(int $chunkX, int $chunkZ, &$regionX, &$regionZ) : void{
		$regionX = $chunkX >> 5;
		$regionZ = $chunkZ >> 5;
	}

	/**
	 * @param int $regionX
	 * @param int $regionZ
	 *
	 * @return RegionLoader|null
	 */
	protected function getRegion(int $regionX, int $regionZ) : ?RegionLoader{
		return $this->regions[World::chunkHash($regionX, $regionZ)] ?? null;
	}

	/**
	 * Returns the path to a specific region file based on its X/Z coordinates
	 *
	 * @param int $regionX
	 * @param int $regionZ
	 *
	 * @return string
	 */
	protected function pathToRegion(int $regionX, int $regionZ) : string{
		return $this->path . "region/r.$regionX.$regionZ." . static::getRegionFileExtension();
	}

	/**
	 * @param int $regionX
	 * @param int $regionZ
	 */
	protected function loadRegion(int $regionX, int $regionZ) : void{
		if(!isset($this->regions[$index = World::chunkHash($regionX, $regionZ)])){
			$path = $this->pathToRegion($regionX, $regionZ);

			$region = new RegionLoader($path);
			try{
				$region->open();
			}catch(CorruptedRegionException $e){
				$logger = \GlobalLogger::get();
				$logger->error("Corrupted region file detected: " . $e->getMessage());

				$region->close(); //Do not write anything to the file

				$backupPath = $path . ".bak." . time();
				rename($path, $backupPath);
				$logger->error("Corrupted region file has been backed up to " . $backupPath);

				$region = new RegionLoader($path);
				$region->open(); //this will create a new empty region to replace the corrupted one
			}

			$this->regions[$index] = $region;
		}
	}

	protected function unloadRegion(int $regionX, int $regionZ) : void{
		if(isset($this->regions[$hash = World::chunkHash($regionX, $regionZ)])){
			$this->regions[$hash]->close();
			unset($this->regions[$hash]);
		}
	}

	public function close() : void{
		foreach($this->regions as $index => $region){
			$region->close();
			unset($this->regions[$index]);
		}
	}

	abstract protected function serializeChunk(Chunk $chunk) : string;

	/**
	 * @param string $data
	 *
	 * @return Chunk
	 * @throws CorruptedChunkException
	 */
	abstract protected function deserializeChunk(string $data) : Chunk;

	/**
	 * @param int $chunkX
	 * @param int $chunkZ
	 *
	 * @return Chunk|null
	 * @throws CorruptedChunkException
	 */
	protected function readChunk(int $chunkX, int $chunkZ) : ?Chunk{
		$regionX = $regionZ = null;
		self::getRegionIndex($chunkX, $chunkZ, $regionX, $regionZ);
		assert(is_int($regionX) and is_int($regionZ));

		$this->loadRegion($regionX, $regionZ);

		$chunkData = $this->getRegion($regionX, $regionZ)->readChunk($chunkX & 0x1f, $chunkZ & 0x1f);
		if($chunkData !== null){
			return $this->deserializeChunk($chunkData);
		}

		return null;
	}

	protected function writeChunk(Chunk $chunk) : void{
		$chunkX = $chunk->getX();
		$chunkZ = $chunk->getZ();

		self::getRegionIndex($chunkX, $chunkZ, $regionX, $regionZ);
		$this->loadRegion($regionX, $regionZ);

		$this->getRegion($regionX, $regionZ)->writeChunk($chunkX & 0x1f, $chunkZ & 0x1f, $this->serializeChunk($chunk));
	}

	private function createRegionIterator() : \RegexIterator{
		return new \RegexIterator(
			new \FilesystemIterator(
				$this->path . '/region/',
				\FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS
			),
			'/\/r\.(-?\d+)\.(-?\d+)\.' . static::getRegionFileExtension() . '$/',
			\RegexIterator::GET_MATCH
		);
	}

	public function getAllChunks(bool $skipCorrupted = false, ?\Logger $logger = null) : \Generator{
		$iterator = $this->createRegionIterator();

		foreach($iterator as $region){
			$regionX = ((int) $region[1]);
			$regionZ = ((int) $region[2]);
			$rX = $regionX << 5;
			$rZ = $regionZ << 5;

			for($chunkX = $rX; $chunkX < $rX + 32; ++$chunkX){
				for($chunkZ = $rZ; $chunkZ < $rZ + 32; ++$chunkZ){
					try{
						$chunk = $this->loadChunk($chunkX, $chunkZ);
						if($chunk !== null){
							yield $chunk;
						}
					}catch(CorruptedChunkException $e){
						if(!$skipCorrupted){
							throw $e;
						}
						if($logger !== null){
							$logger->error("Skipped corrupted chunk $chunkX $chunkZ (" . $e->getMessage() . ")");
						}
					}
				}
			}

			$this->unloadRegion($regionX, $regionZ);
		}
	}

	public function calculateChunkCount() : int{
		$count = 0;
		foreach($this->createRegionIterator() as $region){
			$regionX = ((int) $region[1]);
			$regionZ = ((int) $region[2]);
			$this->loadRegion($regionX, $regionZ);
			$count += $this->getRegion($regionX, $regionZ)->calculateChunkCount();
			$this->unloadRegion($regionX, $regionZ);
		}
		return $count;
	}
}
