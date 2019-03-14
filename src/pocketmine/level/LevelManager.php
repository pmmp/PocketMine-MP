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

namespace pocketmine\level;

use pocketmine\entity\Entity;
use pocketmine\event\level\LevelInitEvent;
use pocketmine\event\level\LevelLoadEvent;
use pocketmine\event\level\LevelUnloadEvent;
use pocketmine\level\format\io\exception\UnsupportedLevelFormatException;
use pocketmine\level\format\io\FormatConverter;
use pocketmine\level\format\io\LevelProvider;
use pocketmine\level\format\io\LevelProviderManager;
use pocketmine\level\format\io\WritableLevelProvider;
use pocketmine\level\generator\Generator;
use pocketmine\level\generator\normal\Normal;
use pocketmine\Server;
use pocketmine\timings\Timings;
use pocketmine\utils\Utils;
use function array_keys;
use function array_shift;
use function asort;
use function assert;
use function count;
use function implode;
use function microtime;
use function random_int;
use function round;
use function sprintf;
use function trim;
use const INT32_MAX;
use const INT32_MIN;

class LevelManager{
	/** @var Level[] */
	private $levels = [];
	/** @var Level|null */
	private $levelDefault;

	/** @var Server */
	private $server;

	/** @var bool */
	private $autoSave = true;
	/** @var int */
	private $autoSaveTicks = 6000;

	/** @var int */
	private $autoSaveTicker = 0;

	public function __construct(Server $server){
		$this->server = $server;

		$this->autoSave = $this->server->getConfigBool("auto-save", $this->autoSave);
		$this->autoSaveTicks = (int) $this->server->getProperty("ticks-per.autosave", 6000);
	}

	/**
	 * @return Level[]
	 */
	public function getLevels() : array{
		return $this->levels;
	}

	/**
	 * @return Level|null
	 */
	public function getDefaultLevel() : ?Level{
		return $this->levelDefault;
	}

