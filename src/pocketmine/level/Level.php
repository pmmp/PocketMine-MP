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

/**
 * All Level related classes are here, like Generators, Populators, Noise, ...
 */
namespace pocketmine\level;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\entity\Entity;
use pocketmine\entity\object\ExperienceOrb;
use pocketmine\entity\object\ItemEntity;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockUpdateEvent;
use pocketmine\event\level\ChunkLoadEvent;
use pocketmine\event\level\ChunkPopulateEvent;
use pocketmine\event\level\ChunkUnloadEvent;
use pocketmine\event\level\LevelSaveEvent;
use pocketmine\event\level\LevelUnloadEvent;
use pocketmine\event\level\SpawnChangeEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\level\biome\Biome;
use pocketmine\level\format\Chunk;
use pocketmine\level\format\ChunkException;
use pocketmine\level\format\EmptySubChunk;
use pocketmine\level\format\io\BaseLevelProvider;
use pocketmine\level\format\io\ChunkRequestTask;
use pocketmine\level\format\io\LevelProvider;
use pocketmine\level\generator\Generator;
use pocketmine\level\generator\GeneratorRegisterTask;
use pocketmine\level\generator\GeneratorUnregisterTask;
use pocketmine\level\generator\PopulationTask;
use pocketmine\level\light\BlockLightUpdate;
use pocketmine\level\light\LightPopulationTask;
use pocketmine\level\light\SkyLightUpdate;
use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\level\particle\Particle;
use pocketmine\level\sound\Sound;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3;
use pocketmine\metadata\BlockMetadataStore;
use pocketmine\metadata\Metadatable;
use pocketmine\metadata\MetadataValue;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\BatchPacket;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\SetDifficultyPacket;
use pocketmine\network\mcpe\protocol\SetTimePacket;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use pocketmine\tile\Chest;
use pocketmine\tile\Container;
use pocketmine\tile\Tile;
use pocketmine\timings\Timings;
use pocketmine\utils\Random;
use pocketmine\utils\ReversePriorityQueue;

#include <rules/Level.h>

class Level implements ChunkManager, Metadatable{

	private static $levelIdCounter = 1;
	private static $chunkLoaderCounter = 1;

	public const Y_MASK = 0xFF;
	public const Y_MAX = 0x100; //256

	public const TIME_DAY = 0;
	public const TIME_SUNSET = 12000;
	public const TIME_NIGHT = 14000;
	public const TIME_SUNRISE = 23000;

	public const TIME_FULL = 24000;

	public const DIFFICULTY_PEACEFUL = 0;
	public const DIFFICULTY_EASY = 1;
	public const DIFFICULTY_NORMAL = 2;
	public const DIFFICULTY_HARD = 3;

	/** @var Tile[] */
	private $tiles = [];

	/** @var Player[] */
	private $players = [];

	/** @var Entity[] */
	private $entities = [];

	/** @var Entity[] */
	public $updateEntities = [];
	/** @var Tile[] */
	public $updateTiles = [];
	/** @var Block[][] */
	private $blockCache = [];

	/** @var BatchPacket[] */
	private $chunkCache = [];

	/** @var int */
	private $sendTimeTicker = 0;

	/** @var Server */
	private $server;

	/** @var int */
	private $levelId;

	/** @var LevelProvider */
	private $provider;
	/** @var int */
	private $providerGarbageCollectionTicker = 0;

	/** @var int */
	private $worldHeight;

	/** @var ChunkLoader[] */
	private $loaders = [];
	/** @var int[] */
	private $loaderCounter = [];
	/** @var ChunkLoader[][] */
	private $chunkLoaders = [];
	/** @var Player[][] */
	private $playerLoaders = [];

	/** @var DataPacket[][] */
	private $chunkPackets = [];
	/** @var DataPacket[] */
	private $globalPackets = [];

	/** @var float[] */
	private $unloadQueue = [];

	/** @var int */
	private $time;
	/** @var bool */
	public $stopTime = false;

	/** @var float */
	private $sunAnglePercentage = 0.0;
	/** @var int */
	private $skyLightReduction = 0;

	/** @var string */
	private $folderName;
	/** @var string */
	private $displayName;

	/** @var Chunk[] */
	private $chunks = [];

	/** @var Vector3[][] */
	private $changedBlocks = [];

	/** @var ReversePriorityQueue */
	private $scheduledBlockUpdateQueue;
	/** @var int[] */
	private $scheduledBlockUpdateQueueIndex = [];

	/** @var \SplQueue */
	private $neighbourBlockUpdateQueue;

	/** @var Player[][] */
	private $chunkSendQueue = [];
	/** @var bool[] */
	private $chunkSendTasks = [];

	/** @var bool[] */
	private $chunkPopulationQueue = [];
	/** @var bool[] */
	private $chunkPopulationLock = [];
	/** @var int */
	private $chunkPopulationQueueSize = 2;

	/** @var bool */
	private $autoSave = true;

	/** @var BlockMetadataStore */
	private $blockMetadata;

	/** @var Position */
	private $temporalPosition;
	/** @var Vector3 */
	private $temporalVector;

	/** @var \SplFixedArray */
	private $blockStates;

	/** @var int */
	private $sleepTicks = 0;

	/** @var int */
	private $chunkTickRadius;
	/** @var int[] */
	private $chunkTickList = [];
	/** @var int */
	private $chunksPerTick;
	/** @var bool */
	private $clearChunksOnTick;
	/** @var \SplFixedArray<Block> */
	private $randomTickBlocks = null;

	/** @var LevelTimings */
	public $timings;

	/** @var int */
	private $tickRate;
	/** @var int */
	public $tickRateTime = 0;
	/** @var int */
	public $tickRateCounter = 0;

	/** @var string|Generator */
	private $generator;

	/** @var bool */
	private $closed = false;



	public static function chunkHash(int $x, int $z) : int{
		return (($x & 0xFFFFFFFF) << 32) | ($z & 0xFFFFFFFF);
	}

	public static function blockHash(int $x, int $y, int $z) : int{
		if($y < 0 or $y >= Level::Y_MAX){
			throw new \InvalidArgumentException("Y coordinate $y is out of range!");
		}
		return (($x & 0xFFFFFFF) << 36) | (($y & Level::Y_MASK) << 28) | ($z & 0xFFFFFFF);
	}

	public static function getBlockXYZ(int $hash, ?int &$x, ?int &$y, ?int &$z) : void{
		$x = $hash >> 36;
		$y = ($hash >> 28) & Level::Y_MASK; //it's always positive
		$z = ($hash & 0xFFFFFFF) << 36 >> 36;
	}

	/**
	 * @param int      $hash
	 * @param int|null $x
	 * @param int|null $z
	 */
	public static function getXZ(int $hash, ?int &$x, ?int &$z) : void{
		$x = $hash >> 32;
		$z = ($hash & 0xFFFFFFFF) << 32 >> 32;
	}

	public static function generateChunkLoaderId(ChunkLoader $loader) : int{
		if($loader->getLoaderId() === 0){
			return self::$chunkLoaderCounter++;
		}else{
			throw new \InvalidStateException("ChunkLoader has a loader id already assigned: " . $loader->getLoaderId());
		}
	}

	/**
	 * @param string $str
	 * @return int
	 */
	public static function getDifficultyFromString(string $str) : int{
		switch(strtolower(trim($str))){
			case "0":
			case "peaceful":
			case "p":
				return Level::DIFFICULTY_PEACEFUL;

			case "1":
			case "easy":
			case "e":
				return Level::DIFFICULTY_EASY;

			case "2":
			case "normal":
			case "n":
				return Level::DIFFICULTY_NORMAL;

			case "3":
			case "hard":
			case "h":
				return Level::DIFFICULTY_HARD;
		}

		return -1;
	}

	/**
	 * Init the default level data
	 *
	 * @param Server        $server
	 * @param string        $name
	 * @param LevelProvider $provider
	 */
	public function __construct(Server $server, string $name, LevelProvider $provider){
		$this->blockStates = BlockFactory::getBlockStatesArray();
		$this->levelId = static::$levelIdCounter++;
		$this->blockMetadata = new BlockMetadataStore($this);
		$this->server = $server;
		$this->autoSave = $server->getAutoSave();

		$this->provider = $provider;

		$this->displayName = $this->provider->getName();
		$this->worldHeight = $this->provider->getWorldHeight();

		$this->server->getLogger()->info($this->server->getLanguage()->translateString("pocketmine.level.preparing", [$this->displayName]));
		$this->generator = Generator::getGenerator($this->provider->getGenerator());

		$this->folderName = $name;

		$this->scheduledBlockUpdateQueue = new ReversePriorityQueue();
		$this->scheduledBlockUpdateQueue->setExtractFlags(\SplPriorityQueue::EXTR_BOTH);

		$this->neighbourBlockUpdateQueue = new \SplQueue();

		$this->time = $this->provider->getTime();

		$this->chunkTickRadius = min($this->server->getViewDistance(), max(1, (int) $this->server->getProperty("chunk-ticking.tick-radius", 4)));
		$this->chunksPerTick = (int) $this->server->getProperty("chunk-ticking.per-tick", 40);
		$this->chunkPopulationQueueSize = (int) $this->server->getProperty("chunk-generation.population-queue-size", 2);
		$this->clearChunksOnTick = (bool) $this->server->getProperty("chunk-ticking.clear-tick-list", true);

		$dontTickBlocks = array_fill_keys($this->server->getProperty("chunk-ticking.disable-block-ticking", []), true);

		$this->randomTickBlocks = new \SplFixedArray(256);
		foreach($this->randomTickBlocks as $id => $null){
			$block = BlockFactory::get($id); //Make sure it's a copy
			if(!isset($dontTickBlocks[$id]) and $block->ticksRandomly()){
				$this->randomTickBlocks[$id] = $block;
			}
		}

		$this->timings = new LevelTimings($this);
		$this->temporalPosition = new Position(0, 0, 0, $this);
		$this->temporalVector = new Vector3(0, 0, 0);
		$this->tickRate = 1;
	}

	public function getTickRate() : int{
		return $this->tickRate;
	}

	public function getTickRateTime() : float{
		return $this->tickRateTime;
	}

	public function setTickRate(int $tickRate){
		$this->tickRate = $tickRate;
	}

	public function initLevel(){
		$this->registerGenerator();
	}

	public function registerGenerator(){
		$pool = $this->server->getAsyncPool();
		for($i = 0, $size = $pool->getSize(); $i < $size; ++$i){
			$pool->submitTaskToWorker(new GeneratorRegisterTask($this, $this->generator, $this->provider->getGeneratorOptions()), $i);

		}
	}

	public function unregisterGenerator(){
		$pool = $this->server->getAsyncPool();
		for($i = 0, $size = $pool->getSize(); $i < $size; ++$i){
			$pool->submitTaskToWorker(new GeneratorUnregisterTask($this), $i);
		}
	}

	public function getBlockMetadata() : BlockMetadataStore{
		return $this->blockMetadata;
	}

	public function getServer() : Server{
		return $this->server;
	}

	final public function getProvider() : LevelProvider{
		return $this->provider;
	}

	/**
	 * Returns the unique level identifier
	 */
	final public function getId() : int{
		return $this->levelId;
	}

	public function isClosed() : bool{
		return $this->closed;
	}

	public function close(){
		if($this->closed){
			throw new \InvalidStateException("Tried to close a level which is already closed");
		}

		foreach($this->chunks as $chunk){
			$this->unloadChunk($chunk->getX(), $chunk->getZ(), false);
		}

		$this->save();

		$this->unregisterGenerator();

		$this->provider->close();
		$this->provider = null;
		$this->blockMetadata = null;
		$this->blockCache = [];
		$this->temporalPosition = null;

		$this->closed = true;
	}

