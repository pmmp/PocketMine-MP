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
 * All World related classes are here, like Generators, Populators, Noise, ...
 */
namespace pocketmine\world;

use pocketmine\block\Air;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\tile\Spawnable;
use pocketmine\block\tile\Tile;
use pocketmine\block\UnknownBlock;
use pocketmine\entity\Entity;
use pocketmine\entity\Location;
use pocketmine\entity\object\ExperienceOrb;
use pocketmine\entity\object\ItemEntity;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockUpdateEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\world\ChunkLoadEvent;
use pocketmine\event\world\ChunkPopulateEvent;
use pocketmine\event\world\ChunkUnloadEvent;
use pocketmine\event\world\SpawnChangeEvent;
use pocketmine\event\world\WorldSaveEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemUseResult;
use pocketmine\item\LegacyStringToItemParser;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\convert\RuntimeBlockMapping;
use pocketmine\network\mcpe\protocol\BlockActorDataPacket;
use pocketmine\network\mcpe\protocol\ClientboundPacket;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\timings\Timings;
use pocketmine\utils\Limits;
use pocketmine\utils\ReversePriorityQueue;
use pocketmine\world\biome\Biome;
use pocketmine\world\format\Chunk;
use pocketmine\world\format\io\exception\CorruptedChunkException;
use pocketmine\world\format\io\WritableWorldProvider;
use pocketmine\world\generator\Generator;
use pocketmine\world\generator\GeneratorManager;
use pocketmine\world\generator\GeneratorRegisterTask;
use pocketmine\world\generator\GeneratorUnregisterTask;
use pocketmine\world\generator\PopulationTask;
use pocketmine\world\light\BlockLightUpdate;
use pocketmine\world\light\LightPopulationTask;
use pocketmine\world\light\SkyLightUpdate;
use pocketmine\world\particle\DestroyBlockParticle;
use pocketmine\world\particle\Particle;
use pocketmine\world\sound\BlockPlaceSound;
use pocketmine\world\sound\Sound;
use function abs;
use function array_fill_keys;
use function array_map;
use function array_merge;
use function array_sum;
use function assert;
use function cos;
use function count;
use function floor;
use function get_class;
use function gettype;
use function is_a;
use function is_array;
use function is_object;
use function lcg_value;
use function max;
use function microtime;
use function min;
use function mt_rand;
use function spl_object_id;
use function strtolower;
use function trim;
use const M_PI;
use const PHP_INT_MAX;
use const PHP_INT_MIN;

#include <rules/World.h>

class World implements ChunkManager{

	/** @var int */
	private static $worldIdCounter = 1;

	public const Y_MASK = 0xFF;
	public const Y_MAX = 0x100; //256

	public const HALF_Y_MAX = self::Y_MAX / 2;

	public const TIME_DAY = 1000;
	public const TIME_NOON = 6000;
	public const TIME_SUNSET = 12000;
	public const TIME_NIGHT = 13000;
	public const TIME_MIDNIGHT = 18000;
	public const TIME_SUNRISE = 23000;

	public const TIME_FULL = 24000;

	public const DIFFICULTY_PEACEFUL = 0;
	public const DIFFICULTY_EASY = 1;
	public const DIFFICULTY_NORMAL = 2;
	public const DIFFICULTY_HARD = 3;

	public const DEFAULT_TICKED_BLOCKS_PER_SUBCHUNK_PER_TICK = 3;

	/** @var Player[] */
	private $players = [];

	/** @var Entity[] */
	private $entities = [];

	/** @var Entity[] */
	public $updateEntities = [];
	/** @var Block[][] */
	private $blockCache = [];

	/** @var int */
	private $sendTimeTicker = 0;

	/** @var Server */
	private $server;

	/** @var int */
	private $worldId;

	/** @var WritableWorldProvider */
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

	/** @var ChunkListener[][] */
	private $chunkListeners = [];
	/** @var Player[][] */
	private $playerChunkListeners = [];

	/** @var ClientboundPacket[][] */
	private $chunkPackets = [];

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

	/**
	 * @var ReversePriorityQueue
	 * @phpstan-var ReversePriorityQueue<int, Vector3>
	 */
	private $scheduledBlockUpdateQueue;
	/** @var int[] */
	private $scheduledBlockUpdateQueueIndex = [];

	/**
	 * @var \SplQueue
	 * @phpstan-var \SplQueue<int>
	 */
	private $neighbourBlockUpdateQueue;
	/** @var bool[] blockhash => dummy */
	private $neighbourBlockUpdateQueueIndex = [];

	/** @var bool[] */
	private $chunkPopulationQueue = [];
	/** @var bool[] */
	private $chunkLock = [];
	/** @var int */
	private $chunkPopulationQueueSize = 2;
	/** @var bool[] */
	private $generatorRegisteredWorkers = [];

	/** @var bool */
	private $autoSave = true;

	/** @var int */
	private $sleepTicks = 0;

	/** @var int */
	private $chunkTickRadius;
	/** @var int */
	private $chunksPerTick;
	/** @var int */
	private $tickedBlocksPerSubchunkPerTick = self::DEFAULT_TICKED_BLOCKS_PER_SUBCHUNK_PER_TICK;
	/** @var bool[] */
	private $randomTickBlocks = [];

	/** @var WorldTimings */
	public $timings;

	/** @var float */
	public $tickRateTime = 0;

	/** @var bool */
	private $doingTick = false;

	/** @var string|Generator */
	private $generator;

	/** @var bool */
	private $closed = false;
	/**
	 * @var \Closure[]
	 * @phpstan-var array<int, \Closure() : void>
	 */
	private $unloadCallbacks = [];

	/** @var BlockLightUpdate|null */
	private $blockLightUpdate = null;
	/** @var SkyLightUpdate|null */
	private $skyLightUpdate = null;

	/** @var \Logger */
	private $logger;

	public static function chunkHash(int $x, int $z) : int{
		return (($x & 0xFFFFFFFF) << 32) | ($z & 0xFFFFFFFF);
	}

	public static function blockHash(int $x, int $y, int $z) : int{
		$shiftedY = $y - self::HALF_Y_MAX;
		if($shiftedY < -512 or $shiftedY >= 512){
			throw new \InvalidArgumentException("Y coordinate $y is out of range!");
		}
		return (($x & 0x7ffffff) << 37) | (($shiftedY & 0x3ff) << 27) | ($z & 0x7ffffff);
	}

	/**
	 * Computes a small index relative to chunk base from the given coordinates.
	 */
	public static function chunkBlockHash(int $x, int $y, int $z) : int{
		return ($y << 8) | (($z & 0xf) << 4) | ($x & 0xf);
	}

	public static function getBlockXYZ(int $hash, ?int &$x, ?int &$y, ?int &$z) : void{
		$x = $hash >> 37;
		$y = ($hash << 27 >> 54) + self::HALF_Y_MAX;
		$z = $hash << 37 >> 37;
	}

	public static function getXZ(int $hash, ?int &$x, ?int &$z) : void{
		$x = $hash >> 32;
		$z = ($hash & 0xFFFFFFFF) << 32 >> 32;
	}

	public static function getDifficultyFromString(string $str) : int{
		switch(strtolower(trim($str))){
			case "0":
			case "peaceful":
			case "p":
				return World::DIFFICULTY_PEACEFUL;

			case "1":
			case "easy":
			case "e":
				return World::DIFFICULTY_EASY;

			case "2":
			case "normal":
			case "n":
				return World::DIFFICULTY_NORMAL;

			case "3":
			case "hard":
			case "h":
				return World::DIFFICULTY_HARD;
		}

		return -1;
	}

	/**
	 * Init the default world data
	 */
	public function __construct(Server $server, string $name, WritableWorldProvider $provider){
		$this->worldId = static::$worldIdCounter++;
		$this->server = $server;

		$this->provider = $provider;

		$this->displayName = $this->provider->getWorldData()->getName();
		$this->logger = new \PrefixedLogger($server->getLogger(), "World: $this->displayName");

		$this->worldHeight = $this->provider->getWorldHeight();

		$this->server->getLogger()->info($this->server->getLanguage()->translateString("pocketmine.level.preparing", [$this->displayName]));
		$this->generator = GeneratorManager::getInstance()->getGenerator($this->provider->getWorldData()->getGenerator(), true);
		//TODO: validate generator options

		$this->folderName = $name;

		$this->scheduledBlockUpdateQueue = new ReversePriorityQueue();
		$this->scheduledBlockUpdateQueue->setExtractFlags(\SplPriorityQueue::EXTR_BOTH);

		$this->neighbourBlockUpdateQueue = new \SplQueue();

		$this->time = $this->provider->getWorldData()->getTime();

		$cfg = $this->server->getConfigGroup();
		$this->chunkTickRadius = min($this->server->getViewDistance(), max(1, (int) $cfg->getProperty("chunk-ticking.tick-radius", 4)));
		$this->chunksPerTick = (int) $cfg->getProperty("chunk-ticking.per-tick", 40);
		$this->tickedBlocksPerSubchunkPerTick = (int) $cfg->getProperty("chunk-ticking.blocks-per-subchunk-per-tick", self::DEFAULT_TICKED_BLOCKS_PER_SUBCHUNK_PER_TICK);
		$this->chunkPopulationQueueSize = (int) $cfg->getProperty("chunk-generation.population-queue-size", 2);

		$dontTickBlocks = array_fill_keys($cfg->getProperty("chunk-ticking.disable-block-ticking", []), true);

		foreach(BlockFactory::getInstance()->getAllKnownStates() as $state){
			if(!isset($dontTickBlocks[$state->getId()]) and $state->ticksRandomly()){
				$this->randomTickBlocks[$state->getFullId()] = true;
			}
		}

		$this->timings = new WorldTimings($this);
	}

