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
use pocketmine\entity\Arrow;
use pocketmine\entity\Effect;
use pocketmine\entity\Entity;
use pocketmine\entity\Item as DroppedItem;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockUpdateEvent;
use pocketmine\event\level\ChunkLoadEvent;
use pocketmine\event\level\ChunkPopulateEvent;
use pocketmine\event\level\ChunkUnloadEvent;
use pocketmine\event\level\LevelSaveEvent;
use pocketmine\event\level\LevelUnloadEvent;
use pocketmine\event\level\SpawnChangeEvent;
use pocketmine\event\LevelTimings;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\Timings;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\level\format\Chunk;
use pocketmine\level\format\io\BaseLevelProvider;
use pocketmine\level\format\io\LevelProvider;
use pocketmine\level\generator\GenerationTask;
use pocketmine\level\generator\Generator;
use pocketmine\level\generator\GeneratorRegisterTask;
use pocketmine\level\generator\GeneratorUnregisterTask;
use pocketmine\level\generator\LightPopulationTask;
use pocketmine\level\generator\PopulationTask;
use pocketmine\level\light\BlockLightUpdate;
use pocketmine\level\light\SkyLightUpdate;
use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\level\particle\Particle;
use pocketmine\level\sound\Sound;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Math;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3;
use pocketmine\metadata\BlockMetadataStore;
use pocketmine\metadata\Metadatable;
use pocketmine\metadata\MetadataValue;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\ShortTag;
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
use pocketmine\utils\Random;
use pocketmine\utils\ReversePriorityQueue;

#include <rules/Level.h>

class Level implements ChunkManager, Metadatable{

	private static $levelIdCounter = 1;
	private static $chunkLoaderCounter = 1;
	public static $COMPRESSION_LEVEL = 8;

	const Y_MASK = 0xFF;
	const Y_MAX = 0x100; //256

	const BLOCK_UPDATE_NORMAL = 1;
	const BLOCK_UPDATE_RANDOM = 2;
	const BLOCK_UPDATE_SCHEDULED = 3;
	const BLOCK_UPDATE_WEAK = 4;
	const BLOCK_UPDATE_TOUCH = 5;

	const TIME_DAY = 0;
	const TIME_SUNSET = 12000;
	const TIME_NIGHT = 14000;
	const TIME_SUNRISE = 23000;

	const TIME_FULL = 24000;

	const DIFFICULTY_PEACEFUL = 0;
	const DIFFICULTY_EASY = 1;
	const DIFFICULTY_NORMAL = 2;
	const DIFFICULTY_HARD = 3;

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

	private $blockCache = [];

	/** @var BatchPacket[] */
	private $chunkCache = [];

	private $sendTimeTicker = 0;

	/** @var Server */
	private $server;

	/** @var int */
	private $levelId;

	/** @var LevelProvider */
	private $provider;

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

	/** @var float[] */
	private $unloadQueue = [];

	/** @var int */
	private $time;
	public $stopTime;

	private $folderName;

	/** @var Chunk[] */
	private $chunks = [];

	/** @var Vector3[][] */
	private $changedBlocks = [];

	/** @var ReversePriorityQueue */
	private $scheduledBlockUpdateQueue;
	private $scheduledBlockUpdateQueueIndex = [];

	/** @var \SplQueue */
	private $neighbourBlockUpdateQueue;

	/** @var Player[][] */
	private $chunkSendQueue = [];
	private $chunkSendTasks = [];

	private $chunkPopulationQueue = [];
	private $chunkPopulationLock = [];
	private $chunkGenerationQueue = [];
	private $chunkGenerationQueueSize = 8;
	private $chunkPopulationQueueSize = 2;

	private $autoSave = true;

	/** @var BlockMetadataStore */
	private $blockMetadata;

	/** @var Position */
	private $temporalPosition;
	/** @var Vector3 */
	private $temporalVector;

	/** @var \SplFixedArray */
	private $blockStates;

	public $sleepTicks = 0;

	private $chunkTickRadius;
	private $chunkTickList = [];
	private $chunksPerTick;
	private $clearChunksOnTick;
	/** @var \SplFixedArray<Block> */
	private $randomTickBlocks = null;

	/** @var LevelTimings */
	public $timings;

	private $tickRate;
	public $tickRateTime = 0;
	public $tickRateCounter = 0;

	/** @var Generator */
	private $generator;
	/** @var Generator */
	private $generatorInstance;

	private $closed = false;



	public static function chunkHash(int $x, int $z){
		return (($x & 0xFFFFFFFF) << 32) | ($z & 0xFFFFFFFF);
	}

	public static function blockHash(int $x, int $y, int $z){
		if($y < 0 or $y >= Level::Y_MAX){
			throw new \InvalidArgumentException("Y coordinate $y is out of range!");
		}
		return (($x & 0xFFFFFFF) << 36) | (($y & Level::Y_MASK) << 28) | ($z & 0xFFFFFFF);
	}

	public static function getBlockXYZ($hash, &$x, &$y, &$z){
		$x = $hash >> 36;
		$y = ($hash >> 28) & Level::Y_MASK; //it's always positive
		$z = ($hash & 0xFFFFFFF) << 36 >> 36;
	}