	public function addSound(Sound $sound, array $players = null){
		$pk = $sound->encode();
		if(!is_array($pk)){
			$pk = [$pk];
		}

		if($players === null){
			foreach($pk as $e){
				$this->addChunkPacket($sound->getFloorX() >> 4, $sound->getFloorZ() >> 4, $e);
			}
		}else{
			$this->server->batchPackets($players, $pk, false);
		}
	}

	public function addParticle(Particle $particle, array $players = null){
		$pk = $particle->encode();
		if(!is_array($pk)){
			$pk = [$pk];
		}

		if($players === null){
			foreach($pk as $e){
				$this->addChunkPacket($particle->getFloorX() >> 4, $particle->getFloorZ() >> 4, $e);
			}
		}else{
			$this->server->batchPackets($players, $pk, false);
		}
	}

	/**
	 * Broadcasts a LevelEvent to players in the area. This could be sound, particles, weather changes, etc.
	 *
	 * @param Vector3|null $pos If null, broadcasts to every player in the Level
	 * @param int          $evid
	 * @param int          $data
	 */
	public function broadcastLevelEvent(?Vector3 $pos, int $evid, int $data = 0){
		$pk = new LevelEventPacket();
		$pk->evid = $evid;
		$pk->data = $data;
		if($pos !== null){
			$pk->position = $pos->asVector3();
			$this->addChunkPacket($pos->getFloorX() >> 4, $pos->getFloorZ() >> 4, $pk);
		}else{
			$pk->position = null;
			$this->addGlobalPacket($pk);
		}
	}

	/**
	 * Broadcasts a LevelSoundEvent to players in the area.
	 *
	 * @param Vector3 $pos
	 * @param int     $soundId
	 * @param int     $pitch
	 * @param int     $extraData
	 * @param bool    $isBabyMob
	 * @param bool    $disableRelativeVolume If true, all players receiving this sound-event will hear the sound at full volume regardless of distance
	 */
	public function broadcastLevelSoundEvent(Vector3 $pos, int $soundId, int $pitch = 1, int $extraData = -1, bool $isBabyMob = false, bool $disableRelativeVolume = false){
		$pk = new LevelSoundEventPacket();
		$pk->sound = $soundId;
		$pk->pitch = $pitch;
		$pk->extraData = $extraData;
		$pk->isBabyMob = $isBabyMob;
		$pk->disableRelativeVolume = $disableRelativeVolume;
		$pk->position = $pos->asVector3();
		$this->addChunkPacket($pos->getFloorX() >> 4, $pos->getFloorZ() >> 4, $pk);
	}

	public function getAutoSave() : bool{
		return $this->autoSave;
	}

	public function setAutoSave(bool $value){
		$this->autoSave = $value;
	}

	/**
	 * @internal DO NOT use this from plugins, it's for internal use only. Use Server->unloadLevel() instead.
	 *
	 * Unloads the current level from memory safely
	 *
	 * @param bool $force default false, force unload of default level
	 *
	 * @return bool
	 */
	public function unload(bool $force = false) : bool{

		$ev = new LevelUnloadEvent($this);

		if($this === $this->server->getDefaultLevel() and !$force){
			$ev->setCancelled(true);
		}

		$this->server->getPluginManager()->callEvent($ev);

		if(!$force and $ev->isCancelled()){
			return false;
		}

		$this->server->getLogger()->info($this->server->getLanguage()->translateString("pocketmine.level.unloading", [$this->getName()]));
		$defaultLevel = $this->server->getDefaultLevel();
		foreach($this->getPlayers() as $player){
			if($this === $defaultLevel or $defaultLevel === null){
				$player->close($player->getLeaveMessage(), "Forced default level unload");
			}elseif($defaultLevel instanceof Level){
				$player->teleport($this->server->getDefaultLevel()->getSafeSpawn());
			}
		}

		if($this === $defaultLevel){
			$this->server->setDefaultLevel(null);
		}

		$this->server->removeLevel($this);

		$this->close();

		return true;
	}

	/**
	 * Gets the players being used in a specific chunk
	 *
	 * @param int $chunkX
	 * @param int $chunkZ
	 *
	 * @return Player[]
	 */
	public function getChunkPlayers(int $chunkX, int $chunkZ) : array{
		return $this->playerLoaders[Level::chunkHash($chunkX, $chunkZ)] ?? [];
	}

	/**
	 * Gets the chunk loaders being used in a specific chunk
	 *
	 * @param int $chunkX
	 * @param int $chunkZ
	 *
	 * @return ChunkLoader[]
	 */
	public function getChunkLoaders(int $chunkX, int $chunkZ) : array{
		return $this->chunkLoaders[Level::chunkHash($chunkX, $chunkZ)] ?? [];
	}

	/**
	 * Queues a DataPacket to be sent to all players using the chunk at the specified X/Z coordinates at the end of the
	 * current tick.
	 *
	 * @param int        $chunkX
	 * @param int        $chunkZ
	 * @param DataPacket $packet
	 */
	public function addChunkPacket(int $chunkX, int $chunkZ, DataPacket $packet){
		if(!isset($this->chunkPackets[$index = Level::chunkHash($chunkX, $chunkZ)])){
			$this->chunkPackets[$index] = [$packet];
		}else{
			$this->chunkPackets[$index][] = $packet;
		}
	}

	/**
	 * Queues a DataPacket to be sent to everyone in the Level at the end of the current tick.
	 *
	 * @param DataPacket $packet
	 */
	public function addGlobalPacket(DataPacket $packet) : void{
		$this->globalPackets[] = $packet;
	}

	public function registerChunkLoader(ChunkLoader $loader, int $chunkX, int $chunkZ, bool $autoLoad = true){
		$hash = $loader->getLoaderId();

		if(!isset($this->chunkLoaders[$index = Level::chunkHash($chunkX, $chunkZ)])){
			$this->chunkLoaders[$index] = [];
			$this->playerLoaders[$index] = [];
		}elseif(isset($this->chunkLoaders[$index][$hash])){
			return;
		}

		$this->chunkLoaders[$index][$hash] = $loader;
		if($loader instanceof Player){
			$this->playerLoaders[$index][$hash] = $loader;
		}

		if(!isset($this->loaders[$hash])){
			$this->loaderCounter[$hash] = 1;
			$this->loaders[$hash] = $loader;
		}else{
			++$this->loaderCounter[$hash];
		}

		$this->cancelUnloadChunkRequest($chunkX, $chunkZ);

		if($autoLoad){
			$this->loadChunk($chunkX, $chunkZ);
		}
	}

	public function unregisterChunkLoader(ChunkLoader $loader, int $chunkX, int $chunkZ){
		if(isset($this->chunkLoaders[$index = Level::chunkHash($chunkX, $chunkZ)][$hash = $loader->getLoaderId()])){
			unset($this->chunkLoaders[$index][$hash]);
			unset($this->playerLoaders[$index][$hash]);
			if(count($this->chunkLoaders[$index]) === 0){
				unset($this->chunkLoaders[$index]);
				unset($this->playerLoaders[$index]);
				$this->unloadChunkRequest($chunkX, $chunkZ, true);
			}

			if(--$this->loaderCounter[$hash] === 0){
				unset($this->loaderCounter[$hash]);
				unset($this->loaders[$hash]);
			}
		}
	}

	/**
	 * WARNING: Do not use this, it's only for internal use.
	 * Changes to this function won't be recorded on the version.
	 */
	public function checkTime(){
		if($this->stopTime){
			return;
		}else{
			++$this->time;
		}
	}

	/**
	 * WARNING: Do not use this, it's only for internal use.
	 * Changes to this function won't be recorded on the version.
	 *
	 * @param Player ...$targets If empty, will send to all players in the level.
	 */
	public function sendTime(Player ...$targets){
		$pk = new SetTimePacket();
		$pk->time = $this->time;

		$this->server->broadcastPacket(count($targets) > 0 ? $targets : $this->players, $pk);
	}

	/**
	 * WARNING: Do not use this, it's only for internal use.
	 * Changes to this function won't be recorded on the version.
	 *
	 * @param int $currentTick
	 *
	 */
	public function doTick(int $currentTick){
		if($this->closed){
			throw new \InvalidStateException("Attempted to tick a Level which has been closed");
		}

		$this->timings->doTick->startTiming();

		$this->checkTime();

		$this->sunAnglePercentage = $this->computeSunAnglePercentage(); //Sun angle depends on the current time
		$this->skyLightReduction = $this->computeSkyLightReduction(); //Sky light reduction depends on the sun angle

		if(++$this->sendTimeTicker === 200){
			$this->sendTime();
			$this->sendTimeTicker = 0;
		}

		$this->unloadChunks();
		if(++$this->providerGarbageCollectionTicker >= 6000){
			$this->provider->doGarbageCollection();
			$this->providerGarbageCollectionTicker = 0;
		}

		//Do block updates
		$this->timings->doTickPending->startTiming();

		//Delayed updates
		while($this->scheduledBlockUpdateQueue->count() > 0 and $this->scheduledBlockUpdateQueue->current()["priority"] <= $currentTick){
			$block = $this->getBlock($this->scheduledBlockUpdateQueue->extract()["data"]);
			unset($this->scheduledBlockUpdateQueueIndex[Level::blockHash($block->x, $block->y, $block->z)]);
			$block->onScheduledUpdate();
		}

		//Normal updates
		while($this->neighbourBlockUpdateQueue->count() > 0){
			$index = $this->neighbourBlockUpdateQueue->dequeue();
			Level::getBlockXYZ($index, $x, $y, $z);

			$block = $this->getBlockAt($x, $y, $z);
			$block->clearCaches(); //for blocks like fences, force recalculation of connected AABBs

			$this->server->getPluginManager()->callEvent($ev = new BlockUpdateEvent($block));
			if(!$ev->isCancelled()){
				$block->onNearbyBlockChange();
			}
		}

		$this->timings->doTickPending->stopTiming();

		$this->timings->entityTick->startTiming();
		//Update entities that need update
		Timings::$tickEntityTimer->startTiming();
		foreach($this->updateEntities as $id => $entity){
			if($entity->isClosed() or !$entity->onUpdate($currentTick)){
				unset($this->updateEntities[$id]);
			}
		}
		Timings::$tickEntityTimer->stopTiming();
		$this->timings->entityTick->stopTiming();

		$this->timings->tileEntityTick->startTiming();
		Timings::$tickTileEntityTimer->startTiming();
		//Update tiles that need update
		foreach($this->updateTiles as $id => $tile){
			if(!$tile->onUpdate()){
				unset($this->updateTiles[$id]);
			}
		}
		Timings::$tickTileEntityTimer->stopTiming();
		$this->timings->tileEntityTick->stopTiming();

		$this->timings->doTickTiles->startTiming();
		$this->tickChunks();
		$this->timings->doTickTiles->stopTiming();

		if(count($this->changedBlocks) > 0){
			if(count($this->players) > 0){
				foreach($this->changedBlocks as $index => $blocks){
					unset($this->chunkCache[$index]);
					Level::getXZ($index, $chunkX, $chunkZ);
					if(count($blocks) > 512){
						$chunk = $this->getChunk($chunkX, $chunkZ);
						foreach($this->getChunkPlayers($chunkX, $chunkZ) as $p){
							$p->onChunkChanged($chunk);
						}
					}elseif(!empty($blocks)){
						$this->sendBlocks($this->getChunkPlayers($chunkX, $chunkZ), $blocks, UpdateBlockPacket::FLAG_ALL);
					}
				}
			}else{
				$this->chunkCache = [];
			}

			$this->changedBlocks = [];

		}

		$this->processChunkRequest();

		if($this->sleepTicks > 0 and --$this->sleepTicks <= 0){
			$this->checkSleep();
		}

		if(!empty($this->players) and !empty($this->globalPackets)){
			$this->server->batchPackets($this->players, $this->globalPackets);
			$this->globalPackets = [];
		}

		foreach($this->chunkPackets as $index => $entries){
			Level::getXZ($index, $chunkX, $chunkZ);
			$chunkPlayers = $this->getChunkPlayers($chunkX, $chunkZ);
			if(count($chunkPlayers) > 0){
				$this->server->batchPackets($chunkPlayers, $entries, false, false);
			}
		}

		$this->chunkPackets = [];

		$this->timings->doTick->stopTiming();
	}