	public function getTickRateTime() : float{
		return $this->tickRateTime;
	}

	public function registerGeneratorToWorker(int $worker) : void{
		$this->generatorRegisteredWorkers[$worker] = true;
		$this->server->getAsyncPool()->submitTaskToWorker(new GeneratorRegisterTask($this, $this->generator, $this->provider->getWorldData()->getGeneratorOptions()), $worker);
	}

	public function unregisterGenerator() : void{
		$pool = $this->server->getAsyncPool();
		foreach($pool->getRunningWorkers() as $i){
			if(isset($this->generatorRegisteredWorkers[$i])){
				$pool->submitTaskToWorker(new GeneratorUnregisterTask($this), $i);
			}
		}
		$this->generatorRegisteredWorkers = [];
	}

	public function getServer() : Server{
		return $this->server;
	}

	public function getLogger() : \Logger{
		return $this->logger;
	}

	final public function getProvider() : WritableWorldProvider{
		return $this->provider;
	}

	/**
	 * Returns the unique world identifier
	 */
	final public function getId() : int{
		return $this->worldId;
	}

	public function isClosed() : bool{
		return $this->closed;
	}

	/**
	 * @internal
	 */
	public function close() : void{
		if($this->closed){
			throw new \InvalidStateException("Tried to close a world which is already closed");
		}

		foreach($this->unloadCallbacks as $callback){
			$callback();
		}
		$this->unloadCallbacks = [];

		foreach($this->chunks as $chunk){
			$this->unloadChunk($chunk->getX(), $chunk->getZ(), false);
		}

		$this->save();

		$this->unregisterGenerator();

		$this->provider->close();
		$this->provider = null;
		$this->blockCache = [];

		$this->closed = true;
	}

	public function addOnUnloadCallback(\Closure $callback) : void{
		$this->unloadCallbacks[spl_object_id($callback)] = $callback;
	}

	public function removeOnUnloadCallback(\Closure $callback) : void{
		unset($this->unloadCallbacks[spl_object_id($callback)]);
	}

	/**
	 * @param Player[]|null $players
	 */
	public function addSound(Vector3 $pos, Sound $sound, ?array $players = null) : void{
		$pk = $sound->encode($pos);
		if(!is_array($pk)){
			$pk = [$pk];
		}
		if(count($pk) > 0){
			if($players === null){
				foreach($pk as $e){
					$this->broadcastPacketToViewers($pos, $e);
				}
			}else{
				$this->server->broadcastPackets($players, $pk);
			}
		}
	}

	/**
	 * @param Player[]|null $players
	 */
	public function addParticle(Vector3 $pos, Particle $particle, ?array $players = null) : void{
		$pk = $particle->encode($pos);
		if(!is_array($pk)){
			$pk = [$pk];
		}
		if(count($pk) > 0){
			if($players === null){
				foreach($pk as $e){
					$this->broadcastPacketToViewers($pos, $e);
				}
			}else{
				$this->server->broadcastPackets($players, $pk);
			}
		}
	}

	public function getAutoSave() : bool{
		return $this->autoSave;
	}

	public function setAutoSave(bool $value) : void{
		$this->autoSave = $value;
	}

	/**
	 * @deprecated WARNING: This function has a misleading name. Contrary to what the name might imply, this function
	 * DOES NOT return players who are IN a chunk, rather, it returns players who can SEE the chunk.
	 *
	 * Returns a list of players who have the target chunk within their view distance.
	 *
	 * @return Player[]
	 */
	public function getChunkPlayers(int $chunkX, int $chunkZ) : array{
		return $this->playerChunkListeners[World::chunkHash($chunkX, $chunkZ)] ?? [];
	}

	/**
	 * Gets the chunk loaders being used in a specific chunk
	 *
	 * @return ChunkLoader[]
	 */
	public function getChunkLoaders(int $chunkX, int $chunkZ) : array{
		return $this->chunkLoaders[World::chunkHash($chunkX, $chunkZ)] ?? [];
	}

	/**
	 * Returns an array of players who have the target position within their view distance.
	 *
	 * @return Player[]
	 */
	public function getViewersForPosition(Vector3 $pos) : array{
		return $this->getChunkPlayers($pos->getFloorX() >> 4, $pos->getFloorZ() >> 4);
	}

	/**
	 * Broadcasts a packet to every player who has the target position within their view distance.
	 */
	public function broadcastPacketToViewers(Vector3 $pos, ClientboundPacket $packet) : void{
		if(!isset($this->chunkPackets[$index = World::chunkHash($pos->getFloorX() >> 4, $pos->getFloorZ() >> 4)])){
			$this->chunkPackets[$index] = [$packet];
		}else{
			$this->chunkPackets[$index][] = $packet;
		}
	}

	public function registerChunkLoader(ChunkLoader $loader, int $chunkX, int $chunkZ, bool $autoLoad = true) : void{
		$loaderId = spl_object_id($loader);

		if(!isset($this->chunkLoaders[$chunkHash = World::chunkHash($chunkX, $chunkZ)])){
			$this->chunkLoaders[$chunkHash] = [];
		}elseif(isset($this->chunkLoaders[$chunkHash][$loaderId])){
			return;
		}

		$this->chunkLoaders[$chunkHash][$loaderId] = $loader;

		if(!isset($this->loaders[$loaderId])){
			$this->loaderCounter[$loaderId] = 1;
			$this->loaders[$loaderId] = $loader;
		}else{
			++$this->loaderCounter[$loaderId];
		}

		$this->cancelUnloadChunkRequest($chunkX, $chunkZ);

		if($autoLoad){
			$this->loadChunk($chunkX, $chunkZ);
		}
	}

	public function unregisterChunkLoader(ChunkLoader $loader, int $chunkX, int $chunkZ) : void{
		$chunkHash = World::chunkHash($chunkX, $chunkZ);
		$loaderId = spl_object_id($loader);
		if(isset($this->chunkLoaders[$chunkHash][$loaderId])){
			unset($this->chunkLoaders[$chunkHash][$loaderId]);
			if(count($this->chunkLoaders[$chunkHash]) === 0){
				unset($this->chunkLoaders[$chunkHash]);
				$this->unloadChunkRequest($chunkX, $chunkZ, true);
			}

			if(--$this->loaderCounter[$loaderId] === 0){
				unset($this->loaderCounter[$loaderId]);
				unset($this->loaders[$loaderId]);
			}
		}
	}

	/**
	 * Registers a listener to receive events on a chunk.
	 */
	public function registerChunkListener(ChunkListener $listener, int $chunkX, int $chunkZ) : void{
		$hash = World::chunkHash($chunkX, $chunkZ);
		if(isset($this->chunkListeners[$hash])){
			$this->chunkListeners[$hash][spl_object_id($listener)] = $listener;
		}else{
			$this->chunkListeners[$hash] = [spl_object_id($listener) => $listener];
		}
		if($listener instanceof Player){
			$this->playerChunkListeners[$hash][spl_object_id($listener)] = $listener;
		}
	}

	/**
	 * Unregisters a chunk listener previously registered.
	 *
	 * @see World::registerChunkListener()
	 */
	public function unregisterChunkListener(ChunkListener $listener, int $chunkX, int $chunkZ) : void{
		$hash = World::chunkHash($chunkX, $chunkZ);
		if(isset($this->chunkListeners[$hash])){
			unset($this->chunkListeners[$hash][spl_object_id($listener)]);
			unset($this->playerChunkListeners[$hash][spl_object_id($listener)]);
			if(count($this->chunkListeners[$hash]) === 0){
				unset($this->chunkListeners[$hash]);
				unset($this->playerChunkListeners[$hash]);
			}
		}
	}

	/**
	 * Unregisters a chunk listener from all chunks it is listening on in this World.
	 */
	public function unregisterChunkListenerFromAll(ChunkListener $listener) : void{
		$id = spl_object_id($listener);
		foreach($this->chunkListeners as $hash => $listeners){
			if(isset($listeners[$id])){
				unset($this->chunkListeners[$hash][$id]);
				if(count($this->chunkListeners[$hash]) === 0){
					unset($this->chunkListeners[$hash]);
				}
			}
		}
	}