	/**
	 * Sets the default level to a different level
	 * This won't change the level-name property,
	 * it only affects the server on runtime
	 *
	 * @param Level|null $level
	 */
	public function setDefaultLevel(?Level $level) : void{
		if($level === null or ($this->isLevelLoaded($level->getFolderName()) and $level !== $this->levelDefault)){
			$this->levelDefault = $level;
		}
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function isLevelLoaded(string $name) : bool{
		return $this->getLevelByName($name) instanceof Level;
	}

	/**
	 * @param int $levelId
	 *
	 * @return Level|null
	 */
	public function getLevel(int $levelId) : ?Level{
		return $this->levels[$levelId] ?? null;
	}

	/**
	 * NOTE: This matches levels based on the FOLDER name, NOT the display name.
	 *
	 * @param string $name
	 *
	 * @return Level|null
	 */
	public function getLevelByName(string $name) : ?Level{
		foreach($this->levels as $level){
			if($level->getFolderName() === $name){
				return $level;
			}
		}

		return null;
	}

	/**
	 * @param Level $level
	 * @param bool  $forceUnload
	 *
	 * @return bool
	 *
	 * @throws \InvalidArgumentException
	 */
	public function unloadLevel(Level $level, bool $forceUnload = false) : bool{
		if($level === $this->getDefaultLevel() and !$forceUnload){
			throw new \InvalidArgumentException("The default world cannot be unloaded while running, please switch worlds.");
		}
		if($level->isDoingTick()){
			throw new \InvalidArgumentException("Cannot unload a world during world tick");
		}

		$ev = new LevelUnloadEvent($level);
		if($level === $this->levelDefault and !$forceUnload){
			$ev->setCancelled(true);
		}

		$ev->call();

		if(!$forceUnload and $ev->isCancelled()){
			return false;
		}

		$this->server->getLogger()->info($this->server->getLanguage()->translateString("pocketmine.level.unloading", [$level->getDisplayName()]));
		foreach($level->getPlayers() as $player){
			if($level === $this->levelDefault or $this->levelDefault === null){
				$player->close($player->getLeaveMessage(), "Forced default world unload");
			}elseif($this->levelDefault instanceof Level){
				$player->teleport($this->levelDefault->getSafeSpawn());
			}
		}

		if($level === $this->levelDefault){
			$this->levelDefault = null;
		}
		unset($this->levels[$level->getId()]);

		$level->close();
		return true;
	}

	/**
	 * Loads a level from the data directory
	 *
	 * @param string $name
	 * @param bool   $autoUpgrade Converts worlds to the default format if the world's format is not writable / deprecated
	 *
	 * @return bool
	 *
	 * @throws LevelException
	 */
	public function loadLevel(string $name, bool $autoUpgrade = false) : bool{
		if(trim($name) === ""){
			throw new LevelException("Invalid empty world name");
		}
		if($this->isLevelLoaded($name)){
			return true;
		}elseif(!$this->isLevelGenerated($name)){
			return false;
		}

		$path = $this->server->getDataPath() . "worlds/" . $name . "/";

		$providers = LevelProviderManager::getMatchingProviders($path);
		if(count($providers) !== 1){
			$this->server->getLogger()->error($this->server->getLanguage()->translateString("pocketmine.level.loadError", [
				$name,
				empty($providers) ?
					$this->server->getLanguage()->translateString("pocketmine.level.unknownFormat") :
					$this->server->getLanguage()->translateString("pocketmine.level.ambiguousFormat", [implode(", ", array_keys($providers))])
			]));
			return false;
		}
		$providerClass = array_shift($providers);

		/**
		 * @var LevelProvider
		 * @see LevelProvider::__construct()
		 */
		$provider = new $providerClass($path);
		if(!($provider instanceof WritableLevelProvider)){
			if(!$autoUpgrade){
				throw new LevelException("World \"$name\" is in an unsupported format and needs to be upgraded");
			}
			$this->server->getLogger()->notice("Upgrading world \"$name\" to new format. This may take a while.");

			$converter = new FormatConverter($provider, LevelProviderManager::getDefault(), $this->server->getDataPath() . "world_conversion_backups", $this->server->getLogger());
			$provider = $converter->execute();

			$this->server->getLogger()->notice("Upgraded world \"$name\" to new format successfully. Backed up pre-conversion world at " . $converter->getBackupPath());
		}

		try{
			$level = new Level($this->server, $name, $provider);
		}catch(UnsupportedLevelFormatException $e){
			$this->server->getLogger()->error($this->server->getLanguage()->translateString("pocketmine.level.loadError", [$name, $e->getMessage()]));
			return false;
		}

		$this->levels[$level->getId()] = $level;
		$level->setAutoSave($this->autoSave);

		(new LevelLoadEvent($level))->call();

		return true;
	}

	/**
	 * Generates a new level if it does not exist
	 *
	 * @param string   $name
	 * @param int|null $seed
	 * @param string   $generator Class name that extends pocketmine\level\generator\Generator
	 * @param array    $options
	 * @param bool     $backgroundGeneration
	 *
	 * @return bool
	 * @throws \InvalidArgumentException
	 */
	public function generateLevel(string $name, ?int $seed = null, string $generator = Normal::class, array $options = [], bool $backgroundGeneration = true) : bool{
		if(trim($name) === "" or $this->isLevelGenerated($name)){
			return false;
		}

		$seed = $seed ?? random_int(INT32_MIN, INT32_MAX);

		Utils::testValidInstance($generator, Generator::class);

		$providerClass = LevelProviderManager::getDefault();

		$path = $this->server->getDataPath() . "worlds/" . $name . "/";
		/** @var WritableLevelProvider $providerClass */
		$providerClass::generate($path, $name, $seed, $generator, $options);

		/** @see WritableLevelProvider::__construct() */
		$level = new Level($this->server, $name, new $providerClass($path));
		$this->levels[$level->getId()] = $level;

		$level->setAutoSave($this->autoSave);

		(new LevelInitEvent($level))->call();

		(new LevelLoadEvent($level))->call();

		if(!$backgroundGeneration){
			return true;
		}

		$this->server->getLogger()->notice($this->server->getLanguage()->translateString("pocketmine.level.backgroundGeneration", [$name]));

		$spawnLocation = $level->getSpawnLocation();
		$centerX = $spawnLocation->getFloorX() >> 4;
		$centerZ = $spawnLocation->getFloorZ() >> 4;

		$order = [];

		for($X = -3; $X <= 3; ++$X){
			for($Z = -3; $Z <= 3; ++$Z){
				$distance = $X ** 2 + $Z ** 2;
				$chunkX = $X + $centerX;
				$chunkZ = $Z + $centerZ;
				$index = Level::chunkHash($chunkX, $chunkZ);
				$order[$index] = $distance;
			}
		}

		asort($order);

		foreach($order as $index => $distance){
			Level::getXZ($index, $chunkX, $chunkZ);
			$level->populateChunk($chunkX, $chunkZ, true);
		}

		return true;
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function isLevelGenerated(string $name) : bool{
		if(trim($name) === ""){
			return false;
		}
		$path = $this->server->getDataPath() . "worlds/" . $name . "/";
		if(!($this->getLevelByName($name) instanceof Level)){
			return !empty(LevelProviderManager::getMatchingProviders($path));
		}

		return true;
	}

	/**
	 * Searches all levels for the entity with the specified ID.
	 * Useful for tracking entities across multiple worlds without needing strong references.
	 *
	 * @param int $entityId
	 *
	 * @return Entity|null
	 */
	public function findEntity(int $entityId) : ?Entity{
		foreach($this->levels as $level){
			assert(!$level->isClosed());
			if(($entity = $level->getEntity($entityId)) instanceof Entity){
				return $entity;
			}
		}

		return null;
	}


	public function tick(int $currentTick) : void{
		foreach($this->levels as $k => $level){
			if(!isset($this->levels[$k])){
				// Level unloaded during the tick of a level earlier in this loop, perhaps by plugin
				continue;
			}

			$levelTime = microtime(true);
			$level->doTick($currentTick);
			$tickMs = (microtime(true) - $levelTime) * 1000;
			$level->tickRateTime = $tickMs;
			if($tickMs >= 50){
				$this->server->getLogger()->debug(sprintf("World \"%s\" took too long to tick: %gms (%g ticks)", $level->getDisplayName(), $tickMs, round($tickMs / 50, 2)));
			}
		}

		if($this->autoSave and ++$this->autoSaveTicker >= $this->autoSaveTicks){
			$this->autoSaveTicker = 0;
			$this->server->getLogger()->debug("[Auto Save] Saving worlds...");
			$start = microtime(true);
			$this->doAutoSave();
			$time = microtime(true) - $start;
			$this->server->getLogger()->debug("[Auto Save] Save completed in " . ($time >= 1 ? round($time, 3) . "s" : round($time * 1000) . "ms"));
		}
	}


	/**
	 * @return bool
	 */
	public function getAutoSave() : bool{
		return $this->autoSave;
	}

	/**
	 * @param bool $value
	 */
	public function setAutoSave(bool $value) : void{
		$this->autoSave = $value;
		foreach($this->levels as $level){
			$level->setAutoSave($this->autoSave);
		}
	}

	private function doAutoSave() : void{
		Timings::$worldSaveTimer->startTiming();
		foreach($this->levels as $level){
			foreach($level->getPlayers() as $player){
				if($player->spawned){
					$player->save();
				}
			}
			$level->save(false);
		}
		Timings::$worldSaveTimer->stopTiming();
	}
}