	public function checkSleep(){
		if(count($this->players) === 0){
			return;
		}

		$resetTime = true;
		foreach($this->getPlayers() as $p){
			if(!$p->isSleeping()){
				$resetTime = false;
				break;
			}
		}

		if($resetTime){
			$time = $this->getTime() % Level::TIME_FULL;

			if($time >= Level::TIME_NIGHT and $time < Level::TIME_SUNRISE){
				$this->setTime($this->getTime() + Level::TIME_FULL - $time);

				foreach($this->getPlayers() as $p){
					$p->stopSleep();
				}
			}
		}
	}

	public function setSleepTicks(int $ticks) : void{
		$this->sleepTicks = $ticks;
	}

	/**
	 * @param Player[] $target
	 * @param Block[]  $blocks
	 * @param int      $flags
	 * @param bool     $optimizeRebuilds
	 */
	public function sendBlocks(array $target, array $blocks, $flags = UpdateBlockPacket::FLAG_NONE, bool $optimizeRebuilds = false){
		$packets = [];
		if($optimizeRebuilds){
			$chunks = [];
			foreach($blocks as $b){
				if($b === null){
					continue;
				}
				$pk = new UpdateBlockPacket();

				$first = false;
				if(!isset($chunks[$index = Level::chunkHash($b->x >> 4, $b->z >> 4)])){
					$chunks[$index] = true;
					$first = true;
				}

				$pk->x = $b->x;
				$pk->y = $b->y;
				$pk->z = $b->z;

				if($b instanceof Block){
					$blockId = $b->getId();
					$blockData = $b->getDamage();
				}else{
					$fullBlock = $this->getFullBlock($b->x, $b->y, $b->z);
					$blockId = $fullBlock >> 4;
					$blockData = $fullBlock & 0xf;
				}

				$pk->blockRuntimeId = BlockFactory::toStaticRuntimeId($blockId, $blockData);

				$pk->flags = $first ? $flags : UpdateBlockPacket::FLAG_NONE;

				$packets[] = $pk;
			}
		}else{
			foreach($blocks as $b){
				if($b === null){
					continue;
				}
				$pk = new UpdateBlockPacket();

				$pk->x = $b->x;
				$pk->y = $b->y;
				$pk->z = $b->z;

				if($b instanceof Block){
					$blockId = $b->getId();
					$blockData = $b->getDamage();
				}else{
					$fullBlock = $this->getFullBlock($b->x, $b->y, $b->z);
					$blockId = $fullBlock >> 4;
					$blockData = $fullBlock & 0xf;
				}

				$pk->blockRuntimeId = BlockFactory::toStaticRuntimeId($blockId, $blockData);

				$pk->flags = $flags;

				$packets[] = $pk;
			}
		}

		$this->server->batchPackets($target, $packets, false, false);
	}

	public function clearCache(bool $force = false){
		if($force){
			$this->chunkCache = [];
			$this->blockCache = [];
		}else{
			$count = 0;
			foreach($this->blockCache as $list){
				$count += count($list);
				if($count > 2048){
					$this->blockCache = [];
					break;
				}
			}
		}
	}

	public function clearChunkCache(int $chunkX, int $chunkZ){
		unset($this->chunkCache[Level::chunkHash($chunkX, $chunkZ)]);
	}

	public function getRandomTickedBlocks() : \SplFixedArray{
		return $this->randomTickBlocks;
	}

	public function addRandomTickedBlock(int $id){
		$this->randomTickBlocks[$id] = BlockFactory::get($id);
	}

	public function removeRandomTickedBlock(int $id){
		$this->randomTickBlocks[$id] = null;
	}

	private function tickChunks(){
		if($this->chunksPerTick <= 0 or count($this->loaders) === 0){
			$this->chunkTickList = [];
			return;
		}

		$chunksPerLoader = min(200, max(1, (int) ((($this->chunksPerTick - count($this->loaders)) / count($this->loaders)) + 0.5)));
		$randRange = 3 + $chunksPerLoader / 30;
		$randRange = (int) ($randRange > $this->chunkTickRadius ? $this->chunkTickRadius : $randRange);

		foreach($this->loaders as $loader){
			$chunkX = (int) floor($loader->getX()) >> 4;
			$chunkZ = (int) floor($loader->getZ()) >> 4;

			$index = Level::chunkHash($chunkX, $chunkZ);
			$existingLoaders = max(0, $this->chunkTickList[$index] ?? 0);
			$this->chunkTickList[$index] = $existingLoaders + 1;
			for($chunk = 0; $chunk < $chunksPerLoader; ++$chunk){
				$dx = mt_rand(-$randRange, $randRange);
				$dz = mt_rand(-$randRange, $randRange);
				$hash = Level::chunkHash($dx + $chunkX, $dz + $chunkZ);
				if(!isset($this->chunkTickList[$hash]) and isset($this->chunks[$hash])){
					$this->chunkTickList[$hash] = -1;
				}
			}
		}

		foreach($this->chunkTickList as $index => $loaders){
			Level::getXZ($index, $chunkX, $chunkZ);

			if(($chunk = $this->chunks[$index] ?? null) === null){
				unset($this->chunkTickList[$index]);
				continue;
			}elseif($loaders <= 0){
				unset($this->chunkTickList[$index]);
			}

			foreach($chunk->getEntities() as $entity){
				$entity->scheduleUpdate();
			}


			foreach($chunk->getSubChunks() as $Y => $subChunk){
				if(!($subChunk instanceof EmptySubChunk)){
					for($i = 0; $i < 3; ++$i){
						$k = mt_rand(0, 0xfff);
						$x = $k & 0x0f;
						$y = ($k >> 4) & 0x0f;
						$z = ($k >> 8) & 0x0f;

						$blockId = $subChunk->getBlockId($x, $y, $z);
						if($this->randomTickBlocks[$blockId] !== null){
							/** @var Block $block */
							$block = clone $this->randomTickBlocks[$blockId];
							$block->setDamage($subChunk->getBlockData($x, $y, $z));

							$block->x = $chunkX * 16 + $x;
							$block->y = ($Y << 4) + $y;
							$block->z = $chunkZ * 16 + $z;
							$block->level = $this;
							$block->onRandomTick();
						}
					}
				}
			}
		}

		if($this->clearChunksOnTick){
			$this->chunkTickList = [];
		}
	}

	public function __debugInfo() : array{
		return [];
	}

	/**
	 * @param bool $force
	 *
	 * @return bool
	 */
	public function save(bool $force = false) : bool{

		if(!$this->getAutoSave() and !$force){
			return false;
		}

		$this->server->getPluginManager()->callEvent(new LevelSaveEvent($this));

		$this->provider->setTime($this->time);
		$this->saveChunks();
		if($this->provider instanceof BaseLevelProvider){
			$this->provider->saveLevelData();
		}

		return true;
	}

	public function saveChunks(){
		foreach($this->chunks as $chunk){
			if(($chunk->hasChanged() or count($chunk->getTiles()) > 0 or count($chunk->getSavableEntities()) > 0) and $chunk->isGenerated()){
				$this->provider->saveChunk($chunk);
				$chunk->setChanged(false);
			}
		}
	}

	/**
	 * Schedules a block update to be executed after the specified number of ticks.
	 * Blocks will be updated with the scheduled update type.
	 *
	 * @param Vector3 $pos
	 * @param int     $delay
	 */
	public function scheduleDelayedBlockUpdate(Vector3 $pos, int $delay){
		if(
			!$this->isInWorld($pos->x, $pos->y, $pos->z) or
			(isset($this->scheduledBlockUpdateQueueIndex[$index = Level::blockHash($pos->x, $pos->y, $pos->z)]) and $this->scheduledBlockUpdateQueueIndex[$index] <= $delay)
		){
			return;
		}
		$this->scheduledBlockUpdateQueueIndex[$index] = $delay;
		$this->scheduledBlockUpdateQueue->insert(new Vector3((int) $pos->x, (int) $pos->y, (int) $pos->z), $delay + $this->server->getTick());
	}

	/**
	 * Schedules the blocks around the specified position to be updated at the end of this tick.
	 * Blocks will be updated with the normal update type.
	 *
	 * @param Vector3 $pos
	 */
	public function scheduleNeighbourBlockUpdates(Vector3 $pos){
		$pos = $pos->floor();

		for($i = 0; $i <= 5; ++$i){
			$side = $pos->getSide($i);
			if($this->isInWorld($side->x, $side->y, $side->z)){
				$this->neighbourBlockUpdateQueue->enqueue(Level::blockHash($side->x, $side->y, $side->z));
			}
		}
	}

	/**
	 * @param AxisAlignedBB $bb
	 * @param bool          $targetFirst
	 *
	 * @return Block[]
	 */
	public function getCollisionBlocks(AxisAlignedBB $bb, bool $targetFirst = false) : array{
		$minX = (int) floor($bb->minX - 1);
		$minY = (int) floor($bb->minY - 1);
		$minZ = (int) floor($bb->minZ - 1);
		$maxX = (int) floor($bb->maxX + 1);
		$maxY = (int) floor($bb->maxY + 1);
		$maxZ = (int) floor($bb->maxZ + 1);

		$collides = [];

		if($targetFirst){
			for($z = $minZ; $z <= $maxZ; ++$z){
				for($x = $minX; $x <= $maxX; ++$x){
					for($y = $minY; $y <= $maxY; ++$y){
						$block = $this->getBlockAt($x, $y, $z);
						if(!$block->canPassThrough() and $block->collidesWithBB($bb)){
							return [$block];
						}
					}
				}
			}
		}else{
			for($z = $minZ; $z <= $maxZ; ++$z){
				for($x = $minX; $x <= $maxX; ++$x){
					for($y = $minY; $y <= $maxY; ++$y){
						$block = $this->getBlockAt($x, $y, $z);
						if(!$block->canPassThrough() and $block->collidesWithBB($bb)){
							$collides[] = $block;
						}
					}
				}
			}
		}


		return $collides;
	}

	/**
	 * @param Vector3 $pos
	 *
	 * @return bool
	 */
	public function isFullBlock(Vector3 $pos) : bool{
		if($pos instanceof Block){
			if($pos->isSolid()){
				return true;
			}
			$bb = $pos->getBoundingBox();
		}else{
			$bb = $this->getBlock($pos)->getBoundingBox();
		}

		return $bb !== null and $bb->getAverageEdgeLength() >= 1;
	}