	/**
	 * @param string|int $hash
	 * @param int|null   $x
	 * @param int|null   $z
	 */
	public static function getXZ($hash, &$x, &$z){
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
	 * @param Server $server
	 * @param string $name
	 * @param string $path
	 * @param string $provider Class that extends LevelProvider
	 *
	 * @throws \Exception
	 */
	public function __construct(Server $server, string $name, string $path, string $provider){
		$this->blockStates = BlockFactory::getBlockStatesArray();
		$this->levelId = static::$levelIdCounter++;
		$this->blockMetadata = new BlockMetadataStore($this);
		$this->server = $server;
		$this->autoSave = $server->getAutoSave();

		/** @var LevelProvider $provider */

		if(is_subclass_of($provider, LevelProvider::class, true)){
			$this->provider = new $provider($this, $path);
		}else{
			throw new LevelException("Provider is not a subclass of LevelProvider");
		}
		$this->server->getLogger()->info($this->server->getLanguage()->translateString("pocketmine.level.preparing", [$this->provider->getName()]));
		$this->generator = Generator::getGenerator($this->provider->getGenerator());

		$this->folderName = $name;
		$this->scheduledBlockUpdateQueue = new ReversePriorityQueue();
		$this->scheduledBlockUpdateQueue->setExtractFlags(\SplPriorityQueue::EXTR_BOTH);

		$this->neighbourBlockUpdateQueue = new \SplQueue();

		$this->time = $this->provider->getTime();

		$this->chunkTickRadius = min($this->server->getViewDistance(), max(1, (int) $this->server->getProperty("chunk-ticking.tick-radius", 4)));
		$this->chunksPerTick = (int) $this->server->getProperty("chunk-ticking.per-tick", 40);
		$this->chunkGenerationQueueSize = (int) $this->server->getProperty("chunk-generation.queue-size", 8);
		$this->chunkPopulationQueueSize = (int) $this->server->getProperty("chunk-generation.population-queue-size", 2);
		$this->clearChunksOnTick = (bool) $this->server->getProperty("chunk-ticking.clear-tick-list", true);

		$dontTickBlocks = $this->server->getProperty("chunk-ticking.disable-block-ticking", []);
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
		$generator = $this->generator;
		$this->generatorInstance = new $generator($this->provider->getGeneratorOptions());
		$this->generatorInstance->init($this, new Random($this->getSeed()));

		$this->registerGenerator();
	}

	public function registerGenerator(){
		$size = $this->server->getScheduler()->getAsyncTaskPoolSize();
		for($i = 0; $i < $size; ++$i){
			$this->server->getScheduler()->scheduleAsyncTaskToWorker(new GeneratorRegisterTask($this, $this->generatorInstance), $i);
		}
	}

	public function unregisterGenerator(){
		$size = $this->server->getScheduler()->getAsyncTaskPoolSize();
		for($i = 0; $i < $size; ++$i){
			$this->server->getScheduler()->scheduleAsyncTaskToWorker(new GeneratorUnregisterTask($this), $i);
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

		if($this->getAutoSave()){
			$this->save();
		}

		foreach($this->chunks as $chunk){
			$this->unloadChunk($chunk->getX(), $chunk->getZ(), false);
		}

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
				$this->addChunkPacket($sound->x >> 4, $sound->z >> 4, $e);
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
				$this->addChunkPacket($particle->x >> 4, $particle->z >> 4, $e);
			}
		}else{
			$this->server->batchPackets($players, $pk, false);
		}
	}

	/**
	 * Broadcasts a LevelEvent to players in the area. This could be sound, particles, weather changes, etc.
	 *
	 * @param Vector3 $pos
	 * @param int $evid
	 * @param int $data
	 */
	public function broadcastLevelEvent(Vector3 $pos, int $evid, int $data = 0){
		$pk = new LevelEventPacket();
		$pk->evid = $evid;
		$pk->data = $data;
		$pk->position = $pos->asVector3();
		$this->addChunkPacket($pos->x >> 4, $pos->z >> 4, $pk);
	}