	/**
	 * Returns all the listeners attached to this chunk.
	 *
	 * @return ChunkListener[]
	 */
	public function getChunkListeners(int $chunkX, int $chunkZ) : array{
		return $this->chunkListeners[World::chunkHash($chunkX, $chunkZ)] ?? [];
	}

	/**
	 * @internal
	 *
	 * @param Player ...$targets If empty, will send to all players in the world.
	 */
	public function sendTime(Player ...$targets) : void{
		if(count($targets) === 0){
			$targets = $this->players;
		}
		foreach($targets as $player){
			$player->getNetworkSession()->syncWorldTime($this->time);
		}
	}

	public function isDoingTick() : bool{
		return $this->doingTick;
	}

	/**
	 * @internal
	 */
	public function doTick(int $currentTick) : void{
		if($this->closed){
			throw new \InvalidStateException("Attempted to tick a world which has been closed");
		}

		$this->timings->doTick->startTiming();
		$this->doingTick = true;
		try{
			$this->actuallyDoTick($currentTick);
		}finally{
			$this->doingTick = false;
			$this->timings->doTick->stopTiming();
		}
	}

	protected function actuallyDoTick(int $currentTick) : void{
		if(!$this->stopTime){
			//this simulates an overflow, as would happen in any language which doesn't do stupid things to var types
			if($this->time === PHP_INT_MAX){
				$this->time = PHP_INT_MIN;
			}else{
				$this->time++;
			}
		}

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
			/** @var Vector3 $vec */
			$vec = $this->scheduledBlockUpdateQueue->extract()["data"];
			unset($this->scheduledBlockUpdateQueueIndex[World::blockHash($vec->x, $vec->y, $vec->z)]);
			if(!$this->isInLoadedTerrain($vec)){
				continue;
			}
			$block = $this->getBlock($vec);
			$block->onScheduledUpdate();
		}

		//Normal updates
		while($this->neighbourBlockUpdateQueue->count() > 0){
			$index = $this->neighbourBlockUpdateQueue->dequeue();
			World::getBlockXYZ($index, $x, $y, $z);

			$block = $this->getBlockAt($x, $y, $z);
			$block->readStateFromWorld(); //for blocks like fences, force recalculation of connected AABBs

			$ev = new BlockUpdateEvent($block);
			$ev->call();
			if(!$ev->isCancelled()){
				foreach($this->getNearbyEntities(AxisAlignedBB::one()->offset($x, $y, $z)) as $entity){
					$entity->onNearbyBlockChange();
				}
				$block->onNearbyBlockChange();
			}
			unset($this->neighbourBlockUpdateQueueIndex[$index]);
		}

		$this->timings->doTickPending->stopTiming();

		$this->timings->entityTick->startTiming();
		//Update entities that need update
		Timings::$tickEntityTimer->startTiming();
		foreach($this->updateEntities as $id => $entity){
			if($entity->isClosed() or !$entity->onUpdate($currentTick)){
				unset($this->updateEntities[$id]);
			}
			if($entity->isFlaggedForDespawn()){
				$entity->close();
			}
		}
		Timings::$tickEntityTimer->stopTiming();
		$this->timings->entityTick->stopTiming();

		$this->timings->doTickTiles->startTiming();
		$this->tickChunks();
		$this->timings->doTickTiles->stopTiming();

		$this->executeQueuedLightUpdates();

		if(count($this->changedBlocks) > 0){
			if(count($this->players) > 0){
				foreach($this->changedBlocks as $index => $blocks){
					if(count($blocks) === 0){ //blocks can be set normally and then later re-set with direct send
						continue;
					}
					World::getXZ($index, $chunkX, $chunkZ);
					if(count($blocks) > 512){
						$chunk = $this->getChunk($chunkX, $chunkZ);
						foreach($this->getChunkPlayers($chunkX, $chunkZ) as $p){
							$p->onChunkChanged($chunk);
						}
					}else{
						$this->sendBlocks($this->getChunkPlayers($chunkX, $chunkZ), $blocks);
					}
				}
			}

			$this->changedBlocks = [];

		}

		foreach($this->players as $p){
			$p->doChunkRequests();
		}

		if($this->sleepTicks > 0 and --$this->sleepTicks <= 0){
			$this->checkSleep();
		}

		foreach($this->chunkPackets as $index => $entries){
			World::getXZ($index, $chunkX, $chunkZ);
			$chunkPlayers = $this->getChunkPlayers($chunkX, $chunkZ);
			if(count($chunkPlayers) > 0){
				$this->server->broadcastPackets($chunkPlayers, $entries);
			}
		}