	/**
	 * @param Entity        $entity
	 * @param AxisAlignedBB $bb
	 * @param bool          $entities
	 *
	 * @return AxisAlignedBB[]
	 */
	public function getCollisionCubes(Entity $entity, AxisAlignedBB $bb, bool $entities = true) : array{
		$minX = (int) floor($bb->minX - 1);
		$minY = (int) floor($bb->minY - 1);
		$minZ = (int) floor($bb->minZ - 1);
		$maxX = (int) floor($bb->maxX + 1);
		$maxY = (int) floor($bb->maxY + 1);
		$maxZ = (int) floor($bb->maxZ + 1);

		$collides = [];

		for($z = $minZ; $z <= $maxZ; ++$z){
			for($x = $minX; $x <= $maxX; ++$x){
				for($y = $minY; $y <= $maxY; ++$y){
					$block = $this->getBlockAt($x, $y, $z);
					if(!$block->canPassThrough()){
						foreach($block->getCollisionBoxes() as $blockBB){
							if($blockBB->intersectsWith($bb)){
								$collides[] = $blockBB;
							}
						}
					}
				}
			}
		}

		if($entities){
			foreach($this->getCollidingEntities($bb->grow(0.25, 0.25, 0.25), $entity) as $ent){
				$collides[] = clone $ent->boundingBox;
			}
		}

		return $collides;
	}

	public function getFullLight(Vector3 $pos) : int{
		return $this->getFullLightAt($pos->x, $pos->y, $pos->z);
	}

	public function getFullLightAt(int $x, int $y, int $z) : int{
		$skyLight = $this->getRealBlockSkyLightAt($x, $y, $z);
		if($skyLight < 15){
			return max($skyLight, $this->getBlockLightAt($x, $y, $z));
		}else{
			return $skyLight;
		}
	}

	/**
	 * Computes the percentage of a circle away from noon the sun is currently at. This can be multiplied by 2 * M_PI to
	 * get an angle in radians, or by 360 to get an angle in degrees.
	 *
	 * @return float
	 */
	public function computeSunAnglePercentage() : float{
		$timeProgress = ($this->time % 24000) / 24000;

		//0.0 needs to be high noon, not dusk
		$sunProgress = $timeProgress + ($timeProgress < 0.25 ? 0.75 : -0.25);

		//Offset the sun progress to be above the horizon longer at dusk and dawn
		//this is roughly an inverted sine curve, which pushes the sun progress back at dusk and forwards at dawn
		$diff = (((1 - ((cos($sunProgress * M_PI) + 1) / 2)) - $sunProgress) / 3);

		return $sunProgress + $diff;
	}

	/**
	 * Returns the percentage of a circle away from noon the sun is currently at.
	 * @return float
	 */
	public function getSunAnglePercentage() : float{
		return $this->sunAnglePercentage;
	}

	/**
	 * Returns the current sun angle in radians.
	 * @return float
	 */
	public function getSunAngleRadians() : float{
		return $this->sunAnglePercentage * 2 * M_PI;
	}

	/**
	 * Returns the current sun angle in degrees.
	 * @return float
	 */
	public function getSunAngleDegrees() : float{
		return $this->sunAnglePercentage * 360.0;
	}

	/**
	 * Computes how many points of sky light is subtracted based on the current time. Used to offset raw chunk sky light
	 * to get a real light value.
	 *
	 * @return int
	 */
	public function computeSkyLightReduction() : int{
		$percentage = max(0, min(1, -(cos($this->getSunAngleRadians()) * 2 - 0.5)));

		//TODO: check rain and thunder level

		return (int) ($percentage * 11);
	}

	/**
	 * Returns how many points of sky light is subtracted based on the current time.
	 * @return int
	 */
	public function getSkyLightReduction() : int{
		return $this->skyLightReduction;
	}

	/**
	 * Returns the sky light level at the specified coordinates, offset by the current time and weather.
	 *
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 *
	 * @return int 0-15
	 */
	public function getRealBlockSkyLightAt(int $x, int $y, int $z) : int{
		$light = $this->getBlockSkyLightAt($x, $y, $z) - $this->skyLightReduction;
		return $light < 0 ? 0 : $light;
	}

	/**
	 * @param $x
	 * @param $y
	 * @param $z
	 *
	 * @return int bitmap, (id << 4) | data
	 */
	public function getFullBlock(int $x, int $y, int $z) : int{
		return $this->getChunk($x >> 4, $z >> 4, false)->getFullBlock($x & 0x0f, $y, $z & 0x0f);
	}

	public function isInWorld(int $x, int $y, int $z) : bool{
		return (
			$x <= INT32_MAX and $x >= INT32_MIN and
			$y < $this->worldHeight and $y >= 0 and
			$z <= INT32_MAX and $z >= INT32_MIN
		);
	}

	/**
	 * Gets the Block object at the Vector3 location. This method wraps around {@link getBlockAt}, converting the
	 * vector components to integers.
	 *
	 * Note: If you're using this for performance-sensitive code, and you're guaranteed to be supplying ints in the
	 * specified vector, consider using {@link getBlockAt} instead for better performance.
	 *
	 * @param Vector3 $pos
	 * @param bool    $cached Whether to use the block cache for getting the block (faster, but may be inaccurate)
	 * @param bool    $addToCache Whether to cache the block object created by this method call.
	 *
	 * @return Block
	 */
	public function getBlock(Vector3 $pos, bool $cached = true, bool $addToCache = true) : Block{
		return $this->getBlockAt((int) floor($pos->x), (int) floor($pos->y), (int) floor($pos->z), $cached, $addToCache);
	}

	/**
	 * Gets the Block object at the specified coordinates.
	 *
	 * Note for plugin developers: If you are using this method a lot (thousands of times for many positions for
	 * example), you may want to set addToCache to false to avoid using excessive amounts of memory.
	 *
	 * @param int  $x
	 * @param int  $y
	 * @param int  $z
	 * @param bool $cached Whether to use the block cache for getting the block (faster, but may be inaccurate)
	 * @param bool $addToCache Whether to cache the block object created by this method call.
	 *
	 * @return Block
	 */
	public function getBlockAt(int $x, int $y, int $z, bool $cached = true, bool $addToCache = true) : Block{
		$fullState = 0;
		$blockHash = null;
		$chunkHash = Level::chunkHash($x >> 4, $z >> 4);

		if($this->isInWorld($x, $y, $z)){
			$blockHash = Level::blockHash($x, $y, $z);

			if($cached and isset($this->blockCache[$chunkHash][$blockHash])){
				return $this->blockCache[$chunkHash][$blockHash];
			}

			$chunk = $this->chunks[$chunkHash] ?? null;
			if($chunk !== null){
				$fullState = $chunk->getFullBlock($x & 0x0f, $y, $z & 0x0f);
			}else{
				$addToCache = false;
			}
		}

		$block = clone $this->blockStates[$fullState & 0xfff];

		$block->x = $x;
		$block->y = $y;
		$block->z = $z;
		$block->level = $this;

		if($addToCache and $blockHash !== null){
			$this->blockCache[$chunkHash][$blockHash] = $block;
		}

		return $block;
	}

	public function updateAllLight(Vector3 $pos){
		$this->updateBlockSkyLight($pos->x, $pos->y, $pos->z);
		$this->updateBlockLight($pos->x, $pos->y, $pos->z);
	}

	/**
	 * Returns the highest block light level available in the positions adjacent to the specified block coordinates.
	 *
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 *
	 * @return int
	 */
	public function getHighestAdjacentBlockSkyLight(int $x, int $y, int $z) : int{
		return max([
			$this->getBlockSkyLightAt($x + 1, $y, $z),
			$this->getBlockSkyLightAt($x - 1, $y, $z),
			$this->getBlockSkyLightAt($x, $y + 1, $z),
			$this->getBlockSkyLightAt($x, $y - 1, $z),
			$this->getBlockSkyLightAt($x, $y, $z + 1),
			$this->getBlockSkyLightAt($x, $y, $z - 1)
		]);
	}

	public function updateBlockSkyLight(int $x, int $y, int $z){
		$this->timings->doBlockSkyLightUpdates->startTiming();

		$oldHeightMap = $this->getHeightMap($x, $z);
		$sourceId = $this->getBlockIdAt($x, $y, $z);

		$yPlusOne = $y + 1;

		if($yPlusOne === $oldHeightMap){ //Block changed directly beneath the heightmap. Check if a block was removed or changed to a different light-filter.
			$newHeightMap = $this->getChunk($x >> 4, $z >> 4)->recalculateHeightMapColumn($x & 0x0f, $z & 0x0f);
		}elseif($yPlusOne > $oldHeightMap){ //Block changed above the heightmap.
			if(BlockFactory::$lightFilter[$sourceId] > 1 or BlockFactory::$diffusesSkyLight[$sourceId]){
				$this->setHeightMap($x, $z, $yPlusOne);
				$newHeightMap = $yPlusOne;
			}else{ //Block changed which has no effect on direct sky light, for example placing or removing glass.
				$this->timings->doBlockSkyLightUpdates->stopTiming();
				return;
			}
		}else{ //Block changed below heightmap
			$newHeightMap = $oldHeightMap;
		}

		$update = new SkyLightUpdate($this);

		if($newHeightMap > $oldHeightMap){ //Heightmap increase, block placed, remove sky light
			for($i = $y; $i >= $oldHeightMap; --$i){
				$update->setAndUpdateLight($x, $i, $z, 0); //Remove all light beneath, adjacent recalculation will handle the rest.
			}
		}elseif($newHeightMap < $oldHeightMap){ //Heightmap decrease, block changed or removed, add sky light
			for($i = $y; $i >= $newHeightMap; --$i){
				$update->setAndUpdateLight($x, $i, $z, 15);
			}
		}else{ //No heightmap change, block changed "underground"
			$update->setAndUpdateLight($x, $y, $z, max(0, $this->getHighestAdjacentBlockSkyLight($x, $y, $z) - BlockFactory::$lightFilter[$sourceId]));
		}

		$update->execute();

		$this->timings->doBlockSkyLightUpdates->stopTiming();
	}

	/**
	 * Returns the highest block light level available in the positions adjacent to the specified block coordinates.
	 *
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 *
	 * @return int
	 */
	public function getHighestAdjacentBlockLight(int $x, int $y, int $z) : int{
		return max([
			$this->getBlockLightAt($x + 1, $y, $z),
			$this->getBlockLightAt($x - 1, $y, $z),
			$this->getBlockLightAt($x, $y + 1, $z),
			$this->getBlockLightAt($x, $y - 1, $z),
			$this->getBlockLightAt($x, $y, $z + 1),
			$this->getBlockLightAt($x, $y, $z - 1)
		]);
	}

	public function updateBlockLight(int $x, int $y, int $z){
		$this->timings->doBlockLightUpdates->startTiming();

		$id = $this->getBlockIdAt($x, $y, $z);
		$newLevel = max(BlockFactory::$light[$id], $this->getHighestAdjacentBlockLight($x, $y, $z) - BlockFactory::$lightFilter[$id]);

		$update = new BlockLightUpdate($this);
		$update->setAndUpdateLight($x, $y, $z, $newLevel);
		$update->execute();

		$this->timings->doBlockLightUpdates->stopTiming();
	}