	/**
	 * Broadcasts a LevelSoundEvent to players in the area.
	 *
	 * @param Vector3 $pos
	 * @param int $soundId
	 * @param int $pitch
	 * @param int $extraData
	 * @param bool $unknown
	 * @param bool $disableRelativeVolume If true, all players receiving this sound-event will hear the sound at full volume regardless of distance
	 */
	public function broadcastLevelSoundEvent(Vector3 $pos, int $soundId, int $pitch = 1, int $extraData = -1, bool $unknown = false, bool $disableRelativeVolume = false){
		$pk = new LevelSoundEventPacket();
		$pk->sound = $soundId;
		$pk->pitch = $pitch;
		$pk->extraData = $extraData;
		$pk->unknownBool = $unknown;
		$pk->disableRelativeVolume = $disableRelativeVolume;
		$pk->position = $pos->asVector3();
		$this->addChunkPacket($pos->x >> 4, $pos->z >> 4, $pk);
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

		if($this === $this->server->getDefaultLevel() and $force !== true){
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

	public function addChunkPacket(int $chunkX, int $chunkZ, DataPacket $packet){
		if(!isset($this->chunkPackets[$index = Level::chunkHash($chunkX, $chunkZ)])){
			$this->chunkPackets[$index] = [$packet];
		}else{
			$this->chunkPackets[$index][] = $packet;
		}
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
		if($this->stopTime === true){
			return;
		}else{
			$this->time += 1;
		}
	}

	/**
	 * WARNING: Do not use this, it's only for internal use.
	 * Changes to this function won't be recorded on the version.
	 *
	 * @param Player[] ...$targets If empty, will send to all players in the level.
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

		if(++$this->sendTimeTicker === 200){
			$this->sendTime();
			$this->sendTimeTicker = 0;
		}

		$this->unloadChunks();

		//Do block updates
		$this->timings->doTickPending->startTiming();

		//Delayed updates
		while($this->scheduledBlockUpdateQueue->count() > 0 and $this->scheduledBlockUpdateQueue->current()["priority"] <= $currentTick){
			$block = $this->getBlock($this->scheduledBlockUpdateQueue->extract()["data"]);
			unset($this->scheduledBlockUpdateQueueIndex[Level::blockHash($block->x, $block->y, $block->z)]);
			$block->onUpdate(self::BLOCK_UPDATE_SCHEDULED);
		}

		//Normal updates
		while($this->neighbourBlockUpdateQueue->count() > 0){
			$index = $this->neighbourBlockUpdateQueue->dequeue();
			Level::getBlockXYZ($index, $x, $y, $z);
			$this->server->getPluginManager()->callEvent($ev = new BlockUpdateEvent($this->getBlock($this->temporalVector->setComponents($x, $y, $z))));
			if(!$ev->isCancelled()){
				$ev->getBlock()->onUpdate(self::BLOCK_UPDATE_NORMAL);
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
		if(count($this->updateTiles) > 0){
			foreach($this->updateTiles as $id => $tile){
				if($tile->onUpdate() !== true){
					unset($this->updateTiles[$id]);
				}
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
					}else{
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

	public function sendBlockExtraData(int $x, int $y, int $z, int $id, int $data, array $targets = null){
		$pk = new LevelEventPacket;
		$pk->evid = LevelEventPacket::EVENT_SET_DATA;
		$pk->position = new Vector3($x, $y, $z);
		$pk->data = ($data << 8) | $id;

		$this->server->broadcastPacket($targets ?? $this->getChunkPlayers($x >> 4, $z >> 4), $pk);
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
				$pk = new UpdateBlockPacket();
				if($b === null){
					continue;
				}

				$first = false;
				if(!isset($chunks[$index = Level::chunkHash($b->x >> 4, $b->z >> 4)])){
					$chunks[$index] = true;
					$first = true;
				}

				$pk->x = $b->x;
				$pk->y = $b->y;
				$pk->z = $b->z;

				if($b instanceof Block){
					$pk->blockId = $b->getId();
					$pk->blockData = $b->getDamage();
				}else{
					$fullBlock = $this->getFullBlock($b->x, $b->y, $b->z);
					$pk->blockId = $fullBlock >> 4;
					$pk->blockData = $fullBlock & 0xf;
				}

				$pk->flags = $first ? $flags : UpdateBlockPacket::FLAG_NONE;

				$packets[] = $pk;
			}
		}else{
			foreach($blocks as $b){
				$pk = new UpdateBlockPacket();
				if($b === null){
					continue;
				}

				$pk->x = $b->x;
				$pk->y = $b->y;
				$pk->z = $b->z;

				if($b instanceof Block){
					$pk->blockId = $b->getId();
					$pk->blockData = $b->getDamage();
				}else{
					$fullBlock = $this->getFullBlock($b->x, $b->y, $b->z);
					$pk->blockId = $fullBlock >> 4;
					$pk->blockData = $fullBlock & 0xf;
				}

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
			if(count($this->blockCache) > 2048){
				$this->blockCache = [];
			}
		}
	}

	public function clearChunkCache(int $chunkX, int $chunkZ){
		unset($this->chunkCache[Level::chunkHash($chunkX, $chunkZ)]);
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
			$chunkX = $loader->getX() >> 4;
			$chunkZ = $loader->getZ() >> 4;

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


			if(!isset($this->chunks[$index]) or ($chunk = $this->getChunk($chunkX, $chunkZ, false)) === null){
				unset($this->chunkTickList[$index]);
				continue;
			}elseif($loaders <= 0){
				unset($this->chunkTickList[$index]);
			}

			foreach($chunk->getEntities() as $entity){
				$entity->scheduleUpdate();
			}


			foreach($chunk->getSubChunks() as $Y => $subChunk){
				if(!$subChunk->isEmpty()){
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
							$block->onUpdate(self::BLOCK_UPDATE_RANDOM);
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
			if($chunk->hasChanged() and $chunk->isGenerated()){
				$this->provider->setChunk($chunk->getX(), $chunk->getZ(), $chunk);
				$this->provider->saveChunk($chunk->getX(), $chunk->getZ());
				$chunk->setChanged(false);
			}
		}
	}

	/**
	 * @param Vector3 $pos
	 */
	public function updateAround(Vector3 $pos){
		$pos = $pos->floor();
		$this->server->getPluginManager()->callEvent($ev = new BlockUpdateEvent($this->getBlock($this->temporalVector->setComponents($pos->x, $pos->y - 1, $pos->z))));
		if(!$ev->isCancelled()){
			$ev->getBlock()->onUpdate(self::BLOCK_UPDATE_NORMAL);
		}

		$this->server->getPluginManager()->callEvent($ev = new BlockUpdateEvent($this->getBlock($this->temporalVector->setComponents($pos->x, $pos->y + 1, $pos->z))));
		if(!$ev->isCancelled()){
			$ev->getBlock()->onUpdate(self::BLOCK_UPDATE_NORMAL);
		}

		$this->server->getPluginManager()->callEvent($ev = new BlockUpdateEvent($this->getBlock($this->temporalVector->setComponents($pos->x - 1, $pos->y, $pos->z))));
		if(!$ev->isCancelled()){
			$ev->getBlock()->onUpdate(self::BLOCK_UPDATE_NORMAL);
		}

		$this->server->getPluginManager()->callEvent($ev = new BlockUpdateEvent($this->getBlock($this->temporalVector->setComponents($pos->x + 1, $pos->y, $pos->z))));
		if(!$ev->isCancelled()){
			$ev->getBlock()->onUpdate(self::BLOCK_UPDATE_NORMAL);
		}

		$this->server->getPluginManager()->callEvent($ev = new BlockUpdateEvent($this->getBlock($this->temporalVector->setComponents($pos->x, $pos->y, $pos->z - 1))));
		if(!$ev->isCancelled()){
			$ev->getBlock()->onUpdate(self::BLOCK_UPDATE_NORMAL);
		}

		$this->server->getPluginManager()->callEvent($ev = new BlockUpdateEvent($this->getBlock($this->temporalVector->setComponents($pos->x, $pos->y, $pos->z + 1))));
		if(!$ev->isCancelled()){
			$ev->getBlock()->onUpdate(self::BLOCK_UPDATE_NORMAL);
		}
	}

	/**
	 * @deprecated This method will be removed in the future due to misleading/ambiguous name. Use {@link Level#scheduleDelayedBlockUpdate} instead.
	 *
	 * @param Vector3 $pos
	 * @param int     $delay
	 */
	public function scheduleUpdate(Vector3 $pos, int $delay){
		$this->scheduleDelayedBlockUpdate($pos, $delay);
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
		$bbPlusOne = $bb->grow(1, 1, 1);
		$minX = Math::floorFloat($bbPlusOne->minX);
		$minY = Math::floorFloat($bbPlusOne->minY);
		$minZ = Math::floorFloat($bbPlusOne->minZ);
		$maxX = Math::ceilFloat($bbPlusOne->maxX);
		$maxY = Math::ceilFloat($bbPlusOne->maxY);
		$maxZ = Math::ceilFloat($bbPlusOne->maxZ);

		$collides = [];

		if($targetFirst){
			for($z = $minZ; $z <= $maxZ; ++$z){
				for($x = $minX; $x <= $maxX; ++$x){
					for($y = $minY; $y <= $maxY; ++$y){
						$block = $this->getBlock($this->temporalVector->setComponents($x, $y, $z));
						if($block->getId() !== 0 and $block->collidesWithBB($bb)){
							return [$block];
						}
					}
				}
			}
		}else{
			for($z = $minZ; $z <= $maxZ; ++$z){
				for($x = $minX; $x <= $maxX; ++$x){
					for($y = $minY; $y <= $maxY; ++$y){
						$block = $this->getBlock($this->temporalVector->setComponents($x, $y, $z));
						if($block->getId() !== 0 and $block->collidesWithBB($bb)){
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
		$bbPlusOne = $bb->grow(1, 1, 1);
		$minX = Math::floorFloat($bbPlusOne->minX);
		$minY = Math::floorFloat($bbPlusOne->minY);
		$minZ = Math::floorFloat($bbPlusOne->minZ);
		$maxX = Math::ceilFloat($bbPlusOne->maxX);
		$maxY = Math::ceilFloat($bbPlusOne->maxY);
		$maxZ = Math::ceilFloat($bbPlusOne->maxZ);

		$collides = [];

		for($z = $minZ; $z <= $maxZ; ++$z){
			for($x = $minX; $x <= $maxX; ++$x){
				for($y = $minY; $y <= $maxY; ++$y){
					$block = $this->getBlock($this->temporalVector->setComponents($x, $y, $z));
					if(!$block->canPassThrough() and $block->collidesWithBB($bb)){
						$collides[] = $block->getBoundingBox();
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

	/*
	public function rayTraceBlocks(Vector3 $pos1, Vector3 $pos2, $flag = false, $flag1 = false, $flag2 = false){
		if(!is_nan($pos1->x) and !is_nan($pos1->y) and !is_nan($pos1->z)){
			if(!is_nan($pos2->x) and !is_nan($pos2->y) and !is_nan($pos2->z)){
				$x1 = (int) $pos1->x;
				$y1 = (int) $pos1->y;
				$z1 = (int) $pos1->z;
				$x2 = (int) $pos2->x;
				$y2 = (int) $pos2->y;
				$z2 = (int) $pos2->z;

				$block = $this->getBlock(Vector3::createVector($x1, $y1, $z1));

				if(!$flag1 or $block->getBoundingBox() !== null){
					$ob = $block->calculateIntercept($pos1, $pos2);
					if($ob !== null){
						return $ob;
					}
				}

				$movingObjectPosition = null;

				$k = 200;

				while($k-- >= 0){
					if(is_nan($pos1->x) or is_nan($pos1->y) or is_nan($pos1->z)){
						return null;
					}

					if($x1 === $x2 and $y1 === $y2 and $z1 === $z2){
						return $flag2 ? $movingObjectPosition : null;
					}

					$flag3 = true;
					$flag4 = true;
					$flag5 = true;

					$i = 999;
					$j = 999;
					$k = 999;

					if($x1 > $x2){
						$i = $x2 + 1;
					}elseif($x1 < $x2){
						$i = $x2;
					}else{
						$flag3 = false;
					}

					if($y1 > $y2){
						$j = $y2 + 1;
					}elseif($y1 < $y2){
						$j = $y2;
					}else{
						$flag4 = false;
					}

					if($z1 > $z2){
						$k = $z2 + 1;
					}elseif($z1 < $z2){
						$k = $z2;
					}else{
						$flag5 = false;
					}

					//TODO
				}
			}
		}
	}
	*/

	public function getFullLight(Vector3 $pos) : int{
		return $this->getFullLightAt($pos->x, $pos->y, $pos->z);
	}

	public function getFullLightAt(int $x, int $y, int $z) : int{
		//TODO: decrease light level by time of day
		$skyLight = $this->getBlockSkyLightAt($x, $y, $z);
		if($skyLight < 15){
			return max($skyLight, $this->getBlockLightAt($x, $y, $z));
		}else{
			return $skyLight;
		}
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

	public function isInWorld(float $x, float $y, float $z) : bool{
		return (
			$x <= INT32_MAX and $x >= INT32_MIN and
			$y < $this->getWorldHeight() and $y >= 0 and
			$z <= INT32_MAX and $z >= INT32_MIN
		);
	}

	/**
	 * Gets the Block object at the Vector3 location
	 *
	 * Note for plugin developers: If you are using this method a lot (thousands of times for many positions for
	 * example), you may want to set addToCache to false to avoid using excessive amounts of memory.
	 *
	 * @param Vector3 $pos
	 * @param bool    $cached Whether to use the block cache for getting the block (faster, but may be inaccurate)
	 * @param bool    $addToCache Whether to cache the block object created by this method call.
	 *
	 * @return Block
	 */
	public function getBlock(Vector3 $pos, bool $cached = true, bool $addToCache = true) : Block{
		$pos = $pos->floor();

		$fullState = 0;
		$index = null;

		if($this->isInWorld($pos->x, $pos->y, $pos->z)){
			$index = Level::blockHash($pos->x, $pos->y, $pos->z);
			if($cached and isset($this->blockCache[$index])){
				return $this->blockCache[$index];
			}elseif(isset($this->chunks[$chunkIndex = Level::chunkHash($pos->x >> 4, $pos->z >> 4)])){
				$fullState = $this->chunks[$chunkIndex]->getFullBlock($pos->x & 0x0f, $pos->y, $pos->z & 0x0f);
			}
		}

		$block = clone $this->blockStates[$fullState & 0xfff];

		$block->x = $pos->x;
		$block->y = $pos->y;
		$block->z = $pos->z;
		$block->level = $this;

		if($addToCache and $index !== null){
			$this->blockCache[$index] = $block;
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

			$block->position($pos);
			unset($this->blockCache[Level::blockHash($pos->x, $pos->y, $pos->z)]);

			$index = Level::chunkHash($pos->x >> 4, $pos->z >> 4);

			if($direct === true){
				$this->sendBlocks($this->getChunkPlayers($pos->x >> 4, $pos->z >> 4), [$block], UpdateBlockPacket::FLAG_ALL_PRIORITY);
				unset($this->chunkCache[$index]);
			}else{
				if(!isset($this->changedBlocks[$index])){
					$this->changedBlocks[$index] = [];
				}

				$this->changedBlocks[$index][Level::blockHash($block->x, $block->y, $block->z)] = clone $block;
			}

			foreach($this->getChunkLoaders($pos->x >> 4, $pos->z >> 4) as $loader){
				$loader->onBlockChanged($block);
			}

			if($update === true){
				$this->updateAllLight($block);

				$this->server->getPluginManager()->callEvent($ev = new BlockUpdateEvent($block));
				if(!$ev->isCancelled()){
					foreach($this->getNearbyEntities(new AxisAlignedBB($block->x - 1, $block->y - 1, $block->z - 1, $block->x + 2, $block->y + 2, $block->z + 2)) as $entity){
						$entity->setForceMovementUpdate();
						$entity->scheduleUpdate();
					}
					$ev->getBlock()->onUpdate(self::BLOCK_UPDATE_NORMAL);
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
	 * @return DroppedItem|null
	 */
	public function dropItem(Vector3 $source, Item $item, Vector3 $motion = null, int $delay = 10){
		$motion = $motion ?? new Vector3(lcg_value() * 0.2 - 0.1, 0.2, lcg_value() * 0.2 - 0.1);
		$itemTag = $item->nbtSerialize();
		$itemTag->setName("Item");

		if(!$item->isNull()){
			$itemEntity = Entity::createEntity("Item", $this, new CompoundTag("", [
				new ListTag("Pos", [
					new DoubleTag("", $source->getX()),
					new DoubleTag("", $source->getY()),
					new DoubleTag("", $source->getZ())
				]),
				new ListTag("Motion", [
					new DoubleTag("", $motion->x),
					new DoubleTag("", $motion->y),
					new DoubleTag("", $motion->z)
				]),
				new ListTag("Rotation", [
					new FloatTag("", lcg_value() * 360),
					new FloatTag("", 0)
				]),
				new ShortTag("Health", 5),
				$itemTag,
				new ShortTag("PickupDelay", $delay)
			]));

			$itemEntity->spawnToAll();
			return $itemEntity;
		}
		return null;
	}

	/**
	 * Checks if the level spawn protection radius will prevent the player from using items or building at the specified
	 * Vector3 position.
	 *
	 * @param Player  $player
	 * @param Vector3 $vector
	 *
	 * @return bool false if spawn protection cancelled the action, true if not.
	 */
	protected function checkSpawnProtection(Player $player, Vector3 $vector) : bool{
		if(!$player->hasPermission("pocketmine.spawnprotect.bypass") and ($distance = $this->server->getSpawnRadius()) > -1){
			$t = new Vector2($vector->x, $vector->z);
			$s = new Vector2($this->getSpawnLocation()->x, $this->getSpawnLocation()->z);
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

		if($item === null){
			$item = ItemFactory::get(Item::AIR, 0, 0);
		}

		if($player !== null){
			$ev = new BlockBreakEvent($player, $target, $item, $player->isCreative() or $player->allowInstaBreak());

			if(($player->isSurvival() and $item instanceof Item and !$target->isBreakable($item)) or $player->isSpectator()){
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
							if($entry->getId() > 0 and $entry->getBlock() !== null and $entry->getBlock()->getId() === $target->getId()){
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

			$breakTime = ceil($target->getBreakTime($item) * 20);

			if($player->isCreative() and $breakTime > 3){
				$breakTime = 3;
			}

			if($player->hasEffect(Effect::HASTE)){
				$breakTime *= 1 - (0.2 * $player->getEffect(Effect::HASTE)->getEffectLevel());
			}

			if($player->hasEffect(Effect::MINING_FATIGUE)){
				$breakTime *= 1 + (0.3 * $player->getEffect(Effect::MINING_FATIGUE)->getEffectLevel());
			}

			$breakTime -= 1; //1 tick compensation

			if(!$ev->getInstaBreak() and (ceil($player->lastBreak * 20) + $breakTime) > ceil(microtime(true) * 20)){
				return false;
			}

			$player->lastBreak = PHP_INT_MAX;

			$drops = $ev->getDrops();

		}elseif($item !== null and !$target->isBreakable($item)){
			return false;
		}else{
			$drops = $target->getDrops($item); //Fixes tile entities being deleted before getting drops
		}

		$above = $this->getBlock(new Vector3($target->x, $target->y + 1, $target->z));
		if($above->getId() === Item::FIRE){
			$this->setBlock($above, BlockFactory::get(Block::AIR), true);
		}

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

				foreach($tile->getInventory()->getContents() as $chestItem){
					$this->dropItem($target, $chestItem);
				}
			}

			$tile->close();
		}

		if($item !== null){
			$item->useOn($target);
			if($item->isTool() and $item->getDamage() >= $item->getMaxDurability()){
				$item = ItemFactory::get(Item::AIR, 0, 0);
			}
		}

		if($player === null or $player->isSurvival()){
			foreach($drops as $drop){
				if(!$drop->isNull()){
					$this->dropItem($vector->add(0.5, 0.5, 0.5), $drop);
				}
			}
		}

		return true;
	}

	/**
	 * Uses a item on a position and face, placing it or activating the block
	 *
	 * @param Vector3      $vector
	 * @param Item         $item
	 * @param int          $face
	 * @param Vector3|null $facePos
	 * @param Player|null  $player default null
	 * @param bool         $playSound Whether to play a block-place sound if the block was placed successfully.
	 *
	 * @return bool
	 */
	public function useItemOn(Vector3 $vector, Item &$item, int $face, Vector3 $facePos = null, Player $player = null, bool $playSound = false) : bool{
		$blockClicked = $this->getBlock($vector);
		$blockReplace = $blockClicked->getSide($face);

		if($facePos === null){
			$facePos = new Vector3(0.0, 0.0, 0.0);
		}

		if($blockReplace->y >= $this->provider->getWorldHeight() or $blockReplace->y < 0){
			//TODO: build height limit messages for custom world heights and mcregion cap
			return false;
		}

		if($blockClicked->getId() === Item::AIR){
			return false;
		}

		if($player !== null){
			$ev = new PlayerInteractEvent($player, $item, $blockClicked, $face, $blockClicked->getId() === 0 ? PlayerInteractEvent::RIGHT_CLICK_AIR : PlayerInteractEvent::RIGHT_CLICK_BLOCK);
			if($this->checkSpawnProtection($player, $blockClicked)){
				$ev->setCancelled(); //set it to cancelled so plugins can bypass this
			}

			if($player->isAdventure(true) and !$ev->isCancelled()){
				$canPlace = false;
				$tag = $item->getNamedTagEntry("CanPlaceOn");
				if($tag instanceof ListTag){
					foreach($tag as $v){
						if($v instanceof StringTag){
							$entry = ItemFactory::fromString($v->getValue());
							if($entry->getId() > 0 and $entry->getBlock() !== null and $entry->getBlock()->getId() === $blockClicked->getId()){
								$canPlace = true;
								break;
							}
						}
					}
				}

				$ev->setCancelled(!$canPlace);
			}

			$this->server->getPluginManager()->callEvent($ev);
			if(!$ev->isCancelled()){
				$blockClicked->onUpdate(self::BLOCK_UPDATE_TOUCH);
				if(!$player->isSneaking() and $blockClicked->onActivate($item, $player) === true){
					return true;
				}

				if(!$player->isSneaking() and $item->onActivate($this, $player, $blockReplace, $blockClicked, $face, $facePos)){
					if($item->getCount() <= 0){
						$item = ItemFactory::get(Item::AIR, 0, 0);

						return true;
					}
				}
			}else{
				return false;
			}
		}elseif($blockClicked->onActivate($item, $player) === true){
			return true;
		}

		if($item->canBePlaced()){
			$hand = $item->getBlock();
			$hand->position($blockReplace);
		}else{
			return false;
		}

		if($hand->canBePlacedAt($blockClicked, $facePos)){
			$blockReplace = $blockClicked;
			$hand->position($blockReplace);
		}elseif(!$hand->canBePlacedAt($blockReplace, $facePos)){
			return false;
		}

		if($hand->isSolid() === true and $hand->getBoundingBox() !== null){
			$entities = $this->getCollidingEntities($hand->getBoundingBox());
			foreach($entities as $e){
				if($e instanceof Arrow or $e instanceof DroppedItem or ($e instanceof Player and $e->isSpectator())){
					continue;
				}

				return false; //Entity in block
			}

			if($player !== null){
				if(($diff = $player->getNextPosition()->subtract($player->getPosition())) and $diff->lengthSquared() > 0.00001){
					$bb = $player->getBoundingBox()->getOffsetBoundingBox($diff->x, $diff->y, $diff->z);
					if($hand->getBoundingBox()->intersectsWith($bb)){
						return false; //Inside player BB
					}
				}
			}
		}


		if($player !== null){
			$ev = new BlockPlaceEvent($player, $hand, $blockReplace, $blockClicked, $item);
			if($this->checkSpawnProtection($player, $blockClicked)){
				$ev->setCancelled();
			}

			$this->server->getPluginManager()->callEvent($ev);
			if($ev->isCancelled()){
				return false;
			}
		}

		if(!$hand->place($item, $blockReplace, $blockClicked, $face, $facePos, $player)){
			return false;
		}

		if($playSound){
			$this->broadcastLevelSoundEvent($hand, LevelSoundEventPacket::SOUND_PLACE, 1, $hand->getId());
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
			$minX = Math::floorFloat(($bb->minX - 2) / 16);
			$maxX = Math::ceilFloat(($bb->maxX + 2) / 16);
			$minZ = Math::floorFloat(($bb->minZ - 2) / 16);
			$maxZ = Math::ceilFloat(($bb->maxZ + 2) / 16);

			for($x = $minX; $x <= $maxX; ++$x){
				for($z = $minZ; $z <= $maxZ; ++$z){
					foreach($this->getChunkEntities($x, $z) as $ent){
						/** @var Entity|null $entity */
						if(($entity === null or ($ent !== $entity and $entity->canCollideWith($ent))) and $ent->boundingBox->intersectsWith($bb)){
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

		$minX = Math::floorFloat(($bb->minX - 2) / 16);
		$maxX = Math::ceilFloat(($bb->maxX + 2) / 16);
		$minZ = Math::floorFloat(($bb->minZ - 2) / 16);
		$maxZ = Math::ceilFloat(($bb->maxZ + 2) / 16);

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
	 * Returns the Tile in a position, or null if not found
	 *
	 * @param Vector3 $pos
	 *
	 * @return Tile|null
	 */
	public function getTile(Vector3 $pos){
		$chunk = $this->getChunk($pos->x >> 4, $pos->z >> 4, false);

		if($chunk !== null){
			return $chunk->getTile($pos->x & 0x0f, $pos->y, $pos->z & 0x0f);
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
		unset($this->blockCache[Level::blockHash($x, $y, $z)]);
		$this->getChunk($x >> 4, $z >> 4, true)->setBlockId($x & 0x0f, $y, $z & 0x0f, $id & 0xff);

		if(!isset($this->changedBlocks[$index = Level::chunkHash($x >> 4, $z >> 4)])){
			$this->changedBlocks[$index] = [];
		}
		$this->changedBlocks[$index][Level::blockHash($x, $y, $z)] = $v = new Vector3($x, $y, $z);
		foreach($this->getChunkLoaders($x >> 4, $z >> 4) as $loader){
			$loader->onBlockChanged($v);
		}
	}

	/**
	 * Gets the raw block extra data
	 *
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 *
	 * @return int 16-bit
	 */
	public function getBlockExtraDataAt(int $x, int $y, int $z) : int{
		return $this->getChunk($x >> 4, $z >> 4, true)->getBlockExtraData($x & 0x0f, $y, $z & 0x0f);
	}

	/**
	 * Sets the raw block metadata.
	 *
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 * @param int $id
	 * @param int $data
	 */
	public function setBlockExtraDataAt(int $x, int $y, int $z, int $id, int $data){
		$this->getChunk($x >> 4, $z >> 4, true)->setBlockExtraData($x & 0x0f, $y, $z & 0x0f, ($data << 8) | $id);

		$this->sendBlockExtraData($x, $y, $z, $id, $data);
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
		unset($this->blockCache[Level::blockHash($x, $y, $z)]);
		$this->getChunk($x >> 4, $z >> 4, true)->setBlockData($x & 0x0f, $y, $z & 0x0f, $data & 0x0f);

		if(!isset($this->changedBlocks[$index = Level::chunkHash($x >> 4, $z >> 4)])){
			$this->changedBlocks[$index] = [];
		}
		$this->changedBlocks[$index][Level::blockHash($x, $y, $z)] = $v = new Vector3($x, $y, $z);
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
	 * @return int
	 */
	public function getHeightMap(int $x, int $z) : int{
		return $this->getChunk($x >> 4, $z >> 4, true)->getHeightMap($x & 0x0f, $z & 0x0f);
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
	 * Gets the Chunk object
	 *
	 * @param int  $x
	 * @param int  $z
	 * @param bool $create Whether to generate the chunk if it does not exist
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
			$chunk = $this->getChunk($x, $z, false);
			if($chunk !== null and ($oldChunk === null or $oldChunk->isPopulated() === false) and $chunk->isPopulated()){
				$this->server->getPluginManager()->callEvent(new ChunkPopulateEvent($this, $chunk));

				foreach($this->getChunkLoaders($x, $z) as $loader){
					$loader->onChunkPopulated($chunk);
				}
			}
		}elseif(isset($this->chunkGenerationQueue[$index]) or isset($this->chunkPopulationLock[$index])){
			unset($this->chunkGenerationQueue[$index]);
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
		$index = Level::chunkHash($chunkX, $chunkZ);
		$oldChunk = $this->getChunk($chunkX, $chunkZ, false);
		if($unload and $oldChunk !== null){
			$this->unloadChunk($chunkX, $chunkZ, false, false);

			$this->provider->setChunk($chunkX, $chunkZ, $chunk);
			$this->chunks[$index] = $chunk;
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
				$tile->chunk = $chunk;
			}

			$this->provider->setChunk($chunkX, $chunkZ, $chunk);
			$this->chunks[$index] = $chunk;
		}

		unset($this->chunkCache[$index]);
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
		return isset($this->chunks[Level::chunkHash($x, $z)]) or $this->provider->isChunkLoaded($x, $z);
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

				$task = $this->provider->requestChunkTask($x, $z);
				$this->server->getScheduler()->scheduleAsyncTask($task);

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
		}else{
			$entity->close();
		}

		unset($this->entities[$entity->getId()]);
		unset($this->updateEntities[$entity->getId()]);
	}

	/**
	 * @param Entity $entity
	 *
	 * @throws LevelException
	 */
	public function addEntity(Entity $entity){
		if($entity->getLevel() !== $this){
			throw new LevelException("Invalid Entity level");
		}
		if($entity instanceof Player){
			$this->players[$entity->getId()] = $entity;
		}
		$this->entities[$entity->getId()] = $entity;
	}

	/**
	 * @param Tile $tile
	 *
	 * @throws LevelException
	 */
	public function addTile(Tile $tile){
		if($tile->getLevel() !== $this){
			throw new LevelException("Invalid Tile level");
		}
		$this->tiles[$tile->getId()] = $tile;
		$this->clearChunkCache($tile->getX() >> 4, $tile->getZ() >> 4);
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

		unset($this->tiles[$tile->getId()]);
		unset($this->updateTiles[$tile->getId()]);
		$this->clearChunkCache($tile->getX() >> 4, $tile->getZ() >> 4);
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
	 * @param int  $x
	 * @param int  $z
	 * @param bool $generate
	 *
	 * @return bool
	 *
	 * @throws \InvalidStateException
	 */
	public function loadChunk(int $x, int $z, bool $generate = true) : bool{
		if(isset($this->chunks[$index = Level::chunkHash($x, $z)])){
			return true;
		}

		$this->timings->syncChunkLoadTimer->startTiming();

		$this->cancelUnloadChunkRequest($x, $z);

		$chunk = $this->provider->getChunk($x, $z, $generate);
		if($chunk === null){
			if($generate){
				throw new \InvalidStateException("Could not create new Chunk");
			}
			return false;
		}

		$this->chunks[$index] = $chunk;
		$chunk->initChunk($this);

		$this->server->getPluginManager()->callEvent(new ChunkLoadEvent($this, $chunk, !$chunk->isGenerated()));

		if(!$chunk->isLightPopulated() and $chunk->isPopulated() and $this->getServer()->getProperty("chunk-ticking.light-updates", false)){
			$this->getServer()->getScheduler()->scheduleAsyncTask(new LightPopulationTask($this, $chunk));
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
		if(($safe === true and $this->isChunkInUse($x, $z)) or $this->isSpawnChunk($x, $z)){
			return false;
		}

		$this->queueUnloadChunk($x, $z);

		return true;
	}

	public function cancelUnloadChunkRequest(int $x, int $z){
		unset($this->unloadQueue[Level::chunkHash($x, $z)]);
	}

	public function unloadChunk(int $x, int $z, bool $safe = true, bool $trySave = true) : bool{
		if($safe === true and $this->isChunkInUse($x, $z)){
			return false;
		}

		if(!$this->isChunkLoaded($x, $z)){
			return true;
		}

		$this->timings->doChunkUnload->startTiming();

		$index = Level::chunkHash($x, $z);

		$chunk = $this->chunks[$index] ?? null;

		if($chunk !== null){
			$this->server->getPluginManager()->callEvent($ev = new ChunkUnloadEvent($this, $chunk));
			if($ev->isCancelled()){
				$this->timings->doChunkUnload->stopTiming();
				return false;
			}
		}

		try{
			if($chunk !== null){
				if($trySave and $this->getAutoSave() and $chunk->isGenerated()){
					$entities = 0;
					foreach($chunk->getEntities() as $e){
						if($e instanceof Player){
							continue;
						}
						++$entities;
					}

					if($chunk->hasChanged() or count($chunk->getTiles()) > 0 or $entities > 0){
						$this->provider->setChunk($x, $z, $chunk);
						$this->provider->saveChunk($x, $z);
					}
				}

				foreach($this->getChunkLoaders($x, $z) as $loader){
					$loader->onChunkUnloaded($chunk);
				}
			}
			$this->provider->unloadChunk($x, $z, $safe);
		}catch(\Throwable $e){
			$logger = $this->server->getLogger();
			$logger->error($this->server->getLanguage()->translateString("pocketmine.level.chunkUnloadError", [$e->getMessage()]));
			$logger->logException($e);
		}

		unset($this->chunks[$index]);
		unset($this->chunkTickList[$index]);
		unset($this->chunkCache[$index]);

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
		$spawnX = $this->provider->getSpawn()->getX() >> 4;
		$spawnZ = $this->provider->getSpawn()->getZ() >> 4;

		return abs($X - $spawnX) <= 1 and abs($Z - $spawnZ) <= 1;
	}

	/**
	 * @param Vector3 $spawn default null
	 *
	 * @return bool|Position
	 */
	public function getSafeSpawn($spawn = null){
		if(!($spawn instanceof Vector3) or $spawn->y < 1){
			$spawn = $this->getSpawnLocation();
		}
		if($spawn instanceof Vector3){
			$max = $this->provider->getWorldHeight();
			$v = $spawn->floor();
			$chunk = $this->getChunk($v->x >> 4, $v->z >> 4, false);
			$x = $v->x & 0x0f;
			$z = $v->z & 0x0f;
			if($chunk !== null){
				$y = (int) min($max - 2, $v->y);
				$wasAir = ($chunk->getBlockId($x, $y - 1, $z) === 0);
				for(; $y > 0; --$y){
					$b = $chunk->getFullBlock($x, $y, $z);
					$block = BlockFactory::get($b >> 4, $b & 0x0f);
					if($this->isFullBlock($block)){
						if($wasAir){
							$y++;
							break;
						}
					}else{
						$wasAir = true;
					}
				}

				for(; $y >= 0 and $y < $max; ++$y){
					$b = $chunk->getFullBlock($x, $y + 1, $z);
					$block = BlockFactory::get($b >> 4, $b & 0x0f);
					if(!$this->isFullBlock($block)){
						$b = $chunk->getFullBlock($x, $y, $z);
						$block = BlockFactory::get($b >> 4, $b & 0x0f);
						if(!$this->isFullBlock($block)){
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

		return false;
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
		return $this->provider->getName();
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
		return $this->provider->getWorldHeight();
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
	 * @param Player[] ...$targets
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
					$this->server->getScheduler()->scheduleAsyncTask($task);
				}
			}

			Timings::$populationTimer->stopTiming();
			return false;
		}

		return true;
	}

	public function generateChunk(int $x, int $z, bool $force = false){
		if(count($this->chunkGenerationQueue) >= $this->chunkGenerationQueueSize and !$force){
			return;
		}

		if(!isset($this->chunkGenerationQueue[$index = Level::chunkHash($x, $z)])){
			Timings::$generationTimer->startTiming();
			$this->chunkGenerationQueue[$index] = true;
			$task = new GenerationTask($this, $this->getChunk($x, $z, true));
			$this->server->getScheduler()->scheduleAsyncTask($task);
			Timings::$generationTimer->stopTiming();
		}
	}

	public function regenerateChunk(int $x, int $z){
		$this->unloadChunk($x, $z, false);

		$this->cancelUnloadChunkRequest($x, $z);

		$this->generateChunk($x, $z);
		//TODO: generate & refresh chunk from the generator object
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

		foreach($this->provider->getLoadedChunks() as $chunk){
			if(!isset($this->chunks[Level::chunkHash($chunk->getX(), $chunk->getZ())])){
				$this->provider->unloadChunk($chunk->getX(), $chunk->getZ(), false);
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
