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

namespace pocketmine\world\format\io;

use pocketmine\utils\Filesystem;
use pocketmine\utils\Utils;
use pocketmine\world\generator\GeneratorManager;
use function basename;
use function crc32;
use function file_exists;
use function floor;
use function microtime;
use function mkdir;
use function random_bytes;
use function rename;
use function round;
use function rtrim;
use const DIRECTORY_SEPARATOR;

class FormatConverter{

	/** @var WorldProvider */
	private $oldProvider;
	/** @var WritableWorldProvider|string */
	private $newProvider;

	/** @var string */
	private $backupPath;

	/** @var \Logger */
	private $logger;

	public function __construct(WorldProvider $oldProvider, string $newProvider, string $backupPath, \Logger $logger){
		$this->oldProvider = $oldProvider;
		Utils::testValidInstance($newProvider, WritableWorldProvider::class);
		$this->newProvider = $newProvider;
		$this->logger = new \PrefixedLogger($logger, "World Converter: " . $this->oldProvider->getWorldData()->getName());

		if(!file_exists($backupPath)){
			@mkdir($backupPath, 0777, true);
		}
		$nextSuffix = "";
		do{
			$this->backupPath = $backupPath . DIRECTORY_SEPARATOR . basename($this->oldProvider->getPath()) . $nextSuffix;
			$nextSuffix = "_" . crc32(random_bytes(4));
		}while(file_exists($this->backupPath));
	}

	public function getBackupPath() : string{
		return $this->backupPath;
	}

	public function execute() : WritableWorldProvider{
		$new = $this->generateNew();

		$this->populateLevelData($new->getWorldData());
		$this->convertTerrain($new);

		$path = $this->oldProvider->getPath();
		$this->oldProvider->close();
		$new->close();

		$this->logger->info("Backing up pre-conversion world to " . $this->backupPath);
		rename($path, $this->backupPath);
		rename($new->getPath(), $path);

		$this->logger->info("Conversion completed");
		/**
		 * @see WritableWorldProvider::__construct()
		 */
		return new $this->newProvider($path);
	}

	private function generateNew() : WritableWorldProvider{
		$this->logger->info("Generating new world");
		$data = $this->oldProvider->getWorldData();

		$convertedOutput = rtrim($this->oldProvider->getPath(), "/\\") . "_converted" . DIRECTORY_SEPARATOR;
		if(file_exists($convertedOutput)){
			$this->logger->info("Found previous conversion attempt, deleting...");
			Filesystem::recursiveUnlink($convertedOutput);
		}
		$this->newProvider::generate($convertedOutput, $data->getName(), $data->getSeed(), GeneratorManager::getGenerator($data->getGenerator()), $data->getGeneratorOptions());

		/**
		 * @see WritableWorldProvider::__construct()
		 */
		return new $this->newProvider($convertedOutput);
	}

	private function populateLevelData(WorldData $data) : void{
		$this->logger->info("Converting world manifest");
		$oldData = $this->oldProvider->getWorldData();
		$data->setDifficulty($oldData->getDifficulty());
		$data->setLightningLevel($oldData->getLightningLevel());
		$data->setLightningTime($oldData->getLightningTime());
		$data->setRainLevel($oldData->getRainLevel());
		$data->setRainTime($oldData->getRainTime());
		$data->setSpawn($oldData->getSpawn());
		$data->setTime($oldData->getTime());

		$data->save();
		$this->logger->info("Finished converting manifest");
		//TODO: add more properties as-needed
	}

	private function convertTerrain(WritableWorldProvider $new) : void{
		$this->logger->info("Calculating chunk count");
		$count = $this->oldProvider->calculateChunkCount();
		$this->logger->info("Discovered $count chunks");

		$counter = 0;

		$start = microtime(true);
		$thisRound = $start;
		static $reportInterval = 256;
		foreach($this->oldProvider->getAllChunks(true, $this->logger) as $chunk){
			$chunk->setDirty();
			$new->saveChunk($chunk);
			$counter++;
			if(($counter % $reportInterval) === 0){
				$time = microtime(true);
				$diff = $time - $thisRound;
				$thisRound = $time;
				$this->logger->info("Converted $counter / $count chunks (" . floor($reportInterval / $diff) . " chunks/sec)");
			}
		}
		$total = microtime(true) - $start;
		$this->logger->info("Converted $counter / $counter chunks in " . round($total, 3) . " seconds (" . floor($counter / $total) . " chunks/sec)");
	}
}