	/**
	 * Sets on Vector3 the data from a Block object,
	 * does block updates and puts the changes to the send queue.
	 *
	 * If $direct is true, it'll send changes directly to players. if false, it'll be queued
	 * and the best way to send queued changes will be done in the next tick.
	 * This way big changes can be sent on a single chunk update packet instead of thousands of packets.
	 *
	 * If $update is true, it'll get the neighbour blocks (6 sides) and update them.
	 * If you are doing big changes, you might want to set this to false, then update manually.
	 *
	 * @param Vector3 $pos
	 * @param Block   $block
	 * @param bool    $direct @deprecated
	 * @param bool    $update
	 *
	 * @return bool Whether the block has been updated or not
	 */
	public function setBlock(Vector3 $pos, Block $block, bool $direct = false, bool $update = true) : bool{
		$pos = $pos->floor();
		if(!$this->isInWorld($pos->x, $pos->y, $pos->z)){
			return false;
		}

		$this->timings->setBlock->startTiming();

		if($this->getChunk($pos->x >> 4, $pos->z >> 4, true)->setBlock($pos->x & 0x0f, $pos->y, $pos->z & 0x0f, $block->getId(), $block->getDamage())){
			if(!($pos instanceof Position)){
				$pos = $this->temporalPosition->setComponents($pos->x, $pos->y, $pos->z);
			}

			$block = clone $block;

			$block->position($pos);
			$block->clearCaches();

			$chunkHash = Level::chunkHash($pos->x >> 4, $pos->z >> 4);
			$blockHash = Level::blockHash($pos->x, $pos->y, $pos->z);

			unset($this->blockCache[$chunkHash][$blockHash]);

			if($direct){
				$this->sendBlocks($this->getChunkPlayers($pos->x >> 4, $pos->z >> 4), [$block], UpdateBlockPacket::FLAG_ALL_PRIORITY);
				unset($this->chunkCache[$chunkHash], $this->changedBlocks[$chunkHash][$blockHash]);
			}else{
				if(!isset($this->changedBlocks[$chunkHash])){
					$this->changedBlocks[$chunkHash] = [];
				}

				$this->changedBlocks[$chunkHash][$blockHash] = $block;
			}

			foreach($this->getChunkLoaders($pos->x >> 4, $pos->z >> 4) as $loader){
				$loader->onBlockChanged($block);
			}

			if($update){
				$this->updateAllLight($block);

				$this->server->getPluginManager()->callEvent($ev = new BlockUpdateEvent($block));
				if(!$ev->isCancelled()){
					foreach($this->getNearbyEntities(new AxisAlignedBB($block->x - 1, $block->y - 1, $block->z - 1, $block->x + 2, $block->y + 2, $block->z + 2)) as $entity){
						$entity->onNearbyBlockChange();
					}
					$ev->getBlock()->onNearbyBlockChange();
					$this->scheduleNeighbourBlockUpdates($pos);
				}
			}

			$this->timings->setBlock->stopTiming();

			return true;
		}

		$this->timings->setBlock->stopTiming();

		return false;
	}

	/**
	 * @param Vector3 $source
	 * @param Item    $item
	 * @param Vector3 $motion
	 * @param int     $delay
	 *
	 * @return ItemEntity|null
	 */
	public function dropItem(Vector3 $source, Item $item, Vector3 $motion = null, int $delay = 10){
		$motion = $motion ?? new Vector3(lcg_value() * 0.2 - 0.1, 0.2, lcg_value() * 0.2 - 0.1);
		$itemTag = $item->nbtSerialize();
		$itemTag->setName("Item");

		if(!$item->isNull()){
			$nbt = Entity::createBaseNBT($source, $motion, lcg_value() * 360, 0);
			$nbt->setShort("Health", 5);
			$nbt->setShort("PickupDelay", $delay);
			$nbt->setTag($itemTag);
			$itemEntity = Entity::createEntity("Item", $this, $nbt);

			if($itemEntity instanceof ItemEntity){
				$itemEntity->spawnToAll();

				return $itemEntity;
			}
		}
		return null;
	}

	/**
	 * Drops XP orbs into the world for the specified amount, splitting the amount into several orbs if necessary.
	 *
	 * @param Vector3 $pos
	 * @param int     $amount
	 *
	 * @return ExperienceOrb[]
	 */
	public function dropExperience(Vector3 $pos, int $amount) : array{
		/** @var ExperienceOrb[] $orbs */
		$orbs = [];

		foreach(ExperienceOrb::splitIntoOrbSizes($amount) as $split){
			$nbt = Entity::createBaseNBT(
				$pos,
				$this->temporalVector->setComponents((lcg_value() * 0.2 - 0.1) * 2, lcg_value() * 0.4, (lcg_value() * 0.2 - 0.1) * 2),
				lcg_value() * 360,
				0
			);
			$nbt->setShort(ExperienceOrb::TAG_VALUE_PC, $split);

			$orb = Entity::createEntity("XPOrb", $this, $nbt);
			if($orb === null){
				continue;
			}

			$orb->spawnToAll();
			if($orb instanceof ExperienceOrb){
				$orbs[] = $orb;
			}
		}

		return $orbs;
	}

	/**
	 * Checks if the level spawn protection radius will prevent the player from using items or building at the specified
	 * Vector3 position.
	 *
	 * @param Player  $player
	 * @param Vector3 $vector
	 *
	 * @return bool true if spawn protection cancelled the action, false if not.
	 */
	public function checkSpawnProtection(Player $player, Vector3 $vector) : bool{
		if(!$player->hasPermission("pocketmine.spawnprotect.bypass") and ($distance = $this->server->getSpawnRadius()) > -1){
			$t = new Vector2($vector->x, $vector->z);

			$spawnLocation = $this->getSpawnLocation();
			$s = new Vector2($spawnLocation->x, $spawnLocation->z);
			if(count($this->server->getOps()->getAll()) > 0 and $t->distance($s) <= $distance){
				return true;
			}
		}

		return false;
	}

	/**
	 * Tries to break a block using a item, including Player time checks if available
	 * It'll try to lower the durability if Item is a tool, and set it to Air if broken.
	 *
	 * @param Vector3 $vector
	 * @param Item    &$item (if null, can break anything)
	 * @param Player  $player
	 * @param bool    $createParticles
	 *
	 * @return bool
	 */
	public function useBreakOn(Vector3 $vector, Item &$item = null, Player $player = null, bool $createParticles = false) : bool{
		$target = $this->getBlock($vector);
		$affectedBlocks = $target->getAffectedBlocks();

		if($item === null){
			$item = ItemFactory::get(Item::AIR, 0, 0);
		}

		$drops = [];
		if($player === null or !$player->isCreative()){
			$drops = array_merge(...array_map(function(Block $block) use ($item) : array{ return $block->getDrops($item); }, $affectedBlocks));
		}

		$xpDrop = 0;
		if($player !== null and !$player->isCreative()){
			$xpDrop = array_sum(array_map(function(Block $block) use ($item) : int{ return $block->getXpDropForTool($item); }, $affectedBlocks));
		}

		if($player !== null){
			$ev = new BlockBreakEvent($player, $target, $item, $player->isCreative(), $drops, $xpDrop);

			if(($player->isSurvival() and !$target->isBreakable($item)) or $player->isSpectator()){
				$ev->setCancelled();
			}elseif($this->checkSpawnProtection($player, $target)){
				$ev->setCancelled(); //set it to cancelled so plugins can bypass this
			}

			if($player->isAdventure(true) and !$ev->isCancelled()){
				$tag = $item->getNamedTagEntry("CanDestroy");
				$canBreak = false;
				if($tag instanceof ListTag){
					foreach($tag as $v){
						if($v instanceof StringTag){
							$entry = ItemFactory::fromString($v->getValue());
							if($entry->getId() > 0 and $entry->getBlock()->getId() === $target->getId()){
								$canBreak = true;
								break;
							}
						}
					}
				}

				$ev->setCancelled(!$canBreak);
			}

			$this->server->getPluginManager()->callEvent($ev);
			if($ev->isCancelled()){
				return false;
			}

			$drops = $ev->getDrops();
			$xpDrop = $ev->getXpDropAmount();

		}elseif(!$target->isBreakable($item)){
			return false;
		}

		foreach($affectedBlocks as $t){
			$this->destroyBlockInternal($t, $item, $player, $createParticles);
		}

		$item->onDestroyBlock($target);

		if(!empty($drops)){
			$dropPos = $target->add(0.5, 0.5, 0.5);
			foreach($drops as $drop){
				if(!$drop->isNull()){
					$this->dropItem($dropPos, $drop);
				}
			}
		}

		if($xpDrop > 0){
			$this->dropExperience($target->add(0.5, 0.5, 0.5), $xpDrop);
		}

		return true;
	}

	private function destroyBlockInternal(Block $target, Item $item, ?Player $player = null, bool $createParticles = false) : void{
		if($createParticles){
			$this->addParticle(new DestroyBlockParticle($target->add(0.5, 0.5, 0.5), $target));
		}

		$target->onBreak($item, $player);

		$tile = $this->getTile($target);
		if($tile !== null){
			if($tile instanceof Container){
				if($tile instanceof Chest){
					$tile->unpair();
				}

				$tile->getInventory()->dropContents($this, $target);
			}

			$tile->close();
		}
	}

	/**
	 * Uses a item on a position and face, placing it or activating the block
	 *
	 * @param Vector3      $vector
	 * @param Item         $item
	 * @param int          $face
	 * @param Vector3|null $clickVector
	 * @param Player|null  $player default null
	 * @param bool         $playSound Whether to play a block-place sound if the block was placed successfully.
	 *
	 * @return bool
	 */
	public function useItemOn(Vector3 $vector, Item &$item, int $face, Vector3 $clickVector = null, Player $player = null, bool $playSound = false) : bool{
		$blockClicked = $this->getBlock($vector);
		$blockReplace = $blockClicked->getSide($face);

		if($clickVector === null){
			$clickVector = new Vector3(0.0, 0.0, 0.0);
		}

		if($blockReplace->y >= $this->worldHeight or $blockReplace->y < 0){
			//TODO: build height limit messages for custom world heights and mcregion cap
			return false;
		}

		if($blockClicked->getId() === Block::AIR){
			return false;
		}

		if($player !== null){
			$ev = new PlayerInteractEvent($player, $item, $blockClicked, $clickVector, $face, $blockClicked->getId() === 0 ? PlayerInteractEvent::RIGHT_CLICK_AIR : PlayerInteractEvent::RIGHT_CLICK_BLOCK);
			if($this->checkSpawnProtection($player, $blockClicked)){
				$ev->setCancelled(); //set it to cancelled so plugins can bypass this
			}

			$this->server->getPluginManager()->callEvent($ev);
			if(!$ev->isCancelled()){
				if(!$player->isSneaking() and $blockClicked->onActivate($item, $player)){
					return true;
				}

				if(!$player->isSneaking() and $item->onActivate($player, $blockReplace, $blockClicked, $face, $clickVector)){
					return true;
				}
			}else{
				return false;
			}
		}elseif($blockClicked->onActivate($item, $player)){
			return true;
		}

		if($item->canBePlaced()){
			$hand = $item->getBlock();
			$hand->position($blockReplace);
		}else{
			return false;
		}

		if($hand->canBePlacedAt($blockClicked, $clickVector, $face, true)){
			$blockReplace = $blockClicked;
			$hand->position($blockReplace);
		}elseif(!$hand->canBePlacedAt($blockReplace, $clickVector, $face, false)){
			return false;
		}

		if($hand->isSolid()){
			foreach($hand->getCollisionBoxes() as $collisionBox){
				if(!empty($this->getCollidingEntities($collisionBox))){
					return false;  //Entity in block
				}

				if($player !== null){
					if(($diff = $player->getNextPosition()->subtract($player->getPosition())) and $diff->lengthSquared() > 0.00001){
						$bb = $player->getBoundingBox()->getOffsetBoundingBox($diff->x, $diff->y, $diff->z);
						if($collisionBox->intersectsWith($bb)){
							return false; //Inside player BB
						}
					}
				}
			}
		}


		if($player !== null){
			$ev = new BlockPlaceEvent($player, $hand, $blockReplace, $blockClicked, $item);
			if($this->checkSpawnProtection($player, $blockClicked)){
				$ev->setCancelled();
			}

			if($player->isAdventure(true) and !$ev->isCancelled()){
				$canPlace = false;
				$tag = $item->getNamedTagEntry("CanPlaceOn");
				if($tag instanceof ListTag){
					foreach($tag as $v){
						if($v instanceof StringTag){
							$entry = ItemFactory::fromString($v->getValue());
							if($entry->getId() > 0 and $entry->getBlock()->getId() === $blockClicked->getId()){
								$canPlace = true;
								break;
							}
						}
					}
				}

				$ev->setCancelled(!$canPlace);
			}

			$this->server->getPluginManager()->callEvent($ev);
			if($ev->isCancelled()){
				return false;
			}
		}

		if(!$hand->place($item, $blockReplace, $blockClicked, $face, $clickVector, $player)){
			return false;
		}

		if($playSound){
			$this->broadcastLevelSoundEvent($hand, LevelSoundEventPacket::SOUND_PLACE, 1, BlockFactory::toStaticRuntimeId($hand->getId(), $hand->getDamage()));
		}

		$item->pop();

		return true;
	}