		$this->chunkPackets = [];
	}

	public function checkSleep() : void{
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
			$time = $this->getTimeOfDay();

			if($time >= World::TIME_NIGHT and $time < World::TIME_SUNRISE){
				$this->setTime($this->getTime() + World::TIME_FULL - $time);

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
	 * @param Player[]  $target
	 * @param Vector3[] $blocks
	 */
	public function sendBlocks(array $target, array $blocks) : void{
		$packets = [];

		foreach($blocks as $b){
			if(!($b instanceof Vector3)){
				throw new \TypeError("Expected Vector3 in blocks array, got " . (is_object($b) ? get_class($b) : gettype($b)));
			}

			$fullBlock = $this->getBlockAt($b->x, $b->y, $b->z);
			$packets[] = UpdateBlockPacket::create($b->x, $b->y, $b->z, RuntimeBlockMapping::getInstance()->toRuntimeId($fullBlock->getId(), $fullBlock->getMeta()));

			$tile = $this->getTileAt($b->x, $b->y, $b->z);
			if($tile instanceof Spawnable){
				$packets[] = BlockActorDataPacket::create($b->x, $b->y, $b->z, $tile->getSerializedSpawnCompound());
			}
		}

		$this->server->broadcastPackets($target, $packets);
	}

	public function clearCache(bool $force = false) : void{
		if($force){
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

	/**
	 * @return bool[] fullID => bool
	 */
	public function getRandomTickedBlocks() : array{
		return $this->randomTickBlocks;
	}

	public function addRandomTickedBlock(Block $block) : void{
		if($block instanceof UnknownBlock){
			throw new \InvalidArgumentException("Cannot do random-tick on unknown block");
		}
		$this->randomTickBlocks[$block->getFullId()] = true;
	}

	public function removeRandomTickedBlock(Block $block) : void{
		unset($this->randomTickBlocks[$block->getFullId()]);
	}

	private function tickChunks() : void{
		if($this->chunksPerTick <= 0 or count($this->loaders) === 0){
			return;
		}

		/** @var bool[] $chunkTickList chunkhash => dummy */
		$chunkTickList = [];

		$chunksPerLoader = min(200, max(1, (int) ((($this->chunksPerTick - count($this->loaders)) / count($this->loaders)) + 0.5)));
		$randRange = 3 + $chunksPerLoader / 30;
		$randRange = (int) ($randRange > $this->chunkTickRadius ? $this->chunkTickRadius : $randRange);

		foreach($this->loaders as $loader){
			$chunkX = (int) floor($loader->getX()) >> 4;
			$chunkZ = (int) floor($loader->getZ()) >> 4;

			for($chunk = 0; $chunk < $chunksPerLoader; ++$chunk){
				$dx = mt_rand(-$randRange, $randRange);
				$dz = mt_rand(-$randRange, $randRange);
				$hash = World::chunkHash($dx + $chunkX, $dz + $chunkZ);
				if(!isset($chunkTickList[$hash]) and isset($this->chunks[$hash])){
					if(!$this->chunks[$hash]->isLightPopulated()){
						continue;
					}
					//check adjacent chunks are loaded
					for($cx = -1; $cx <= 1; ++$cx){
						for($cz = -1; $cz <= 1; ++$cz){
							if(!isset($this->chunks[World::chunkHash($chunkX + $dx + $cx, $chunkZ + $dz + $cz)])){
								continue 3;
							}
						}
					}
					$chunkTickList[$hash] = true;
				}
			}
		}

		foreach($chunkTickList as $index => $_){
			World::getXZ($index, $chunkX, $chunkZ);

			$chunk = $this->chunks[$index];
			foreach($chunk->getEntities() as $entity){
				$entity->scheduleUpdate();
			}

			foreach($chunk->getSubChunks() as $Y => $subChunk){
				if(!$subChunk->isEmptyFast()){
					$k = 0;
					for($i = 0; $i < $this->tickedBlocksPerSubchunkPerTick; ++$i){
						if(($i % 5) === 0){
							//60 bits will be used by 5 blocks (12 bits each)
							$k = mt_rand(0, (1 << 60) - 1);
						}
						$x = $k & 0x0f;
						$y = ($k >> 4) & 0x0f;
						$z = ($k >> 8) & 0x0f;
						$k >>= 12;

						$state = $subChunk->getFullBlock($x, $y, $z);

						if(isset($this->randomTickBlocks[$state])){
							/** @var Block $block */
							$block = BlockFactory::getInstance()->fromFullBlock($state);
							$block->position($this, $chunkX * 16 + $x, ($Y << 4) + $y, $chunkZ * 16 + $z);
							$block->onRandomTick();
						}
					}
				}
			}
		}
	}

	/**
	 * @return mixed[]
	 */
	public function __debugInfo() : array{
		return [];
	}

	public function save(bool $force = false) : bool{

		if(!$this->getAutoSave() and !$force){
			return false;
		}

		(new WorldSaveEvent($this))->call();

		$this->provider->getWorldData()->setTime($this->time);
		$this->saveChunks();
		$this->provider->getWorldData()->save();

		return true;
	}

	public function saveChunks() : void{
		$this->timings->syncChunkSaveTimer->startTiming();
		try{
			foreach($this->chunks as $chunk){
				if($chunk->isDirty() and $chunk->isGenerated()){
					$this->provider->saveChunk($chunk);
					$chunk->clearDirtyFlags();
				}
			}
		}finally{
			$this->timings->syncChunkSaveTimer->stopTiming();
		}
	}

	/**
	 * Schedules a block update to be executed after the specified number of ticks.
	 * Blocks will be updated with the scheduled update type.
	 */
	public function scheduleDelayedBlockUpdate(Vector3 $pos, int $delay) : void{
		if(
			!$this->isInWorld($pos->x, $pos->y, $pos->z) or
			(isset($this->scheduledBlockUpdateQueueIndex[$index = World::blockHash($pos->x, $pos->y, $pos->z)]) and $this->scheduledBlockUpdateQueueIndex[$index] <= $delay)
		){
			return;
		}
		$this->scheduledBlockUpdateQueueIndex[$index] = $delay;
		$this->scheduledBlockUpdateQueue->insert(new Vector3((int) $pos->x, (int) $pos->y, (int) $pos->z), $delay + $this->server->getTick());
	}

	private function tryAddToNeighbourUpdateQueue(Vector3 $pos) : void{
		if($this->isInWorld($pos->x, $pos->y, $pos->z)){
			$hash = World::blockHash($pos->x, $pos->y, $pos->z);
			if(!isset($this->neighbourBlockUpdateQueueIndex[$hash])){
				$this->neighbourBlockUpdateQueue->enqueue($hash);
				$this->neighbourBlockUpdateQueueIndex[$hash] = true;
			}
		}
	}

	/**
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
						if($block->collidesWithBB($bb)){
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
						if($block->collidesWithBB($bb)){
							$collides[] = $block;
						}
					}
				}
			}
		}

		return $collides;
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	public function getCollisionBoxes(Entity $entity, AxisAlignedBB $bb, bool $entities = true) : array{
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
					foreach($block->getCollisionBoxes() as $blockBB){
						if($blockBB->intersectsWith($bb)){
							$collides[] = $blockBB;
						}
					}
				}
			}
		}

		if($entities){
			foreach($this->getCollidingEntities($bb->expandedCopy(0.25, 0.25, 0.25), $entity) as $ent){
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
	 */
	public function getSunAnglePercentage() : float{
		return $this->sunAnglePercentage;
	}

	/**
	 * Returns the current sun angle in radians.
	 */
	public function getSunAngleRadians() : float{
		return $this->sunAnglePercentage * 2 * M_PI;
	}

	/**
	 * Returns the current sun angle in degrees.
	 */
	public function getSunAngleDegrees() : float{
		return $this->sunAnglePercentage * 360.0;
	}

	/**
	 * Computes how many points of sky light is subtracted based on the current time. Used to offset raw chunk sky light
	 * to get a real light value.
	 */
	public function computeSkyLightReduction() : int{
		$percentage = max(0, min(1, -(cos($this->getSunAngleRadians()) * 2 - 0.5)));

		//TODO: check rain and thunder level

		return (int) ($percentage * 11);
	}

	/**
	 * Returns how many points of sky light is subtracted based on the current time.
	 */
	public function getSkyLightReduction() : int{
		return $this->skyLightReduction;
	}

	/**
	 * Returns the sky light level at the specified coordinates, offset by the current time and weather.
	 *
	 * @return int 0-15
	 */
	public function getRealBlockSkyLightAt(int $x, int $y, int $z) : int{
		$light = $this->getBlockSkyLightAt($x, $y, $z) - $this->skyLightReduction;
		return $light < 0 ? 0 : $light;
	}

	public function updateAllLight(int $x, int $y, int $z) : void{
		$blockFactory = BlockFactory::getInstance();
		$this->timings->doBlockSkyLightUpdates->startTiming();
		if($this->skyLightUpdate === null){
			$this->skyLightUpdate = new SkyLightUpdate($this, $blockFactory->lightFilter, $blockFactory->blocksDirectSkyLight);
		}
		$this->skyLightUpdate->recalculateNode($x, $y, $z);
		$this->timings->doBlockSkyLightUpdates->stopTiming();

		$this->timings->doBlockLightUpdates->startTiming();
		if($this->blockLightUpdate === null){
			$this->blockLightUpdate = new BlockLightUpdate($this, $blockFactory->lightFilter, $blockFactory->light);
		}
		$this->blockLightUpdate->recalculateNode($x, $y, $z);
		$this->timings->doBlockLightUpdates->stopTiming();
	}

	/**
	 * Returns the highest block light level available in the positions adjacent to the specified block coordinates.
	 */
	public function getHighestAdjacentBlockSkyLight(int $x, int $y, int $z) : int{
		$max = 0;
		foreach([
			[$x + 1, $y, $z],
			[$x - 1, $y, $z],
			[$x, $y + 1, $z],
			[$x, $y - 1, $z],
			[$x, $y, $z + 1],
			[$x, $y, $z - 1]
		] as [$x1, $y1, $z1]){
			if(!$this->isInWorld($x1, $y1, $z1)){
				continue;
			}
			$max = max($max, $this->getBlockSkyLightAt($x1, $y1, $z1));
		}
		return $max;
	}

	/**
	 * Returns the highest block light level available in the positions adjacent to the specified block coordinates.
	 */
	public function getHighestAdjacentBlockLight(int $x, int $y, int $z) : int{
		$max = 0;
		foreach([
			[$x + 1, $y, $z],
			[$x - 1, $y, $z],
			[$x, $y + 1, $z],
			[$x, $y - 1, $z],
			[$x, $y, $z + 1],
			[$x, $y, $z - 1]
		] as [$x1, $y1, $z1]){
			if(!$this->isInWorld($x1, $y1, $z1)){
				continue;
			}
			$max = max($max, $this->getBlockLightAt($x1, $y1, $z1));
		}
		return $max;
	}

	private function executeQueuedLightUpdates() : void{
		if($this->blockLightUpdate !== null){
			$this->timings->doBlockLightUpdates->startTiming();
			$this->blockLightUpdate->execute();
			$this->blockLightUpdate = null;
			$this->timings->doBlockLightUpdates->stopTiming();
		}

		if($this->skyLightUpdate !== null){
			$this->timings->doBlockSkyLightUpdates->startTiming();
			$this->skyLightUpdate->execute();
			$this->skyLightUpdate = null;
			$this->timings->doBlockSkyLightUpdates->stopTiming();
		}
	}

	/**
	 * @return int bitmap, (id << 4) | data
	 */
	public function getFullBlock(int $x, int $y, int $z) : int{
		return $this->getChunk($x >> 4, $z >> 4, false)->getFullBlock($x & 0x0f, $y, $z & 0x0f);
	}

	public function isInWorld(int $x, int $y, int $z) : bool{
		return (
			$x <= Limits::INT32_MAX and $x >= Limits::INT32_MIN and
			$y < $this->worldHeight and $y >= 0 and
			$z <= Limits::INT32_MAX and $z >= Limits::INT32_MIN
		);
	}

	/**
	 * Gets the Block object at the Vector3 location. This method wraps around {@link getBlockAt}, converting the
	 * vector components to integers.
	 *
	 * Note: If you're using this for performance-sensitive code, and you're guaranteed to be supplying ints in the
	 * specified vector, consider using {@link getBlockAt} instead for better performance.
	 *
	 * @param bool    $cached Whether to use the block cache for getting the block (faster, but may be inaccurate)
	 * @param bool    $addToCache Whether to cache the block object created by this method call.
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
	 * @param bool $cached Whether to use the block cache for getting the block (faster, but may be inaccurate)
	 * @param bool $addToCache Whether to cache the block object created by this method call.
	 */
	public function getBlockAt(int $x, int $y, int $z, bool $cached = true, bool $addToCache = true) : Block{
		$fullState = 0;
		$relativeBlockHash = null;
		$chunkHash = World::chunkHash($x >> 4, $z >> 4);

		if($this->isInWorld($x, $y, $z)){
			$relativeBlockHash = World::chunkBlockHash($x, $y, $z);

			if($cached and isset($this->blockCache[$chunkHash][$relativeBlockHash])){
				return $this->blockCache[$chunkHash][$relativeBlockHash];
			}

			$chunk = $this->chunks[$chunkHash] ?? null;
			if($chunk !== null){
				$fullState = $chunk->getFullBlock($x & 0x0f, $y, $z & 0x0f);
			}else{
				$addToCache = false;
			}
		}

		$block = BlockFactory::getInstance()->fromFullBlock($fullState);
		$block->position($this, $x, $y, $z);

		static $dynamicStateRead = false;

		if($dynamicStateRead){
			//this call was generated by a parent getBlock() call calculating dynamic stateinfo
			//don't calculate dynamic state and don't add to block cache (since it won't have dynamic state calculated).
			//this ensures that it's impossible for dynamic state properties to recursively depend on each other.
			$addToCache = false;
		}else{
			$dynamicStateRead = true;
			$block->readStateFromWorld();
			$dynamicStateRead = false;
		}

		if($addToCache and $relativeBlockHash !== null){
			$this->blockCache[$chunkHash][$relativeBlockHash] = $block;
		}

		return $block;
	}

	/**
	 * Sets the block at the given Vector3 coordinates.
	 *
	 * @throws \InvalidArgumentException if the position is out of the world bounds
	 */
	public function setBlock(Vector3 $pos, Block $block, bool $update = true) : void{
		$this->setBlockAt((int) floor($pos->x), (int) floor($pos->y), (int) floor($pos->z), $block, $update);
	}

	/**
	 * Sets the block at the given coordinates.
	 *
	 * If $update is true, it'll get the neighbour blocks (6 sides) and update them, and also update local lighting.
	 * If you are doing big changes, you might want to set this to false, then update manually.
	 *
	 * @throws \InvalidArgumentException if the position is out of the world bounds
	 */
	public function setBlockAt(int $x, int $y, int $z, Block $block, bool $update = true) : void{
		if(!$this->isInWorld($x, $y, $z)){
			throw new \InvalidArgumentException("Pos x=$x,y=$y,z=$z is outside of the world bounds");
		}

		$this->timings->setBlock->startTiming();

		$oldBlock = $this->getBlockAt($x, $y, $z, true, false);

		$block = clone $block;

		$block->position($this, $x, $y, $z);
		$block->writeStateToWorld();
		$pos = $block->getPos();

		$chunkHash = World::chunkHash($x >> 4, $z >> 4);
		$relativeBlockHash = World::chunkBlockHash($x, $y, $z);

		unset($this->blockCache[$chunkHash][$relativeBlockHash]);

		if(!isset($this->changedBlocks[$chunkHash])){
			$this->changedBlocks[$chunkHash] = [];
		}
		$this->changedBlocks[$chunkHash][$relativeBlockHash] = $pos;

		foreach($this->getChunkListeners($x >> 4, $z >> 4) as $listener){
			$listener->onBlockChanged($pos);
		}

		if($update){
			if($oldBlock->getLightFilter() !== $block->getLightFilter() or $oldBlock->getLightLevel() !== $block->getLightLevel()){
				$this->updateAllLight($x, $y, $z);
			}
			$this->tryAddToNeighbourUpdateQueue($pos);
			foreach($pos->sides() as $side){
				$this->tryAddToNeighbourUpdateQueue($side);
			}
		}

		$this->timings->setBlock->stopTiming();
	}

	public function dropItem(Vector3 $source, Item $item, ?Vector3 $motion = null, int $delay = 10) : ?ItemEntity{
		if($item->isNull()){
			return null;
		}

		$itemEntity = new ItemEntity(Location::fromObject($source, $this, lcg_value() * 360, 0), $item);

		$itemEntity->setPickupDelay($delay);
		$itemEntity->setMotion($motion ?? new Vector3(lcg_value() * 0.2 - 0.1, 0.2, lcg_value() * 0.2 - 0.1));
		$itemEntity->spawnToAll();

		return $itemEntity;
	}

	/**
	 * Drops XP orbs into the world for the specified amount, splitting the amount into several orbs if necessary.
	 *
	 * @return ExperienceOrb[]
	 */
	public function dropExperience(Vector3 $pos, int $amount) : array{
		/** @var ExperienceOrb[] $orbs */
		$orbs = [];

		foreach(ExperienceOrb::splitIntoOrbSizes($amount) as $split){
			$orb = new ExperienceOrb(Location::fromObject($pos, $this, lcg_value() * 360, 0));

			$orb->setXpValue($split);
			$orb->setMotion(new Vector3((lcg_value() * 0.2 - 0.1) * 2, lcg_value() * 0.4, (lcg_value() * 0.2 - 0.1) * 2));
			$orb->spawnToAll();

			$orbs[] = $orb;
		}

		return $orbs;
	}

	/**
	 * Tries to break a block using a item, including Player time checks if available
	 * It'll try to lower the durability if Item is a tool, and set it to Air if broken.
	 *
	 * @param Item    $item reference parameter (if null, can break anything)
	 */
	public function useBreakOn(Vector3 $vector, Item &$item = null, ?Player $player = null, bool $createParticles = false) : bool{
		$vector = $vector->floor();
		$target = $this->getBlock($vector);
		$affectedBlocks = $target->getAffectedBlocks();

		if($item === null){
			$item = ItemFactory::air();
		}

		$drops = [];
		if($player === null or $player->hasFiniteResources()){
			$drops = array_merge(...array_map(function(Block $block) use ($item) : array{ return $block->getDrops($item); }, $affectedBlocks));
		}

		$xpDrop = 0;
		if($player !== null and $player->hasFiniteResources()){
			$xpDrop = array_sum(array_map(function(Block $block) use ($item) : int{ return $block->getXpDropForTool($item); }, $affectedBlocks));
		}

		if($player !== null){
			$ev = new BlockBreakEvent($player, $target, $item, $player->isCreative(), $drops, $xpDrop);

			if($target instanceof Air or ($player->isSurvival() and !$target->getBreakInfo()->isBreakable()) or $player->isSpectator()){
				$ev->setCancelled();
			}

			if($player->isAdventure(true) and !$ev->isCancelled()){
				$canBreak = false;
				$itemParser = LegacyStringToItemParser::getInstance();
				foreach($item->getCanDestroy() as $v){
					$entry = $itemParser->parse($v);
					if($entry->getBlock()->isSameType($target)){
						$canBreak = true;
						break;
					}
				}

				$ev->setCancelled(!$canBreak);
			}

			$ev->call();
			if($ev->isCancelled()){
				return false;
			}

			$drops = $ev->getDrops();
			$xpDrop = $ev->getXpDropAmount();

		}elseif(!$target->getBreakInfo()->isBreakable()){
			return false;
		}

		foreach($affectedBlocks as $t){
			$this->destroyBlockInternal($t, $item, $player, $createParticles);
		}

		$item->onDestroyBlock($target);

		if(count($drops) > 0){
			$dropPos = $vector->add(0.5, 0.5, 0.5);
			foreach($drops as $drop){
				if(!$drop->isNull()){
					$this->dropItem($dropPos, $drop);
				}
			}
		}

		if($xpDrop > 0){
			$this->dropExperience($vector->add(0.5, 0.5, 0.5), $xpDrop);
		}

		return true;
	}

	private function destroyBlockInternal(Block $target, Item $item, ?Player $player = null, bool $createParticles = false) : void{
		if($createParticles){
			$this->addParticle($target->getPos()->add(0.5, 0.5, 0.5), new DestroyBlockParticle($target));
		}

		$target->onBreak($item, $player);

		$tile = $this->getTile($target->getPos());
		if($tile !== null){
			$tile->onBlockDestroyed();
		}
	}

	/**
	 * Uses a item on a position and face, placing it or activating the block
	 *
	 * @param Player|null  $player default null
	 * @param bool         $playSound Whether to play a block-place sound if the block was placed successfully.
	 */
	public function useItemOn(Vector3 $vector, Item &$item, int $face, ?Vector3 $clickVector = null, ?Player $player = null, bool $playSound = false) : bool{
		$blockClicked = $this->getBlock($vector);
		$blockReplace = $blockClicked->getSide($face);

		if($clickVector === null){
			$clickVector = new Vector3(0.0, 0.0, 0.0);
		}

		if(!$this->isInWorld($blockReplace->getPos()->x, $blockReplace->getPos()->y, $blockReplace->getPos()->z)){
			//TODO: build height limit messages for custom world heights and mcregion cap
			return false;
		}

		if($blockClicked->getId() === BlockLegacyIds::AIR){
			return false;
		}

		if($player !== null){
			$ev = new PlayerInteractEvent($player, $item, $blockClicked, $clickVector, $face, PlayerInteractEvent::RIGHT_CLICK_BLOCK);
			if($player->isSpectator()){
				$ev->setCancelled(); //set it to cancelled so plugins can bypass this
			}

			$ev->call();
			if(!$ev->isCancelled()){
				if((!$player->isSneaking() or $item->isNull()) and $blockClicked->onInteract($item, $face, $clickVector, $player)){
					return true;
				}

				$result = $item->onActivate($player, $blockReplace, $blockClicked, $face, $clickVector);
				if(!$result->equals(ItemUseResult::NONE())){
					return $result->equals(ItemUseResult::SUCCESS());
				}
			}else{
				return false;
			}
		}elseif($blockClicked->onInteract($item, $face, $clickVector, $player)){
			return true;
		}

		if($item->canBePlaced()){
			$hand = $item->getBlock();
			$hand->position($this, $blockReplace->getPos()->x, $blockReplace->getPos()->y, $blockReplace->getPos()->z);
		}else{
			return false;
		}

		if($hand->canBePlacedAt($blockClicked, $clickVector, $face, true)){
			$blockReplace = $blockClicked;
			$hand->position($this, $blockReplace->getPos()->x, $blockReplace->getPos()->y, $blockReplace->getPos()->z);
		}elseif(!$hand->canBePlacedAt($blockReplace, $clickVector, $face, false)){
			return false;
		}

		foreach($hand->getCollisionBoxes() as $collisionBox){
			if(count($this->getCollidingEntities($collisionBox)) > 0){
				return false;  //Entity in block
			}
		}

		if($player !== null){
			$ev = new BlockPlaceEvent($player, $hand, $blockReplace, $blockClicked, $item);
			if($player->isSpectator()){
				$ev->setCancelled();
			}

			if($player->isAdventure(true) and !$ev->isCancelled()){
				$canPlace = false;
				$itemParser = LegacyStringToItemParser::getInstance();
				foreach($item->getCanPlaceOn() as $v){
					$entry = $itemParser->parse($v);
					if($entry->getBlock()->isSameType($blockClicked)){
						$canPlace = true;
						break;
					}
				}

				$ev->setCancelled(!$canPlace);
			}

			$ev->call();
			if($ev->isCancelled()){
				return false;
			}
		}

		$tx = new BlockTransaction($this);
		if(!$hand->place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player) or !$tx->apply()){
			return false;
		}
		foreach($tx->getBlocks() as [$x, $y, $z, $_]){
			$tile = $this->getTileAt($x, $y, $z);
			if($tile !== null){
				//TODO: seal this up inside block placement
				$tile->copyDataFromItem($item);
			}

			$this->getBlockAt($x, $y, $z)->onPostPlace();
		}

		if($playSound){
			$this->addSound($hand->getPos(), new BlockPlaceSound($hand));
		}

		$item->pop();

		return true;
	}

	public function getEntity(int $entityId) : ?Entity{
		return $this->entities[$entityId] ?? null;
	}

	/**
	 * Gets the list of all the entities in this world
	 *
	 * @return Entity[]
	 */
	public function getEntities() : array{
		return $this->entities;
	}

	/**
	 * Returns the entities colliding the current one inside the AxisAlignedBB
	 *
	 * @return Entity[]
	 */
	public function getCollidingEntities(AxisAlignedBB $bb, ?Entity $entity = null) : array{
		$nearby = [];

		if($entity === null or $entity->canCollide){
			$minX = ((int) floor($bb->minX - 2)) >> 4;
			$maxX = ((int) floor($bb->maxX + 2)) >> 4;
			$minZ = ((int) floor($bb->minZ - 2)) >> 4;
			$maxZ = ((int) floor($bb->maxZ + 2)) >> 4;

			for($x = $minX; $x <= $maxX; ++$x){
				for($z = $minZ; $z <= $maxZ; ++$z){
					if(!$this->isChunkLoaded($x, $z)){
						continue;
					}
					foreach($this->getChunk($x, $z)->getEntities() as $ent){
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
	 * @return Entity[]
	 */
	public function getNearbyEntities(AxisAlignedBB $bb, ?Entity $entity = null) : array{
		$nearby = [];

		$minX = ((int) floor($bb->minX - 2)) >> 4;
		$maxX = ((int) floor($bb->maxX + 2)) >> 4;
		$minZ = ((int) floor($bb->minZ - 2)) >> 4;
		$maxZ = ((int) floor($bb->maxZ + 2)) >> 4;

		for($x = $minX; $x <= $maxX; ++$x){
			for($z = $minZ; $z <= $maxZ; ++$z){
				if(!$this->isChunkLoaded($x, $z)){
					continue;
				}
				foreach($this->getChunk($x, $z)->getEntities() as $ent){
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
	 * @param string  $entityType Class of entity to use for instanceof
	 * @param bool    $includeDead Whether to include entitites which are dead
	 * @phpstan-template TEntity of Entity
	 * @phpstan-param class-string<TEntity> $entityType
	 *
	 * @return Entity|null an entity of type $entityType, or null if not found
	 * @phpstan-return TEntity
	 */
	public function getNearestEntity(Vector3 $pos, float $maxDistance, string $entityType = Entity::class, bool $includeDead = false) : ?Entity{
		assert(is_a($entityType, Entity::class, true));

		$minX = ((int) floor($pos->x - $maxDistance)) >> 4;
		$maxX = ((int) floor($pos->x + $maxDistance)) >> 4;
		$minZ = ((int) floor($pos->z - $maxDistance)) >> 4;
		$maxZ = ((int) floor($pos->z + $maxDistance)) >> 4;

		$currentTargetDistSq = $maxDistance ** 2;

		/**
		 * @var Entity|null $currentTarget
		 * @phpstan-var TEntity|null $currentTarget
		 */
		$currentTarget = null;

		for($x = $minX; $x <= $maxX; ++$x){
			for($z = $minZ; $z <= $maxZ; ++$z){
				if(!$this->isChunkLoaded($x, $z)){
					continue;
				}
				foreach($this->getChunk($x, $z)->getEntities() as $entity){
					if(!($entity instanceof $entityType) or $entity->isFlaggedForDespawn() or (!$includeDead and !$entity->isAlive())){
						continue;
					}
					$distSq = $entity->getPosition()->distanceSquared($pos);
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
	 * Returns a list of the players in this world
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
	 */
	public function getTile(Vector3 $pos) : ?Tile{
		return $this->getTileAt((int) floor($pos->x), (int) floor($pos->y), (int) floor($pos->z));
	}

	/**
	 * Returns the tile at the specified x,y,z coordinates, or null if it does not exist.
	 */
	public function getTileAt(int $x, int $y, int $z) : ?Tile{
		return ($chunk = $this->getChunk($x >> 4, $z >> 4)) !== null ? $chunk->getTile($x & 0x0f, $y, $z & 0x0f) : null;
	}

	/**
	 * Gets the raw block skylight level
	 *
	 * @return int 0-15
	 */
	public function getBlockSkyLightAt(int $x, int $y, int $z) : int{
		return $this->getChunk($x >> 4, $z >> 4, true)->getBlockSkyLight($x & 0x0f, $y, $z & 0x0f);
	}

	/**
	 * Gets the raw block light level
	 *
	 * @return int 0-15
	 */
	public function getBlockLightAt(int $x, int $y, int $z) : int{
		return $this->getChunk($x >> 4, $z >> 4, true)->getBlockLight($x & 0x0f, $y, $z & 0x0f);
	}

	public function getBiomeId(int $x, int $z) : int{
		return $this->getChunk($x >> 4, $z >> 4, true)->getBiomeId($x & 0x0f, $z & 0x0f);
	}

	public function getBiome(int $x, int $z) : Biome{
		return Biome::getBiome($this->getBiomeId($x, $z));
	}

	public function setBiomeId(int $x, int $z, int $biomeId) : void{
		$this->getChunk($x >> 4, $z >> 4, true)->setBiomeId($x & 0x0f, $z & 0x0f, $biomeId);
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
	 * @param bool $create Whether to create an empty chunk as a placeholder if the chunk does not exist
	 */
	public function getChunk(int $chunkX, int $chunkZ, bool $create = false) : ?Chunk{
		if(isset($this->chunks[$index = World::chunkHash($chunkX, $chunkZ)])){
			return $this->chunks[$index];
		}elseif($this->loadChunk($chunkX, $chunkZ, $create)){
			return $this->chunks[$index];
		}

		return null;
	}

	/**
	 * Returns the chunk containing the given Vector3 position.
	 */
	public function getChunkAtPosition(Vector3 $pos, bool $create = false) : ?Chunk{
		return $this->getChunk($pos->getFloorX() >> 4, $pos->getFloorZ() >> 4, $create);
	}

	/**
	 * Returns the chunks adjacent to the specified chunk.
	 *
	 * @return (Chunk|null)[]
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

	public function lockChunk(int $chunkX, int $chunkZ) : void{
		$chunkHash = World::chunkHash($chunkX, $chunkZ);
		if(isset($this->chunkLock[$chunkHash])){
			throw new \InvalidArgumentException("Chunk $chunkX $chunkZ is already locked");
		}
		$this->chunkLock[$chunkHash] = true;
	}

	public function unlockChunk(int $chunkX, int $chunkZ) : void{
		unset($this->chunkLock[World::chunkHash($chunkX, $chunkZ)]);
	}

	public function isChunkLocked(int $chunkX, int $chunkZ) : bool{
		return isset($this->chunkLock[World::chunkHash($chunkX, $chunkZ)]);
	}

	public function generateChunkCallback(int $x, int $z, ?Chunk $chunk) : void{
		Timings::$generationCallbackTimer->startTiming();
		if(isset($this->chunkPopulationQueue[$index = World::chunkHash($x, $z)])){
			for($xx = -1; $xx <= 1; ++$xx){
				for($zz = -1; $zz <= 1; ++$zz){
					$this->unlockChunk($x + $xx, $z + $zz);
				}
			}
			unset($this->chunkPopulationQueue[$index]);

			if($chunk !== null){
				$oldChunk = $this->getChunk($x, $z, false);
				$this->setChunk($x, $z, $chunk, false);
				if(($oldChunk === null or !$oldChunk->isPopulated()) and $chunk->isPopulated()){
					(new ChunkPopulateEvent($this, $chunk))->call();

					foreach($this->getChunkListeners($x, $z) as $listener){
						$listener->onChunkPopulated($chunk);
					}
				}
			}
		}elseif($this->isChunkLocked($x, $z)){
			$this->unlockChunk($x, $z);
			if($chunk !== null){
				$this->setChunk($x, $z, $chunk, false);
			}
		}elseif($chunk !== null){
			$this->setChunk($x, $z, $chunk, false);
		}
		Timings::$generationCallbackTimer->stopTiming();
	}

	/**
	 * @param bool       $deleteEntitiesAndTiles Whether to delete entities and tiles on the old chunk, or transfer them to the new one
	 */
	public function setChunk(int $chunkX, int $chunkZ, ?Chunk $chunk, bool $deleteEntitiesAndTiles = true) : void{
		if($chunk === null){
			return;
		}

		$chunk->setX($chunkX);
		$chunk->setZ($chunkZ);

		$chunkHash = World::chunkHash($chunkX, $chunkZ);
		$oldChunk = $this->getChunk($chunkX, $chunkZ, false);
		if($oldChunk !== null and $oldChunk !== $chunk){
			if($deleteEntitiesAndTiles){
				foreach($oldChunk->getEntities() as $player){
					if(!($player instanceof Player)){
						continue;
					}
					$chunk->addEntity($player);
					$oldChunk->removeEntity($player);
					$player->chunk = $chunk;
				}
				//TODO: this causes chunkloaders to receive false "unloaded" notifications
				$this->unloadChunk($chunkX, $chunkZ, false, false);
			}else{
				foreach($oldChunk->getEntities() as $entity){
					$chunk->addEntity($entity);
					$oldChunk->removeEntity($entity);
					$entity->chunk = $chunk;
				}

				foreach($oldChunk->getTiles() as $tile){
					$chunk->addTile($tile);
					$oldChunk->removeTile($tile);
				}
			}
		}

		$this->chunks[$chunkHash] = $chunk;

		unset($this->blockCache[$chunkHash]);
		unset($this->changedBlocks[$chunkHash]);
		$chunk->setDirty();

		if(!$this->isChunkInUse($chunkX, $chunkZ)){
			$this->unloadChunkRequest($chunkX, $chunkZ);
		}

		foreach($this->getChunkListeners($chunkX, $chunkZ) as $listener){
			$listener->onChunkChanged($chunk);
		}
	}

	/**
	 * Gets the highest block Y value at a specific $x and $z
	 *
	 * @return int 0-255
	 */
	public function getHighestBlockAt(int $x, int $z) : int{
		return $this->getChunk($x >> 4, $z >> 4, true)->getHighestBlockAt($x & 0x0f, $z & 0x0f);
	}

	/**
	 * Returns whether the given position is in a loaded area of terrain.
	 */
	public function isInLoadedTerrain(Vector3 $pos) : bool{
		return $this->isChunkLoaded($pos->getFloorX() >> 4, $pos->getFloorZ() >> 4);
	}

	public function isChunkLoaded(int $x, int $z) : bool{
		return isset($this->chunks[World::chunkHash($x, $z)]);
	}

	public function isChunkGenerated(int $x, int $z) : bool{
		$chunk = $this->getChunk($x, $z);
		return $chunk !== null ? $chunk->isGenerated() : false;
	}

	public function isChunkPopulated(int $x, int $z) : bool{
		$chunk = $this->getChunk($x, $z);
		return $chunk !== null ? $chunk->isPopulated() : false;
	}

	/**
	 * Returns a Position pointing to the spawn
	 */
	public function getSpawnLocation() : Position{
		return Position::fromObject($this->provider->getWorldData()->getSpawn(), $this);
	}

	/**
	 * Sets the world spawn location
	 */
	public function setSpawnLocation(Vector3 $pos) : void{
		$previousSpawn = $this->getSpawnLocation();
		$this->provider->getWorldData()->setSpawn($pos);
		(new SpawnChangeEvent($this, $previousSpawn))->call();
	}

	/**
	 * @throws \InvalidArgumentException
	 */
	public function addEntity(Entity $entity) : void{
		if($entity->isClosed()){
			throw new \InvalidArgumentException("Attempted to add a garbage closed Entity to world");
		}
		if($entity->getWorld() !== $this){
			throw new \InvalidArgumentException("Invalid Entity world");
		}

		if($entity instanceof Player){
			$this->players[$entity->getId()] = $entity;
		}
		$this->entities[$entity->getId()] = $entity;
	}

	/**
	 * Removes the entity from the world index
	 *
	 * @throws \InvalidArgumentException
	 */
	public function removeEntity(Entity $entity) : void{
		if($entity->getWorld() !== $this){
			throw new \InvalidArgumentException("Invalid Entity world");
		}

		if($entity instanceof Player){
			unset($this->players[$entity->getId()]);
			$this->checkSleep();
		}

		unset($this->entities[$entity->getId()]);
		unset($this->updateEntities[$entity->getId()]);
	}

	/**
	 * @throws \InvalidArgumentException
	 */
	public function addTile(Tile $tile) : void{
		if($tile->isClosed()){
			throw new \InvalidArgumentException("Attempted to add a garbage closed Tile to world");
		}
		$pos = $tile->getPos();
		if(!$pos->isValid() || $pos->getWorld() !== $this){
			throw new \InvalidArgumentException("Invalid Tile world");
		}

		$chunkX = $pos->getFloorX() >> 4;
		$chunkZ = $pos->getFloorZ() >> 4;

		if(isset($this->chunks[$hash = World::chunkHash($chunkX, $chunkZ)])){
			$this->chunks[$hash]->addTile($tile);
		}else{
			throw new \InvalidStateException("Attempted to create tile " . get_class($tile) . " in unloaded chunk $chunkX $chunkZ");
		}

		//delegate tile ticking to the corresponding block
		$this->scheduleDelayedBlockUpdate($pos->asVector3(), 1);
	}

	/**
	 * @throws \InvalidArgumentException
	 */
	public function removeTile(Tile $tile) : void{
		$pos = $tile->getPos();
		if(!$pos->isValid() || $pos->getWorld() !== $this){
			throw new \InvalidArgumentException("Invalid Tile world");
		}

		$chunkX = $pos->getFloorX() >> 4;
		$chunkZ = $pos->getFloorZ() >> 4;

		if(isset($this->chunks[$hash = World::chunkHash($chunkX, $chunkZ)])){
			$this->chunks[$hash]->removeTile($tile);
		}
		foreach($this->getChunkListeners($chunkX, $chunkZ) as $listener){
			$listener->onBlockChanged($pos->asVector3());
		}
	}

	public function isChunkInUse(int $x, int $z) : bool{
		return isset($this->chunkLoaders[$index = World::chunkHash($x, $z)]) and count($this->chunkLoaders[$index]) > 0;
	}

	/**
	 * Attempts to load a chunk from the world provider (if not already loaded).
	 *
	 * @param bool $create Whether to create an empty chunk to load if the chunk cannot be loaded from disk.
	 *
	 * @return bool if loading the chunk was successful
	 *
	 * @throws \InvalidStateException
	 */
	public function loadChunk(int $x, int $z, bool $create = true) : bool{
		if(isset($this->chunks[$chunkHash = World::chunkHash($x, $z)])){
			return true;
		}

		$this->timings->syncChunkLoadTimer->startTiming();

		$this->cancelUnloadChunkRequest($x, $z);

		$this->timings->syncChunkLoadDataTimer->startTiming();

		$chunk = null;

		try{
			$chunk = $this->provider->loadChunk($x, $z);
		}catch(CorruptedChunkException $e){
			$this->logger->critical("Failed to load chunk x=$x z=$z: " . $e->getMessage());
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

		(new ChunkLoadEvent($this, $chunk, !$chunk->isGenerated()))->call();

		if(!$chunk->isLightPopulated() and $chunk->isPopulated()){
			$this->getServer()->getAsyncPool()->submitTask(new LightPopulationTask($this, $chunk));
		}

		if(!$this->isChunkInUse($x, $z)){
			$this->logger->debug("Newly loaded chunk $x $z has no loaders registered, will be unloaded at next available opportunity");
			$this->unloadChunkRequest($x, $z);
		}
		foreach($this->getChunkListeners($x, $z) as $listener){
			$listener->onChunkLoaded($chunk);
		}

		$this->timings->syncChunkLoadTimer->stopTiming();

		return true;
	}

	private function queueUnloadChunk(int $x, int $z) : void{
		$this->unloadQueue[World::chunkHash($x, $z)] = microtime(true);
	}

	public function unloadChunkRequest(int $x, int $z, bool $safe = true) : bool{
		if(($safe and $this->isChunkInUse($x, $z)) or $this->isSpawnChunk($x, $z)){
			return false;
		}

		$this->queueUnloadChunk($x, $z);

		return true;
	}

	public function cancelUnloadChunkRequest(int $x, int $z) : void{
		unset($this->unloadQueue[World::chunkHash($x, $z)]);
	}

	public function unloadChunk(int $x, int $z, bool $safe = true, bool $trySave = true) : bool{
		if($safe and $this->isChunkInUse($x, $z)){
			return false;
		}

		if(!$this->isChunkLoaded($x, $z)){
			return true;
		}

		$this->timings->doChunkUnload->startTiming();

		$chunkHash = World::chunkHash($x, $z);

		$chunk = $this->chunks[$chunkHash] ?? null;

		if($chunk !== null){
			$ev = new ChunkUnloadEvent($this, $chunk);
			$ev->call();
			if($ev->isCancelled()){
				$this->timings->doChunkUnload->stopTiming();

				return false;
			}

			if($trySave and $this->getAutoSave() and $chunk->isGenerated() and $chunk->isDirty()){
				$this->timings->syncChunkSaveTimer->startTiming();
				try{
					$this->provider->saveChunk($chunk);
				}finally{
					$this->timings->syncChunkSaveTimer->stopTiming();
				}
			}

			foreach($this->getChunkListeners($x, $z) as $listener){
				$listener->onChunkUnloaded($chunk);
			}

			$chunk->onUnload();
		}

		unset($this->chunks[$chunkHash]);
		unset($this->blockCache[$chunkHash]);
		unset($this->changedBlocks[$chunkHash]);

		$this->timings->doChunkUnload->stopTiming();

		return true;
	}

	/**
	 * Returns whether the chunk at the specified coordinates is a spawn chunk
	 */
	public function isSpawnChunk(int $X, int $Z) : bool{
		$spawn = $this->getSpawnLocation();
		$spawnX = $spawn->x >> 4;
		$spawnZ = $spawn->z >> 4;

		return abs($X - $spawnX) <= 1 and abs($Z - $spawnZ) <= 1;
	}

	public function getSafeSpawn(?Vector3 $spawn = null) : Position{
		if(!($spawn instanceof Vector3) or $spawn->y < 1){
			$spawn = $this->getSpawnLocation();
		}

		$max = $this->worldHeight;
		$v = $spawn->floor();
		$chunk = $this->getChunkAtPosition($v, false);
		$x = (int) $v->x;
		$y = $v->y;
		$z = (int) $v->z;
		if($chunk !== null and $chunk->isGenerated()){
			$y = (int) min($max - 2, $v->y);
			$wasAir = $this->getBlockAt($x, $y - 1, $z)->getId() === BlockLegacyIds::AIR; //TODO: bad hack, clean up
			for(; $y > 0; --$y){
				if($this->getBlockAt($x, $y, $z)->isFullCube()){
					if($wasAir){
						$y++;
						break;
					}
				}else{
					$wasAir = true;
				}
			}

			for(; $y >= 0 and $y < $max; ++$y){
				if(!$this->getBlockAt($x, $y + 1, $z)->isFullCube()){
					if(!$this->getBlockAt($x, $y, $z)->isFullCube()){
						return new Position($spawn->x, $y === (int) $spawn->y ? $spawn->y : $y, $spawn->z, $this);
					}
				}else{
					++$y;
				}
			}
		}

		return new Position($spawn->x, $y, $spawn->z, $this);
	}

	/**
	 * Gets the current time
	 */
	public function getTime() : int{
		return $this->time;
	}

	/**
	 * Returns the current time of day
	 */
	public function getTimeOfDay() : int{
		return $this->time % self::TIME_FULL;
	}

	/**
	 * Returns the World display name.
	 * WARNING: This is NOT guaranteed to be unique. Multiple worlds at runtime may share the same display name.
	 */
	public function getDisplayName() : string{
		return $this->displayName;
	}

	/**
	 * Returns the World folder name. This will not change at runtime and will be unique to a world per runtime.
	 */
	public function getFolderName() : string{
		return $this->folderName;
	}

	/**
	 * Sets the current time on the world
	 */
	public function setTime(int $time) : void{
		$this->time = $time;
		$this->sendTime();
	}

	/**
	 * Stops the time for the world, will not save the lock state to disk
	 */
	public function stopTime() : void{
		$this->stopTime = true;
		$this->sendTime();
	}

	/**
	 * Start the time again, if it was stopped
	 */
	public function startTime() : void{
		$this->stopTime = false;
		$this->sendTime();
	}

	/**
	 * Gets the world seed
	 */
	public function getSeed() : int{
		return $this->provider->getWorldData()->getSeed();
	}

	public function getWorldHeight() : int{
		return $this->worldHeight;
	}

	public function getDifficulty() : int{
		return $this->provider->getWorldData()->getDifficulty();
	}

	public function setDifficulty(int $difficulty) : void{
		if($difficulty < 0 or $difficulty > 3){
			throw new \InvalidArgumentException("Invalid difficulty level $difficulty");
		}
		$this->provider->getWorldData()->setDifficulty($difficulty);

		foreach($this->players as $player){
			$player->getNetworkSession()->syncWorldDifficulty($this->getDifficulty());
		}
	}

	public function populateChunk(int $x, int $z, bool $force = false) : bool{
		if(isset($this->chunkPopulationQueue[$index = World::chunkHash($x, $z)]) or (count($this->chunkPopulationQueue) >= $this->chunkPopulationQueueSize and !$force)){
			return false;
		}
		for($xx = -1; $xx <= 1; ++$xx){
			for($zz = -1; $zz <= 1; ++$zz){
				if($this->isChunkLocked($x + $xx, $z + $zz)){
					return false;
				}
			}
		}

		$chunk = $this->getChunk($x, $z, true);
		if(!$chunk->isPopulated()){
			Timings::$populationTimer->startTiming();

			$this->chunkPopulationQueue[$index] = true;
			for($xx = -1; $xx <= 1; ++$xx){
				for($zz = -1; $zz <= 1; ++$zz){
					$this->lockChunk($x + $xx, $z + $zz);
				}
			}

			$task = new PopulationTask($this, $chunk);
			$workerId = $this->server->getAsyncPool()->selectWorker();
			if(!isset($this->generatorRegisteredWorkers[$workerId])){
				$this->registerGeneratorToWorker($workerId);
			}
			$this->server->getAsyncPool()->submitTaskToWorker($task, $workerId);

			Timings::$populationTimer->stopTiming();
			return false;
		}

		return true;
	}

	public function doChunkGarbageCollection() : void{
		$this->timings->doChunkGC->startTiming();

		foreach($this->chunks as $index => $chunk){
			if(!isset($this->unloadQueue[$index])){
				World::getXZ($index, $X, $Z);
				if(!$this->isSpawnChunk($X, $Z)){
					$this->unloadChunkRequest($X, $Z, true);
				}
			}
			$chunk->collectGarbage();
		}

		$this->provider->doGarbageCollection();

		$this->timings->doChunkGC->stopTiming();
	}

	public function unloadChunks(bool $force = false) : void{
		if(count($this->unloadQueue) > 0){
			$maxUnload = 96;
			$now = microtime(true);
			foreach($this->unloadQueue as $index => $time){
				World::getXZ($index, $X, $Z);

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
}