	/**
	 * @param int $entityId
	 *
	 * @return Entity|null
	 */
	public function getEntity(int $entityId){
		return $this->entities[$entityId] ?? null;
	}

	/**
	 * Gets the list of all the entities in this level
	 *
	 * @return Entity[]
	 */
	public function getEntities() : array{
		return $this->entities;
	}

	/**
	 * Returns the entities colliding the current one inside the AxisAlignedBB
	 *
	 * @param AxisAlignedBB $bb
	 * @param Entity|null   $entity
	 *
	 * @return Entity[]
	 */
	public function getCollidingEntities(AxisAlignedBB $bb, Entity $entity = null) : array{
		$nearby = [];

		if($entity === null or $entity->canCollide){
			$minX = ((int) floor($bb->minX - 2)) >> 4;
			$maxX = ((int) floor($bb->maxX + 2)) >> 4;
			$minZ = ((int) floor($bb->minZ - 2)) >> 4;
			$maxZ = ((int) floor($bb->maxZ + 2)) >> 4;

			for($x = $minX; $x <= $maxX; ++$x){
				for($z = $minZ; $z <= $maxZ; ++$z){
					foreach($this->getChunkEntities($x, $z) as $ent){
						/** @var Entity|null $entity */
						if($ent->canBeCollidedWith() and ($entity === null or ($ent !== $entity and $entity->canCollideWith($ent))) and $ent->boundingBox->intersectsWith($bb)){
							$nearby[] = $ent;
						}
					}
				}
			}
		}

		return $nearby;
	}

	/**
	 * Returns the entities near the current one inside the AxisAlignedBB
	 *
	 * @param AxisAlignedBB $bb
	 * @param Entity        $entity
	 *
	 * @return Entity[]
	 */
	public function getNearbyEntities(AxisAlignedBB $bb, Entity $entity = null) : array{
		$nearby = [];

		$minX = ((int) floor($bb->minX - 2)) >> 4;
		$maxX = ((int) floor($bb->maxX + 2)) >> 4;
		$minZ = ((int) floor($bb->minZ - 2)) >> 4;
		$maxZ = ((int) floor($bb->maxZ + 2)) >> 4;

		for($x = $minX; $x <= $maxX; ++$x){
			for($z = $minZ; $z <= $maxZ; ++$z){
				foreach($this->getChunkEntities($x, $z) as $ent){
					if($ent !== $entity and $ent->boundingBox->intersectsWith($bb)){
						$nearby[] = $ent;
					}
				}
			}
		}

		return $nearby;
	}

	/**
	 * Returns the closest Entity to the specified position, within the given radius.
	 *
	 * @param Vector3 $pos
	 * @param float   $maxDistance
	 * @param string  $entityType Class of entity to use for instanceof
	 * @param bool    $includeDead Whether to include entitites which are dead
	 *
	 * @return Entity|null an entity of type $entityType, or null if not found
	 */
	public function getNearestEntity(Vector3 $pos, float $maxDistance, string $entityType = Entity::class, bool $includeDead = false) : ?Entity{
		assert(is_a($entityType, Entity::class, true));

		$minX = ((int) floor($pos->x - $maxDistance)) >> 4;
		$maxX = ((int) floor($pos->x + $maxDistance)) >> 4;
		$minZ = ((int) floor($pos->z - $maxDistance)) >> 4;
		$maxZ = ((int) floor($pos->z + $maxDistance)) >> 4;

		$currentTargetDistSq = $maxDistance ** 2;

		/** @var Entity|null $currentTarget */
		$currentTarget = null;

		for($x = $minX; $x <= $maxX; ++$x){
			for($z = $minZ; $z <= $maxZ; ++$z){
				foreach($this->getChunkEntities($x, $z) as $entity){
					if(!($entity instanceof $entityType) or $entity->isClosed() or $entity->isFlaggedForDespawn() or (!$includeDead and !$entity->isAlive())){
						continue;
					}
					$distSq = $entity->distanceSquared($pos);
					if($distSq < $currentTargetDistSq){
						$currentTargetDistSq = $distSq;
						$currentTarget = $entity;
					}
				}
			}
		}

		return $currentTarget;
	}


	/**
	 * Returns a list of the Tile entities in this level
	 *
	 * @return Tile[]
	 */
	public function getTiles() : array{
		return $this->tiles;
	}

	/**
	 * @param $tileId
	 *
	 * @return Tile|null
	 */
	public function getTileById(int $tileId){
		return $this->tiles[$tileId] ?? null;
	}

	/**
	 * Returns a list of the players in this level
	 *
	 * @return Player[]
	 */
	public function getPlayers() : array{
		return $this->players;
	}

	/**
	 * @return ChunkLoader[]
	 */
	public function getLoaders() : array{
		return $this->loaders;
	}

	/**
	 * Returns the Tile in a position, or null if not found.
	 *
	 * Note: This method wraps getTileAt(). If you're guaranteed to be passing integers, and you're using this method
	 * in performance-sensitive code, consider using getTileAt() instead of this method for better performance.
	 *
	 * @param Vector3 $pos
	 *
	 * @return Tile|null
	 */
	public function getTile(Vector3 $pos) : ?Tile{
		return $this->getTileAt((int) floor($pos->x), (int) floor($pos->y), (int) floor($pos->z));
	}

	/**
	 * Returns the tile at the specified x,y,z coordinates, or null if it does not exist.
	 *
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 *
	 * @return Tile|null
	 */
	public function getTileAt(int $x, int $y, int $z) : ?Tile{
		$chunk = $this->getChunk($x >> 4, $z >> 4);

		if($chunk !== null){
			return $chunk->getTile($x & 0x0f, $y, $z & 0x0f);
		}

		return null;
	}

	/**
	 * Returns a list of the entities on a given chunk
	 *
	 * @param int $X
	 * @param int $Z
	 *
	 * @return Entity[]
	 */
	public function getChunkEntities($X, $Z) : array{
		return ($chunk = $this->getChunk($X, $Z)) !== null ? $chunk->getEntities() : [];
	}

	/**
	 * Gives a list of the Tile entities on a given chunk
	 *
	 * @param int $X
	 * @param int $Z
	 *
	 * @return Tile[]
	 */
	public function getChunkTiles($X, $Z) : array{
		return ($chunk = $this->getChunk($X, $Z)) !== null ? $chunk->getTiles() : [];
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
	public function getBlockIdAt(int $x, int $y, int $z) : int{
		return $this->getChunk($x >> 4, $z >> 4, true)->getBlockId($x & 0x0f, $y, $z & 0x0f);
	}

	/**
	 * Sets the raw block id.
	 *
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 * @param int $id 0-255
	 */
	public function setBlockIdAt(int $x, int $y, int $z, int $id){
		unset($this->blockCache[$chunkHash = Level::chunkHash($x >> 4, $z >> 4)][$blockHash = Level::blockHash($x, $y, $z)]);
		$this->getChunk($x >> 4, $z >> 4, true)->setBlockId($x & 0x0f, $y, $z & 0x0f, $id & 0xff);

		if(!isset($this->changedBlocks[$chunkHash])){
			$this->changedBlocks[$chunkHash] = [];
		}
		$this->changedBlocks[$chunkHash][$blockHash] = $v = new Vector3($x, $y, $z);
		foreach($this->getChunkLoaders($x >> 4, $z >> 4) as $loader){
			$loader->onBlockChanged($v);
		}
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
	public function getBlockDataAt(int $x, int $y, int $z) : int{
		return $this->getChunk($x >> 4, $z >> 4, true)->getBlockData($x & 0x0f, $y, $z & 0x0f);
	}

	/**
	 * Sets the raw block metadata.
	 *
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 * @param int $data 0-15
	 */
	public function setBlockDataAt(int $x, int $y, int $z, int $data){
		unset($this->blockCache[$chunkHash = Level::chunkHash($x >> 4, $z >> 4)][$blockHash = Level::blockHash($x, $y, $z)]);

		$this->getChunk($x >> 4, $z >> 4, true)->setBlockData($x & 0x0f, $y, $z & 0x0f, $data & 0x0f);

		if(!isset($this->changedBlocks[$chunkHash])){
			$this->changedBlocks[$chunkHash] = [];
		}
		$this->changedBlocks[$chunkHash][$blockHash] = $v = new Vector3($x, $y, $z);
		foreach($this->getChunkLoaders($x >> 4, $z >> 4) as $loader){
			$loader->onBlockChanged($v);
		}
	}

	/**
	 * Gets the raw block skylight level
	 *
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 *
	 * @return int 0-15
	 */
	public function getBlockSkyLightAt(int $x, int $y, int $z) : int{
		return $this->getChunk($x >> 4, $z >> 4, true)->getBlockSkyLight($x & 0x0f, $y, $z & 0x0f);
	}

	/**
	 * Sets the raw block skylight level.
	 *
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 * @param int $level 0-15
	 */
	public function setBlockSkyLightAt(int $x, int $y, int $z, int $level){
		$this->getChunk($x >> 4, $z >> 4, true)->setBlockSkyLight($x & 0x0f, $y, $z & 0x0f, $level & 0x0f);
	}

	/**
	 * Gets the raw block light level
	 *
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 *
	 * @return int 0-15
	 */
	public function getBlockLightAt(int $x, int $y, int $z) : int{
		return $this->getChunk($x >> 4, $z >> 4, true)->getBlockLight($x & 0x0f, $y, $z & 0x0f);
	}

	/**
	 * Sets the raw block light level.
	 *
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 * @param int $level 0-15
	 */
	public function setBlockLightAt(int $x, int $y, int $z, int $level){
		$this->getChunk($x >> 4, $z >> 4, true)->setBlockLight($x & 0x0f, $y, $z & 0x0f, $level & 0x0f);
	}

	/**
	 * @param int $x
	 * @param int $z
	 *
	 * @return int
	 */
	public function getBiomeId(int $x, int $z) : int{
		return $this->getChunk($x >> 4, $z >> 4, true)->getBiomeId($x & 0x0f, $z & 0x0f);
	}

	/**
	 * @param int $x
	 * @param int $z
	 *
	 * @return Biome
	 */
	public function getBiome(int $x, int $z) : Biome{
		return Biome::getBiome($this->getBiomeId($x, $z));
	}

	/**
	 * @param int $x
	 * @param int $z
	 * @param int $biomeId
	 */
	public function setBiomeId(int $x, int $z, int $biomeId){
		$this->getChunk($x >> 4, $z >> 4, true)->setBiomeId($x & 0x0f, $z & 0x0f, $biomeId);
	}

	/**
	 * @param int $x
	 * @param int $z
	 *
	 * @return int
	 */
	public function getHeightMap(int $x, int $z) : int{
		return $this->getChunk($x >> 4, $z >> 4, true)->getHeightMap($x & 0x0f, $z & 0x0f);
	}

	/**
	 * @param int $x
	 * @param int $z
	 * @param int $value
	 */
	public function setHeightMap(int $x, int $z, int $value){
		$this->getChunk($x >> 4, $z >> 4, true)->setHeightMap($x & 0x0f, $z & 0x0f, $value);
	}

	/**
	 * @return Chunk[]
	 */
	public function getChunks() : array{
		return $this->chunks;
	}

	/**
	 * Returns the chunk at the specified X/Z coordinates. If the chunk is not loaded, attempts to (synchronously!!!)
	 * load it.
	 *
	 * @param int  $x
	 * @param int  $z
	 * @param bool $create Whether to create an empty chunk as a placeholder if the chunk does not exist
	 *
	 * @return Chunk|null
	 */
	public function getChunk(int $x, int $z, bool $create = false){
		if(isset($this->chunks[$index = Level::chunkHash($x, $z)])){
			return $this->chunks[$index];
		}elseif($this->loadChunk($x, $z, $create)){
			return $this->chunks[$index];
		}

		return null;
	}

	/**
	 * Returns the chunks adjacent to the specified chunk.
	 *
	 * @param int $x
	 * @param int $z
	 *
	 * @return Chunk[]
	 */
	public function getAdjacentChunks(int $x, int $z) : array{
		$result = [];
		for($xx = 0; $xx <= 2; ++$xx){
			for($zz = 0; $zz <= 2; ++$zz){
				$i = $zz * 3 + $xx;
				if($i === 4){
					continue; //center chunk
				}
				$result[$i] = $this->getChunk($x + $xx - 1, $z + $zz - 1, false);
			}
		}

		return $result;
	}

	public function generateChunkCallback(int $x, int $z, Chunk $chunk){
		Timings::$generationCallbackTimer->startTiming();
		if(isset($this->chunkPopulationQueue[$index = Level::chunkHash($x, $z)])){
			$oldChunk = $this->getChunk($x, $z, false);
			for($xx = -1; $xx <= 1; ++$xx){
				for($zz = -1; $zz <= 1; ++$zz){
					unset($this->chunkPopulationLock[Level::chunkHash($x + $xx, $z + $zz)]);
				}
			}
			unset($this->chunkPopulationQueue[$index]);
			$this->setChunk($x, $z, $chunk, false);
			if(($oldChunk === null or !$oldChunk->isPopulated()) and $chunk->isPopulated()){
				$this->server->getPluginManager()->callEvent(new ChunkPopulateEvent($this, $chunk));

				foreach($this->getChunkLoaders($x, $z) as $loader){
					$loader->onChunkPopulated($chunk);
				}
			}
		}elseif(isset($this->chunkPopulationLock[$index])){
			unset($this->chunkPopulationLock[$index]);
			$this->setChunk($x, $z, $chunk, false);
		}else{
			$this->setChunk($x, $z, $chunk, false);
		}
		Timings::$generationCallbackTimer->stopTiming();
	}

	/**
	 * @param int        $chunkX
	 * @param int        $chunkZ
	 * @param Chunk|null $chunk
	 * @param bool       $unload
	 */
	public function setChunk(int $chunkX, int $chunkZ, Chunk $chunk = null, bool $unload = true){
		if($chunk === null){
			return;
		}

		$chunk->setX($chunkX);
		$chunk->setZ($chunkZ);

		$chunkHash = Level::chunkHash($chunkX, $chunkZ);
		$oldChunk = $this->getChunk($chunkX, $chunkZ, false);
		if($unload and $oldChunk !== null){
			$this->unloadChunk($chunkX, $chunkZ, false, false);
		}else{
			$oldEntities = $oldChunk !== null ? $oldChunk->getEntities() : [];
			$oldTiles = $oldChunk !== null ? $oldChunk->getTiles() : [];

			foreach($oldEntities as $entity){
				$chunk->addEntity($entity);
				$oldChunk->removeEntity($entity);
				$entity->chunk = $chunk;
			}

			foreach($oldTiles as $tile){
				$chunk->addTile($tile);
				$oldChunk->removeTile($tile);
			}
		}

		$this->chunks[$chunkHash] = $chunk;

		unset($this->blockCache[$chunkHash]);
		unset($this->chunkCache[$chunkHash]);
		$chunk->setChanged();

		if(!$this->isChunkInUse($chunkX, $chunkZ)){
			$this->unloadChunkRequest($chunkX, $chunkZ);
		}else{
			foreach($this->getChunkLoaders($chunkX, $chunkZ) as $loader){
				$loader->onChunkChanged($chunk);
			}
		}
	}

	/**
	 * Gets the highest block Y value at a specific $x and $z
	 *
	 * @param int $x
	 * @param int $z
	 *
	 * @return int 0-255
	 */
	public function getHighestBlockAt(int $x, int $z) : int{
		return $this->getChunk($x >> 4, $z >> 4, true)->getHighestBlockAt($x & 0x0f, $z & 0x0f);
	}

	/**
	 * @param int $x
	 * @param int $z
	 *
	 * @return bool
	 */
	public function isChunkLoaded(int $x, int $z) : bool{
		return isset($this->chunks[Level::chunkHash($x, $z)]);
	}

	/**
	 * @param int $x
	 * @param int $z
	 *
	 * @return bool
	 */
	public function isChunkGenerated(int $x, int $z) : bool{
		$chunk = $this->getChunk($x, $z);
		return $chunk !== null ? $chunk->isGenerated() : false;
	}

	/**
	 * @param int $x
	 * @param int $z
	 *
	 * @return bool
	 */
	public function isChunkPopulated(int $x, int $z) : bool{
		$chunk = $this->getChunk($x, $z);
		return $chunk !== null ? $chunk->isPopulated() : false;
	}

	/**
	 * Returns a Position pointing to the spawn
	 *
	 * @return Position
	 */
	public function getSpawnLocation() : Position{
		return Position::fromObject($this->provider->getSpawn(), $this);
	}

	/**
	 * Sets the level spawn location
	 *
	 * @param Vector3 $pos
	 */
	public function setSpawnLocation(Vector3 $pos){
		$previousSpawn = $this->getSpawnLocation();
		$this->provider->setSpawn($pos);
		$this->server->getPluginManager()->callEvent(new SpawnChangeEvent($this, $previousSpawn));
	}

	public function requestChunk(int $x, int $z, Player $player){
		$index = Level::chunkHash($x, $z);
		if(!isset($this->chunkSendQueue[$index])){
			$this->chunkSendQueue[$index] = [];
		}

		$this->chunkSendQueue[$index][$player->getLoaderId()] = $player;
	}

	private function sendChunkFromCache(int $x, int $z){
		if(isset($this->chunkSendTasks[$index = Level::chunkHash($x, $z)])){
			foreach($this->chunkSendQueue[$index] as $player){
				/** @var Player $player */
				if($player->isConnected() and isset($player->usedChunks[$index])){
					$player->sendChunk($x, $z, $this->chunkCache[$index]);
				}
			}
			unset($this->chunkSendQueue[$index]);
			unset($this->chunkSendTasks[$index]);
		}
	}

	private function processChunkRequest(){
		if(count($this->chunkSendQueue) > 0){
			$this->timings->syncChunkSendTimer->startTiming();

			foreach($this->chunkSendQueue as $index => $players){
				if(isset($this->chunkSendTasks[$index])){
					continue;
				}
				Level::getXZ($index, $x, $z);
				$this->chunkSendTasks[$index] = true;
				if(isset($this->chunkCache[$index])){
					$this->sendChunkFromCache($x, $z);
					continue;
				}
				$this->timings->syncChunkSendPrepareTimer->startTiming();

				$chunk = $this->chunks[$index] ?? null;
				if(!($chunk instanceof Chunk)){
					throw new ChunkException("Invalid Chunk sent");
				}

				$this->server->getAsyncPool()->submitTask(new ChunkRequestTask($this, $chunk));

				$this->timings->syncChunkSendPrepareTimer->stopTiming();
			}

			$this->timings->syncChunkSendTimer->stopTiming();
		}
	}

	public function chunkRequestCallback(int $x, int $z, BatchPacket $payload){
		$this->timings->syncChunkSendTimer->startTiming();

		$index = Level::chunkHash($x, $z);

		if(!isset($this->chunkCache[$index]) and $this->server->getMemoryManager()->canUseChunkCache()){
			$this->chunkCache[$index] = $payload;
			$this->sendChunkFromCache($x, $z);
			$this->timings->syncChunkSendTimer->stopTiming();
			return;
		}

		if(isset($this->chunkSendTasks[$index])){
			foreach($this->chunkSendQueue[$index] as $player){
				/** @var Player $player */
				if($player->isConnected() and isset($player->usedChunks[$index])){
					$player->sendChunk($x, $z, $payload);
				}
			}
			unset($this->chunkSendQueue[$index]);
			unset($this->chunkSendTasks[$index]);
		}
		$this->timings->syncChunkSendTimer->stopTiming();
	}

	/**
	 * @param Entity $entity
	 *
	 * @throws LevelException
	 */
	public function addEntity(Entity $entity){
		if($entity->isClosed()){
			throw new \InvalidArgumentException("Attempted to add a garbage closed Entity to Level");
		}
		if($entity->getLevel() !== $this){
			throw new LevelException("Invalid Entity level");
		}

		if($entity instanceof Player){
			$this->players[$entity->getId()] = $entity;
		}
		$this->entities[$entity->getId()] = $entity;
	}

	/**
	 * Removes the entity from the level index
	 *
	 * @param Entity $entity
	 *
	 * @throws LevelException
	 */
	public function removeEntity(Entity $entity){
		if($entity->getLevel() !== $this){
			throw new LevelException("Invalid Entity level");
		}

		if($entity instanceof Player){
			unset($this->players[$entity->getId()]);
			$this->checkSleep();
		}

		unset($this->entities[$entity->getId()]);
		unset($this->updateEntities[$entity->getId()]);
	}

	/**
	 * @param Tile $tile
	 *
	 * @throws LevelException
	 */
	public function addTile(Tile $tile){
		if($tile->isClosed()){
			throw new \InvalidArgumentException("Attempted to add a garbage closed Tile to Level");
		}
		if($tile->getLevel() !== $this){
			throw new LevelException("Invalid Tile level");
		}

		$chunkX = $tile->getFloorX() >> 4;
		$chunkZ = $tile->getFloorZ() >> 4;

		if(isset($this->chunks[$hash = Level::chunkHash($chunkX, $chunkZ)])){
			$this->chunks[$hash]->addTile($tile);
		}else{
			throw new \InvalidStateException("Attempted to create tile " . get_class($tile) . " in unloaded chunk $chunkX $chunkZ");
		}

		$this->tiles[$tile->getId()] = $tile;
		$this->clearChunkCache($chunkX, $chunkZ);
	}

	/**
	 * @param Tile $tile
	 *
	 * @throws LevelException
	 */
	public function removeTile(Tile $tile){
		if($tile->getLevel() !== $this){
			throw new LevelException("Invalid Tile level");
		}

		unset($this->tiles[$tile->getId()], $this->updateTiles[$tile->getId()]);

		$chunkX = $tile->getFloorX() >> 4;
		$chunkZ = $tile->getFloorZ() >> 4;

		if(isset($this->chunks[$hash = Level::chunkHash($chunkX, $chunkZ)])){
			$this->chunks[$hash]->removeTile($tile);
		}
		$this->clearChunkCache($chunkX, $chunkZ);
	}

	/**
	 * @param int $x
	 * @param int $z
	 *
	 * @return bool
	 */
	public function isChunkInUse(int $x, int $z) : bool{
		return isset($this->chunkLoaders[$index = Level::chunkHash($x, $z)]) and count($this->chunkLoaders[$index]) > 0;
	}

	/**
	 * Attempts to load a chunk from the level provider (if not already loaded).
	 *
	 * @param int  $x
	 * @param int  $z
	 * @param bool $create Whether to create an empty chunk to load if the chunk cannot be loaded from disk.
	 *
	 * @return bool if loading the chunk was successful
	 *
	 * @throws \InvalidStateException
	 */
	public function loadChunk(int $x, int $z, bool $create = true) : bool{
		if(isset($this->chunks[$chunkHash = Level::chunkHash($x, $z)])){
			return true;
		}

		$this->timings->syncChunkLoadTimer->startTiming();

		$this->cancelUnloadChunkRequest($x, $z);

		$this->timings->syncChunkLoadDataTimer->startTiming();

		$chunk = null;

		try{
			$chunk = $this->provider->loadChunk($x, $z);
		}catch(\Exception $e){
			$logger = $this->server->getLogger();
			$logger->critical("An error occurred while loading chunk x=$x z=$z: " . $e->getMessage());
			$logger->logException($e);
		}

		if($chunk === null and $create){
			$chunk = new Chunk($x, $z);
		}

		$this->timings->syncChunkLoadDataTimer->stopTiming();

		if($chunk === null){
			$this->timings->syncChunkLoadTimer->stopTiming();
			return false;
		}

		$this->chunks[$chunkHash] = $chunk;
		unset($this->blockCache[$chunkHash]);

		$chunk->initChunk($this);

		$this->server->getPluginManager()->callEvent(new ChunkLoadEvent($this, $chunk, !$chunk->isGenerated()));

		if(!$chunk->isLightPopulated() and $chunk->isPopulated() and $this->getServer()->getProperty("chunk-ticking.light-updates", false)){
			$this->getServer()->getAsyncPool()->submitTask(new LightPopulationTask($this, $chunk));
		}

		if($this->isChunkInUse($x, $z)){
			foreach($this->getChunkLoaders($x, $z) as $loader){
				$loader->onChunkLoaded($chunk);
			}
		}else{
			$this->unloadChunkRequest($x, $z);
		}

		$this->timings->syncChunkLoadTimer->stopTiming();

		return true;
	}

	private function queueUnloadChunk(int $x, int $z){
		$this->unloadQueue[$index = Level::chunkHash($x, $z)] = microtime(true);
		unset($this->chunkTickList[$index]);
	}

	public function unloadChunkRequest(int $x, int $z, bool $safe = true){
		if(($safe and $this->isChunkInUse($x, $z)) or $this->isSpawnChunk($x, $z)){
			return false;
		}

		$this->queueUnloadChunk($x, $z);

		return true;
	}

	public function cancelUnloadChunkRequest(int $x, int $z){
		unset($this->unloadQueue[Level::chunkHash($x, $z)]);
	}

	public function unloadChunk(int $x, int $z, bool $safe = true, bool $trySave = true) : bool{
		if($safe and $this->isChunkInUse($x, $z)){
			return false;
		}

		if(!$this->isChunkLoaded($x, $z)){
			return true;
		}

		$this->timings->doChunkUnload->startTiming();

		$chunkHash = Level::chunkHash($x, $z);

		$chunk = $this->chunks[$chunkHash] ?? null;

		if($chunk !== null){
			$this->server->getPluginManager()->callEvent($ev = new ChunkUnloadEvent($this, $chunk));
			if($ev->isCancelled()){
				$this->timings->doChunkUnload->stopTiming();

				return false;
			}

			try{
				if($trySave and $this->getAutoSave() and $chunk->isGenerated()){
					if($chunk->hasChanged() or count($chunk->getTiles()) > 0 or count($chunk->getSavableEntities()) > 0){
						$this->provider->saveChunk($chunk);
					}
				}

				foreach($this->getChunkLoaders($x, $z) as $loader){
					$loader->onChunkUnloaded($chunk);
				}

				$chunk->onUnload();
			}catch(\Throwable $e){
				$logger = $this->server->getLogger();
				$logger->error($this->server->getLanguage()->translateString("pocketmine.level.chunkUnloadError", [$e->getMessage()]));
				$logger->logException($e);
			}
		}

		unset($this->chunks[$chunkHash]);
		unset($this->chunkTickList[$chunkHash]);
		unset($this->chunkCache[$chunkHash]);
		unset($this->blockCache[$chunkHash]);
		unset($this->changedBlocks[$chunkHash]);

		$this->timings->doChunkUnload->stopTiming();

		return true;
	}

	/**
	 * Returns whether the chunk at the specified coordinates is a spawn chunk
	 *
	 * @param int $X
	 * @param int $Z
	 *
	 * @return bool
	 */
	public function isSpawnChunk(int $X, int $Z) : bool{
		$spawn = $this->provider->getSpawn();
		$spawnX = $spawn->x >> 4;
		$spawnZ = $spawn->z >> 4;

		return abs($X - $spawnX) <= 1 and abs($Z - $spawnZ) <= 1;
	}

	/**
	 * @param Vector3|null $spawn
	 *
	 * @return Position
	 */
	public function getSafeSpawn(?Vector3 $spawn = null) : Position{
		if(!($spawn instanceof Vector3) or $spawn->y < 1){
			$spawn = $this->getSpawnLocation();
		}

		$max = $this->worldHeight;
		$v = $spawn->floor();
		$chunk = $this->getChunk($v->x >> 4, $v->z >> 4, false);
		$x = (int) $v->x;
		$z = (int) $v->z;
		if($chunk !== null){
			$y = (int) min($max - 2, $v->y);
			$wasAir = ($chunk->getBlockId($x & 0x0f, $y - 1, $z & 0x0f) === 0);
			for(; $y > 0; --$y){
				if($this->isFullBlock($this->getBlockAt($x, $y, $z))){
					if($wasAir){
						$y++;
						break;
					}
				}else{
					$wasAir = true;
				}
			}

			for(; $y >= 0 and $y < $max; ++$y){
				if(!$this->isFullBlock($this->getBlockAt($x, $y + 1, $z))){
					if(!$this->isFullBlock($this->getBlockAt($x, $y, $z))){
						return new Position($spawn->x, $y === (int) $spawn->y ? $spawn->y : $y, $spawn->z, $this);
					}
				}else{
					++$y;
				}
			}

			$v->y = $y;
		}

		return new Position($spawn->x, $v->y, $spawn->z, $this);
	}

	/**
	 * Gets the current time
	 *
	 * @return int
	 */
	public function getTime() : int{
		return $this->time;
	}

	/**
	 * Returns the Level name
	 *
	 * @return string
	 */
	public function getName() : string{
		return $this->displayName;
	}

	/**
	 * Returns the Level folder name
	 *
	 * @return string
	 */
	public function getFolderName() : string{
		return $this->folderName;
	}

	/**
	 * Sets the current time on the level
	 *
	 * @param int $time
	 */
	public function setTime(int $time){
		$this->time = $time;
		$this->sendTime();
	}

	/**
	 * Stops the time for the level, will not save the lock state to disk
	 */
	public function stopTime(){
		$this->stopTime = true;
		$this->sendTime();
	}

	/**
	 * Start the time again, if it was stopped
	 */
	public function startTime(){
		$this->stopTime = false;
		$this->sendTime();
	}

	/**
	 * Gets the level seed
	 *
	 * @return int
	 */
	public function getSeed() : int{
		return $this->provider->getSeed();
	}

	/**
	 * Sets the seed for the level
	 *
	 * @param int $seed
	 */
	public function setSeed(int $seed){
		$this->provider->setSeed($seed);
	}

	public function getWorldHeight() : int{
		return $this->worldHeight;
	}

	/**
	 * @return int
	 */
	public function getDifficulty() : int{
		return $this->provider->getDifficulty();
	}

	/**
	 * @param int $difficulty
	 */
	public function setDifficulty(int $difficulty){
		if($difficulty < 0 or $difficulty > 3){
			throw new \InvalidArgumentException("Invalid difficulty level $difficulty");
		}
		$this->provider->setDifficulty($difficulty);

		$this->sendDifficulty();
	}

	/**
	 * @param Player ...$targets
	 */
	public function sendDifficulty(Player ...$targets){
		if(count($targets) === 0){
			$targets = $this->getPlayers();
		}

		$pk = new SetDifficultyPacket();
		$pk->difficulty = $this->getDifficulty();
		$this->server->broadcastPacket($targets, $pk);
	}

	public function populateChunk(int $x, int $z, bool $force = false) : bool{
		if(isset($this->chunkPopulationQueue[$index = Level::chunkHash($x, $z)]) or (count($this->chunkPopulationQueue) >= $this->chunkPopulationQueueSize and !$force)){
			return false;
		}

		$chunk = $this->getChunk($x, $z, true);
		if(!$chunk->isPopulated()){
			Timings::$populationTimer->startTiming();
			$populate = true;
			for($xx = -1; $xx <= 1; ++$xx){
				for($zz = -1; $zz <= 1; ++$zz){
					if(isset($this->chunkPopulationLock[Level::chunkHash($x + $xx, $z + $zz)])){
						$populate = false;
						break;
					}
				}
			}

			if($populate){
				if(!isset($this->chunkPopulationQueue[$index])){
					$this->chunkPopulationQueue[$index] = true;
					for($xx = -1; $xx <= 1; ++$xx){
						for($zz = -1; $zz <= 1; ++$zz){
							$this->chunkPopulationLock[Level::chunkHash($x + $xx, $z + $zz)] = true;
						}
					}
					$task = new PopulationTask($this, $chunk);
					$this->server->getAsyncPool()->submitTask($task);
				}
			}

			Timings::$populationTimer->stopTiming();
			return false;
		}

		return true;
	}

	public function doChunkGarbageCollection(){
		$this->timings->doChunkGC->startTiming();

		foreach($this->chunks as $index => $chunk){
			if(!isset($this->unloadQueue[$index])){
				Level::getXZ($index, $X, $Z);
				if(!$this->isSpawnChunk($X, $Z)){
					$this->unloadChunkRequest($X, $Z, true);
				}
			}
		}

		$this->provider->doGarbageCollection();

		$this->timings->doChunkGC->stopTiming();
	}

	public function unloadChunks(bool $force = false){
		if(count($this->unloadQueue) > 0){
			$maxUnload = 96;
			$now = microtime(true);
			foreach($this->unloadQueue as $index => $time){
				Level::getXZ($index, $X, $Z);

				if(!$force){
					if($maxUnload <= 0){
						break;
					}elseif($time > ($now - 30)){
						continue;
					}
				}

				//If the chunk can't be unloaded, it stays on the queue
				if($this->unloadChunk($X, $Z, true)){
					unset($this->unloadQueue[$index]);
					--$maxUnload;
				}
			}
		}
	}

	public function setMetadata(string $metadataKey, MetadataValue $newMetadataValue){
		$this->server->getLevelMetadata()->setMetadata($this, $metadataKey, $newMetadataValue);
	}

	public function getMetadata(string $metadataKey){
		return $this->server->getLevelMetadata()->getMetadata($this, $metadataKey);
	}

	public function hasMetadata(string $metadataKey) : bool{
		return $this->server->getLevelMetadata()->hasMetadata($this, $metadataKey);
	}

	public function removeMetadata(string $metadataKey, Plugin $owningPlugin){
		$this->server->getLevelMetadata()->removeMetadata($this, $metadataKey, $owningPlugin);
	}
}
