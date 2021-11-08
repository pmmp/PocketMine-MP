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
use pocketmine\block\tile\TileFactory;
use pocketmine\block\UnknownBlock;
use pocketmine\block\VanillaBlocks;
use pocketmine\data\bedrock\BiomeIds;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityFactory;
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
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\nbt\NbtDataException;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\convert\RuntimeBlockMapping;
use pocketmine\network\mcpe\protocol\BlockActorDataPacket;
use pocketmine\network\mcpe\protocol\ClientboundPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\player\Player;
use pocketmine\promise\Promise;
use pocketmine\promise\PromiseResolver;
use pocketmine\scheduler\AsyncPool;
use pocketmine\Server;
use pocketmine\timings\Timings;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Limits;
use pocketmine\utils\ReversePriorityQueue;
use pocketmine\world\biome\Biome;
use pocketmine\world\biome\BiomeRegistry;
use pocketmine\world\format\Chunk;
use pocketmine\world\format\io\ChunkData;
use pocketmine\world\format\io\exception\CorruptedChunkException;
use pocketmine\world\format\io\WritableWorldProvider;
use pocketmine\world\format\LightArray;
use pocketmine\world\format\SubChunk;
use pocketmine\world\generator\GeneratorManager;
use pocketmine\world\generator\GeneratorRegisterTask;
use pocketmine\world\generator\GeneratorUnregisterTask;
use pocketmine\world\generator\PopulationTask;
use pocketmine\world\light\BlockLightUpdate;
use pocketmine\world\light\LightPopulationTask;
use pocketmine\world\light\SkyLightUpdate;
use pocketmine\world\particle\BlockBreakParticle;
use pocketmine\world\particle\Particle;
use pocketmine\world\sound\BlockPlaceSound;
use pocketmine\world\sound\Sound;
use pocketmine\world\utils\SubChunkExplorer;
use function abs;
use function array_fill_keys;
use function array_filter;
use function array_key_exists;
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
use function is_object;
use function lcg_value;
use function max;
use function microtime;
use function min;
use function morton2d_decode;
use function morton2d_encode;
use function morton3d_decode;
use function morton3d_encode;
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

	public const Y_MAX = 256;
	public const Y_MIN = 0;

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
	/**
	 * @var Vector3[]
	 * @phpstan-var array<int, Vector3>
	 */
	private $entityLastKnownPositions = [];

	/**
	 * @var Entity[][]
	 * @phpstan-var array<int, array<int, Entity>>
	 */
	private array $entitiesByChunk = [];

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
	private $minY;
	/** @var int */
	private $maxY;

	/** @var TickingChunkLoader[] */
	private $tickingLoaders = [];
	/** @var int[] */
	private $tickingLoaderCounter = [];
	/** @var ChunkLoader[][] */
	private $chunkLoaders = [];

	/** @var ChunkListener[][] */
	private $chunkListeners = [];
	/** @var Player[][] */
	private $playerChunkListeners = [];

	/** @var ClientboundPacket[][] */
	private $packetBuffersByChunk = [];

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
	private $activeChunkPopulationTasks = [];
	/** @var ChunkLockId[] */
	private $chunkLock = [];
	/** @var int */
	private $maxConcurrentChunkPopulationTasks = 2;
	/**
	 * @var PromiseResolver[] chunkHash => promise
	 * @phpstan-var array<int, PromiseResolver<Chunk>>
	 */
	private array $chunkPopulationRequestMap = [];
	/**
	 * @var \SplQueue (queue of chunkHashes)
	 * @phpstan-var \SplQueue<int>
	 */
	private \SplQueue $chunkPopulationRequestQueue;
	/**
	 * @var true[] chunkHash => dummy
	 * @phpstan-var array<int, true>
	 */
	private array $chunkPopulationRequestQueueIndex = [];

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

	/**
	 * @var string
	 * @phpstan-var class-string<\pocketmine\world\generator\Generator>
	 */
	private $generator;

	/** @var bool */
	private $unloaded = false;
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

	/** @var AsyncPool */
	private $workerPool;

	public static function chunkHash(int $x, int $z) : int{
		return morton2d_encode($x, $z);
	}

	private const MORTON3D_BIT_SIZE = 21;
	private const BLOCKHASH_Y_BITS = 9;
	private const BLOCKHASH_Y_PADDING = 128; //size (in blocks) of padding after both boundaries of the Y axis
	private const BLOCKHASH_Y_OFFSET = self::BLOCKHASH_Y_PADDING - self::Y_MIN;
	private const BLOCKHASH_Y_MASK = (1 << self::BLOCKHASH_Y_BITS) - 1;
	private const BLOCKHASH_XZ_MASK = (1 << self::MORTON3D_BIT_SIZE) - 1;
	private const BLOCKHASH_XZ_EXTRA_BITS = (self::MORTON3D_BIT_SIZE - self::BLOCKHASH_Y_BITS) >> 1;
	private const BLOCKHASH_XZ_EXTRA_MASK = (1 << self::BLOCKHASH_XZ_EXTRA_BITS) - 1;
	private const BLOCKHASH_XZ_SIGN_SHIFT = 64 - self::MORTON3D_BIT_SIZE - self::BLOCKHASH_XZ_EXTRA_BITS;
	private const BLOCKHASH_X_SHIFT = self::BLOCKHASH_Y_BITS;
	private const BLOCKHASH_Z_SHIFT = self::BLOCKHASH_X_SHIFT + self::BLOCKHASH_XZ_EXTRA_BITS;

	public static function blockHash(int $x, int $y, int $z) : int{
		$shiftedY = $y + self::BLOCKHASH_Y_OFFSET;
		if(($shiftedY & (~0 << self::BLOCKHASH_Y_BITS)) !== 0){
			throw new \InvalidArgumentException("Y coordinate $y is out of range!");
		}
		//morton3d gives us 21 bits on each axis, but the Y axis only requires 9
		//so we use the extra space on Y (12 bits) and add 6 extra bits from X and Z instead.
		//if we ever need more space for Y (e.g. due to expansion), take bits from X/Z to compensate.
		return morton3d_encode(
			$x & self::BLOCKHASH_XZ_MASK,
			($shiftedY /* & self::BLOCKHASH_Y_MASK */) |
				((($x >> self::MORTON3D_BIT_SIZE) & self::BLOCKHASH_XZ_EXTRA_MASK) << self::BLOCKHASH_X_SHIFT) |
				((($z >> self::MORTON3D_BIT_SIZE) & self::BLOCKHASH_XZ_EXTRA_MASK) << self::BLOCKHASH_Z_SHIFT),
			$z & self::BLOCKHASH_XZ_MASK
		);
	}

	/**
	 * Computes a small index relative to chunk base from the given coordinates.
	 */
	public static function chunkBlockHash(int $x, int $y, int $z) : int{
		return morton3d_encode($x, $y, $z);
	}

	public static function getBlockXYZ(int $hash, ?int &$x, ?int &$y, ?int &$z) : void{
		[$baseX, $baseY, $baseZ] = morton3d_decode($hash);

		$extraX = ((($baseY >> self::BLOCKHASH_X_SHIFT) & self::BLOCKHASH_XZ_EXTRA_MASK) << self::MORTON3D_BIT_SIZE);
		$extraZ = ((($baseY >> self::BLOCKHASH_Z_SHIFT) & self::BLOCKHASH_XZ_EXTRA_MASK) << self::MORTON3D_BIT_SIZE);

		$x = (($baseX & self::BLOCKHASH_XZ_MASK) | $extraX) << self::BLOCKHASH_XZ_SIGN_SHIFT >> self::BLOCKHASH_XZ_SIGN_SHIFT;
		$y = ($baseY & self::BLOCKHASH_Y_MASK) - self::BLOCKHASH_Y_OFFSET;
		$z = (($baseZ & self::BLOCKHASH_XZ_MASK) | $extraZ) << self::BLOCKHASH_XZ_SIGN_SHIFT >> self::BLOCKHASH_XZ_SIGN_SHIFT;
	}

	public static function getXZ(int $hash, ?int &$x, ?int &$z) : void{
		[$x, $z] = morton2d_decode($hash);
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
	public function __construct(Server $server, string $name, WritableWorldProvider $provider, AsyncPool $workerPool){
		$this->worldId = self::$worldIdCounter++;
		$this->server = $server;

		$this->provider = $provider;
		$this->workerPool = $workerPool;

		$this->displayName = $this->provider->getWorldData()->getName();
		$this->logger = new \PrefixedLogger($server->getLogger(), "World: $this->displayName");

		$this->minY = $this->provider->getWorldMinY();
		$this->maxY = $this->provider->getWorldMaxY();

		$this->server->getLogger()->info($this->server->getLanguage()->translate(KnownTranslationFactory::pocketmine_level_preparing($this->displayName)));
		$generator = GeneratorManager::getInstance()->getGenerator($this->provider->getWorldData()->getGenerator()) ??
			throw new AssumptionFailedError("WorldManager should already have checked that the generator exists");
		$generator->validateGeneratorOptions($this->provider->getWorldData()->getGeneratorOptions());
		$this->generator = $generator->getGeneratorClass();
		$this->chunkPopulationRequestQueue = new \SplQueue();
		$this->addOnUnloadCallback(function() : void{
			$this->logger->debug("Cancelling unfulfilled generation requests");

			foreach($this->chunkPopulationRequestMap as $chunkHash => $promise){
				$promise->reject();
				unset($this->chunkPopulationRequestMap[$chunkHash]);
			}
			if(count($this->chunkPopulationRequestMap) !== 0){
				//TODO: this might actually get hit because generation rejection callbacks might try to schedule new
				//requests, and we can't prevent that right now because there's no way to detect "unloading" state
				throw new AssumptionFailedError("New generation requests scheduled during unload");
			}
		});

		$this->folderName = $name;

		$this->scheduledBlockUpdateQueue = new ReversePriorityQueue();
		$this->scheduledBlockUpdateQueue->setExtractFlags(\SplPriorityQueue::EXTR_BOTH);

		$this->neighbourBlockUpdateQueue = new \SplQueue();

		$this->time = $this->provider->getWorldData()->getTime();

		$cfg = $this->server->getConfigGroup();
		$this->chunkTickRadius = min($this->server->getViewDistance(), max(1, $cfg->getPropertyInt("chunk-ticking.tick-radius", 4)));
		$this->chunksPerTick = $cfg->getPropertyInt("chunk-ticking.per-tick", 40);
		$this->tickedBlocksPerSubchunkPerTick = $cfg->getPropertyInt("chunk-ticking.blocks-per-subchunk-per-tick", self::DEFAULT_TICKED_BLOCKS_PER_SUBCHUNK_PER_TICK);
		$this->maxConcurrentChunkPopulationTasks = $cfg->getPropertyInt("chunk-generation.population-queue-size", 2);

		$dontTickBlocks = array_fill_keys($cfg->getProperty("chunk-ticking.disable-block-ticking", []), true);

		foreach(BlockFactory::getInstance()->getAllKnownStates() as $state){
			if(!isset($dontTickBlocks[$state->getId()]) and $state->ticksRandomly()){
				$this->randomTickBlocks[$state->getFullId()] = true;
			}
		}

		$this->timings = new WorldTimings($this);

		$this->workerPool->addWorkerStartHook($workerStartHook = function(int $workerId) : void{
			if(array_key_exists($workerId, $this->generatorRegisteredWorkers)){
				$this->logger->debug("Worker $workerId with previously registered generator restarted, flagging as unregistered");
				unset($this->generatorRegisteredWorkers[$workerId]);
			}
		});
		$workerPool = $this->workerPool;
		$this->addOnUnloadCallback(static function() use ($workerPool, $workerStartHook) : void{
			$workerPool->removeWorkerStartHook($workerStartHook);
		});
	}

	public function getTickRateTime() : float{
		return $this->tickRateTime;
	}

	public function registerGeneratorToWorker(int $worker) : void{
		$this->logger->debug("Registering generator on worker $worker");
		$this->workerPool->submitTaskToWorker(new GeneratorRegisterTask($this, $this->generator, $this->provider->getWorldData()->getGeneratorOptions()), $worker);
		$this->generatorRegisteredWorkers[$worker] = true;
	}

	public function unregisterGenerator() : void{
		foreach($this->workerPool->getRunningWorkers() as $i){
			if(isset($this->generatorRegisteredWorkers[$i])){
				$this->workerPool->submitTaskToWorker(new GeneratorUnregisterTask($this), $i);
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

	public function isLoaded() : bool{
		return !$this->unloaded;
	}

	/**
	 * @internal
	 */
	public function onUnload() : void{
		if($this->unloaded){
			throw new \LogicException("Tried to close a world which is already closed");
		}

		foreach($this->unloadCallbacks as $callback){
			$callback();
		}
		$this->unloadCallbacks = [];

		foreach($this->chunks as $chunkHash => $chunk){
			self::getXZ($chunkHash, $chunkX, $chunkZ);
			$this->unloadChunk($chunkX, $chunkZ, false);
		}
		foreach($this->entitiesByChunk as $chunkHash => $entities){
			self::getXZ($chunkHash, $chunkX, $chunkZ);

			$leakedEntities = 0;
			foreach($entities as $entity){
				if(!$entity->isFlaggedForDespawn()){
					$leakedEntities++;
				}
				$entity->close();
			}
			if($leakedEntities !== 0){
				$this->logger->warning("$leakedEntities leaked entities found in ungenerated chunk $chunkX $chunkZ during unload, they won't be saved!");
			}
		}

		$this->save();

		$this->unregisterGenerator();

		$this->provider->close();
		$this->blockCache = [];

		$this->unloaded = true;
	}

	/** @phpstan-param \Closure() : void $callback */
	public function addOnUnloadCallback(\Closure $callback) : void{
		$this->unloadCallbacks[spl_object_id($callback)] = $callback;
	}

	/** @phpstan-param \Closure() : void $callback */
	public function removeOnUnloadCallback(\Closure $callback) : void{
		unset($this->unloadCallbacks[spl_object_id($callback)]);
	}

	/**
	 * @param Player[]|null $players
	 */
	public function addSound(Vector3 $pos, Sound $sound, ?array $players = null) : void{
		$pk = $sound->encode($pos);
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
		return $this->getChunkPlayers($pos->getFloorX() >> Chunk::COORD_BIT_SIZE, $pos->getFloorZ() >> Chunk::COORD_BIT_SIZE);
	}

	/**
	 * Broadcasts a packet to every player who has the target position within their view distance.
	 */
	public function broadcastPacketToViewers(Vector3 $pos, ClientboundPacket $packet) : void{
		$this->broadcastPacketToPlayersUsingChunk($pos->getFloorX() >> Chunk::COORD_BIT_SIZE, $pos->getFloorZ() >> Chunk::COORD_BIT_SIZE, $packet);
	}

	private function broadcastPacketToPlayersUsingChunk(int $chunkX, int $chunkZ, ClientboundPacket $packet) : void{
		if(!isset($this->packetBuffersByChunk[$index = World::chunkHash($chunkX, $chunkZ)])){
			$this->packetBuffersByChunk[$index] = [$packet];
		}else{
			$this->packetBuffersByChunk[$index][] = $packet;
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

		if($loader instanceof TickingChunkLoader){
			if(!isset($this->tickingLoaders[$loaderId])){
				$this->tickingLoaderCounter[$loaderId] = 1;
				$this->tickingLoaders[$loaderId] = $loader;
			}else{
				++$this->tickingLoaderCounter[$loaderId];
			}
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
				if(isset($this->chunkPopulationRequestMap[$chunkHash]) && !isset($this->activeChunkPopulationTasks[$chunkHash])){
					$this->chunkPopulationRequestMap[$chunkHash]->reject();
					unset($this->chunkPopulationRequestMap[$chunkHash]);
				}
			}

			if(isset($this->tickingLoaderCounter[$loaderId]) && --$this->tickingLoaderCounter[$loaderId] === 0){
				unset($this->tickingLoaderCounter[$loaderId]);
				unset($this->tickingLoaders[$loaderId]);
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
		foreach($this->chunkListeners as $hash => $listeners){
			World::getXZ($hash, $chunkX, $chunkZ);
			$this->unregisterChunkListener($listener, $chunkX, $chunkZ);
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
		if($this->unloaded){
			throw new \LogicException("Attempted to tick a world which has been closed");
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
		$this->timings->scheduledBlockUpdates->startTiming();

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
			unset($this->neighbourBlockUpdateQueueIndex[$index]);
			World::getBlockXYZ($index, $x, $y, $z);
			if(!$this->isChunkLoaded($x >> Chunk::COORD_BIT_SIZE, $z >> Chunk::COORD_BIT_SIZE)){
				continue;
			}

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
		}

		$this->timings->scheduledBlockUpdates->stopTiming();

		$this->timings->entityTick->startTiming();
		//Update entities that need update
		Timings::$tickEntity->startTiming();
		foreach($this->updateEntities as $id => $entity){
			if($entity->isClosed() or !$entity->onUpdate($currentTick)){
				unset($this->updateEntities[$id]);
			}
			if($entity->isFlaggedForDespawn()){
				$entity->close();
			}
		}
		Timings::$tickEntity->stopTiming();
		$this->timings->entityTick->stopTiming();

		$this->timings->randomChunkUpdates->startTiming();
		$this->tickChunks();
		$this->timings->randomChunkUpdates->stopTiming();

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
							$p->onChunkChanged($chunkX, $chunkZ, $chunk);
						}
					}else{
						foreach($this->createBlockUpdatePackets($blocks) as $packet){
							$this->broadcastPacketToPlayersUsingChunk($chunkX, $chunkZ, $packet);
						}
					}
				}
			}

			$this->changedBlocks = [];

		}

		if($this->sleepTicks > 0 and --$this->sleepTicks <= 0){
			$this->checkSleep();
		}

		foreach($this->packetBuffersByChunk as $index => $entries){
			World::getXZ($index, $chunkX, $chunkZ);
			$chunkPlayers = $this->getChunkPlayers($chunkX, $chunkZ);
			if(count($chunkPlayers) > 0){
				$this->server->broadcastPackets($chunkPlayers, $entries);
			}
		}

		$this->packetBuffersByChunk = [];
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
	 * @param Vector3[] $blocks
	 *
	 * @return ClientboundPacket[]
	 */
	public function createBlockUpdatePackets(array $blocks) : array{
		$packets = [];

		foreach($blocks as $b){
			if(!($b instanceof Vector3)){
				throw new \TypeError("Expected Vector3 in blocks array, got " . (is_object($b) ? get_class($b) : gettype($b)));
			}

			$fullBlock = $this->getBlockAt($b->x, $b->y, $b->z);
			$blockPosition = BlockPosition::fromVector3($b);
			$packets[] = UpdateBlockPacket::create(
				$blockPosition,
				RuntimeBlockMapping::getInstance()->toRuntimeId($fullBlock->getFullId()),
				UpdateBlockPacket::FLAG_NETWORK,
				UpdateBlockPacket::DATA_LAYER_NORMAL
			);

			$tile = $this->getTileAt($b->x, $b->y, $b->z);
			if($tile instanceof Spawnable){
				$packets[] = BlockActorDataPacket::create($blockPosition, $tile->getSerializedSpawnCompound());
			}
		}

		return $packets;
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
		if($this->chunksPerTick <= 0 or count($this->tickingLoaders) === 0){
			return;
		}

		$this->timings->randomChunkUpdatesChunkSelection->startTiming();

		/** @var bool[] $chunkTickList chunkhash => dummy */
		$chunkTickList = [];

		$chunksPerLoader = min(200, max(1, (int) ((($this->chunksPerTick - count($this->tickingLoaders)) / count($this->tickingLoaders)) + 0.5)));
		$randRange = 3 + $chunksPerLoader / 30;
		$randRange = (int) ($randRange > $this->chunkTickRadius ? $this->chunkTickRadius : $randRange);

		foreach($this->tickingLoaders as $loader){
			$chunkX = (int) floor($loader->getX()) >> Chunk::COORD_BIT_SIZE;
			$chunkZ = (int) floor($loader->getZ()) >> Chunk::COORD_BIT_SIZE;

			for($chunk = 0; $chunk < $chunksPerLoader; ++$chunk){
				$dx = mt_rand(-$randRange, $randRange);
				$dz = mt_rand(-$randRange, $randRange);
				$hash = World::chunkHash($dx + $chunkX, $dz + $chunkZ);
				if(!isset($chunkTickList[$hash]) and isset($this->chunks[$hash]) and $this->isChunkTickable($dx + $chunkX, $dz + $chunkZ)){
					$chunkTickList[$hash] = true;
				}
			}
		}

		$this->timings->randomChunkUpdatesChunkSelection->stopTiming();

		foreach($chunkTickList as $index => $_){
			World::getXZ($index, $chunkX, $chunkZ);

			$this->tickChunk($chunkX, $chunkZ);
		}
	}

	private function isChunkTickable(int $chunkX, int $chunkZ) : bool{
		for($cx = -1; $cx <= 1; ++$cx){
			for($cz = -1; $cz <= 1; ++$cz){
				if($this->isChunkLocked($chunkX + $cx, $chunkZ + $cz)){
					return false;
				}
				$adjacentChunk = $this->getChunk($chunkX + $cx, $chunkZ + $cz);
				if($adjacentChunk === null || !$adjacentChunk->isPopulated()){
					return false;
				}
				$lightPopulatedState = $adjacentChunk->isLightPopulated();
				if($lightPopulatedState !== true){
					if($lightPopulatedState === false){
						$this->orderLightPopulation($chunkX + $cx, $chunkZ + $cz);
					}
					return false;
				}
			}
		}

		return true;
	}

	private function orderLightPopulation(int $chunkX, int $chunkZ) : void{
		$chunkHash = World::chunkHash($chunkX, $chunkZ);
		$lightPopulatedState = $this->chunks[$chunkHash]->isLightPopulated();
		if($lightPopulatedState === false){
			$this->chunks[$chunkHash]->setLightPopulated(null);

			$this->workerPool->submitTask(new LightPopulationTask(
				$this->chunks[$chunkHash],
				function(array $blockLight, array $skyLight, array $heightMap) use ($chunkX, $chunkZ) : void{
					/**
					 * TODO: phpstan can't infer these types yet :(
					 * @phpstan-var array<int, LightArray> $blockLight
					 * @phpstan-var array<int, LightArray> $skyLight
					 * @phpstan-var array<int, int>        $heightMap
					 */
					if($this->unloaded || ($chunk = $this->getChunk($chunkX, $chunkZ)) === null || $chunk->isLightPopulated() === true){
						return;
					}
					//TODO: calculated light information might not be valid if the terrain changed during light calculation

					$chunk->setHeightMapArray($heightMap);
					foreach($blockLight as $y => $lightArray){
						$chunk->getSubChunk($y)->setBlockLightArray($lightArray);
					}
					foreach($skyLight as $y => $lightArray){
						$chunk->getSubChunk($y)->setBlockSkyLightArray($lightArray);
					}
					$chunk->setLightPopulated(true);
				}
			));
		}
	}

	private function tickChunk(int $chunkX, int $chunkZ) : void{
		$chunk = $this->getChunk($chunkX, $chunkZ);
		if($chunk === null){
			throw new \InvalidArgumentException("Chunk is not loaded");
		}
		foreach($this->getChunkEntities($chunkX, $chunkZ) as $entity){
			$entity->onRandomUpdate();
		}

		foreach($chunk->getSubChunks() as $Y => $subChunk){
			if(!$subChunk->isEmptyFast()){
				$k = 0;
				for($i = 0; $i < $this->tickedBlocksPerSubchunkPerTick; ++$i){
					if(($i % 5) === 0){
						//60 bits will be used by 5 blocks (12 bits each)
						$k = mt_rand(0, (1 << 60) - 1);
					}
					$x = $k & SubChunk::COORD_MASK;
					$y = ($k >> SubChunk::COORD_BIT_SIZE) & SubChunk::COORD_MASK;
					$z = ($k >> (SubChunk::COORD_BIT_SIZE * 2)) & SubChunk::COORD_MASK;
					$k >>= (SubChunk::COORD_BIT_SIZE * 3);

					$state = $subChunk->getFullBlock($x, $y, $z);

					if(isset($this->randomTickBlocks[$state])){
						/** @var Block $block */
						$block = BlockFactory::getInstance()->fromFullBlock($state);
						$block->position($this, $chunkX * Chunk::EDGE_LENGTH + $x, ($Y << SubChunk::COORD_BIT_SIZE) + $y, $chunkZ * Chunk::EDGE_LENGTH + $z);
						$block->onRandomTick();
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
		$this->timings->syncChunkSave->startTiming();
		try{
			foreach($this->chunks as $chunkHash => $chunk){
				self::getXZ($chunkHash, $chunkX, $chunkZ);
				$this->provider->saveChunk($chunkX, $chunkZ, new ChunkData(
					$chunk,
					array_map(fn(Entity $e) => $e->saveNBT(), array_filter($this->getChunkEntities($chunkX, $chunkZ), fn(Entity $e) => $e->canSaveWithChunk())),
					array_map(fn(Tile $t) => $t->saveNBT(), $chunk->getTiles()),
				));
				$chunk->clearTerrainDirtyFlags();
			}
		}finally{
			$this->timings->syncChunkSave->stopTiming();
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
	 * Notify the blocks at and around the position that the block at the position may have changed.
	 * This will cause onNeighbourBlockUpdate() to be called for these blocks.
	 */
	public function notifyNeighbourBlockUpdate(Vector3 $pos) : void{
		$this->tryAddToNeighbourUpdateQueue($pos);
		foreach($pos->sides() as $side){
			$this->tryAddToNeighbourUpdateQueue($side);
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
	 * Returns the highest available level of any type of light at the given coordinates, adjusted for the current
	 * weather and time of day.
	 */
	public function getFullLight(Vector3 $pos) : int{
		return $this->getFullLightAt($pos->x, $pos->y, $pos->z);
	}

	/**
	 * Returns the highest available level of any type of light at the given coordinates, adjusted for the current
	 * weather and time of day.
	 */
	public function getFullLightAt(int $x, int $y, int $z) : int{
		$skyLight = $this->getRealBlockSkyLightAt($x, $y, $z);
		if($skyLight < 15){
			return max($skyLight, $this->getBlockLightAt($x, $y, $z));
		}else{
			return $skyLight;
		}
	}

	/**
	 * Returns the highest available level of any type of light at, or adjacent to, the given coordinates, adjusted for
	 * the current weather and time of day.
	 */
	public function getHighestAdjacentFullLightAt(int $x, int $y, int $z) : int{
		return $this->getHighestAdjacentLight($x, $y, $z, \Closure::fromCallable([$this, 'getFullLightAt']));
	}

	/**
	 * Returns the highest potential level of sky light at the target coordinates, regardless of the time of day or
	 * weather conditions.
	 * You usually don't want to use this for vanilla gameplay logic; prefer the real sky light instead.
	 * @see World::getRealBlockSkyLightAt()
	 *
	 * @return int 0-15
	 */
	public function getPotentialBlockSkyLightAt(int $x, int $y, int $z) : int{
		if(!$this->isInWorld($x, $y, $z)){
			return $y >= self::Y_MAX ? 15 : 0;
		}
		if(($chunk = $this->getChunk($x >> Chunk::COORD_BIT_SIZE, $z >> Chunk::COORD_BIT_SIZE)) !== null && $chunk->isLightPopulated() === true){
			return $chunk->getSubChunk($y >> Chunk::COORD_BIT_SIZE)->getBlockSkyLightArray()->get($x & SubChunk::COORD_MASK, $y & SubChunk::COORD_MASK, $z & SubChunk::COORD_MASK);
		}
		return 0; //TODO: this should probably throw instead (light not calculated yet)
	}

	/**
	 * Returns the sky light level at the specified coordinates, offset by the current time and weather.
	 *
	 * @return int 0-15
	 */
	public function getRealBlockSkyLightAt(int $x, int $y, int $z) : int{
		$light = $this->getPotentialBlockSkyLightAt($x, $y, $z) - $this->skyLightReduction;
		return $light < 0 ? 0 : $light;
	}

	/**
	 * Gets the raw block light level
	 *
	 * @return int 0-15
	 */
	public function getBlockLightAt(int $x, int $y, int $z) : int{
		if(!$this->isInWorld($x, $y, $z)){
			return 0;
		}
		if(($chunk = $this->getChunk($x >> Chunk::COORD_BIT_SIZE, $z >> Chunk::COORD_BIT_SIZE)) !== null && $chunk->isLightPopulated() === true){
			return $chunk->getSubChunk($y >> Chunk::COORD_BIT_SIZE)->getBlockLightArray()->get($x & SubChunk::COORD_MASK, $y & SubChunk::COORD_MASK, $z & SubChunk::COORD_MASK);
		}
		return 0; //TODO: this should probably throw instead (light not calculated yet)
	}

	public function updateAllLight(int $x, int $y, int $z) : void{
		if(($chunk = $this->getChunk($x >> Chunk::COORD_BIT_SIZE, $z >> Chunk::COORD_BIT_SIZE)) === null || $chunk->isLightPopulated() !== true){
			return;
		}

		$blockFactory = BlockFactory::getInstance();
		$this->timings->doBlockSkyLightUpdates->startTiming();
		if($this->skyLightUpdate === null){
			$this->skyLightUpdate = new SkyLightUpdate(new SubChunkExplorer($this), $blockFactory->lightFilter, $blockFactory->blocksDirectSkyLight);
		}
		$this->skyLightUpdate->recalculateNode($x, $y, $z);
		$this->timings->doBlockSkyLightUpdates->stopTiming();

		$this->timings->doBlockLightUpdates->startTiming();
		if($this->blockLightUpdate === null){
			$this->blockLightUpdate = new BlockLightUpdate(new SubChunkExplorer($this), $blockFactory->lightFilter, $blockFactory->light);
		}
		$this->blockLightUpdate->recalculateNode($x, $y, $z);
		$this->timings->doBlockLightUpdates->stopTiming();
	}

	/**
	 * @phpstan-param \Closure(int $x, int $y, int $z) : int $lightGetter
	 */
	private function getHighestAdjacentLight(int $x, int $y, int $z, \Closure $lightGetter) : int{
		$max = 0;
		foreach([
			[$x + 1, $y, $z],
			[$x - 1, $y, $z],
			[$x, $y + 1, $z],
			[$x, $y - 1, $z],
			[$x, $y, $z + 1],
			[$x, $y, $z - 1]
		] as [$x1, $y1, $z1]){
			if(
				!$this->isInWorld($x1, $y1, $z1) ||
				($chunk = $this->getChunk($x1 >> Chunk::COORD_BIT_SIZE, $z1 >> Chunk::COORD_BIT_SIZE)) === null ||
				$chunk->isLightPopulated() !== true
			){
				continue;
			}
			$max = max($max, $lightGetter($x1, $y1, $z1));
		}
		return $max;
	}

	/**
	 * Returns the highest potential level of sky light in the positions adjacent to the specified block coordinates.
	 */
	public function getHighestAdjacentPotentialBlockSkyLight(int $x, int $y, int $z) : int{
		return $this->getHighestAdjacentLight($x, $y, $z, \Closure::fromCallable([$this, 'getPotentialBlockSkyLightAt']));
	}

	/**
	 * Returns the highest block sky light available in the positions adjacent to the given coordinates, adjusted for
	 * the world's current time of day and weather conditions.
	 */
	public function getHighestAdjacentRealBlockSkyLight(int $x, int $y, int $z) : int{
		return $this->getHighestAdjacentPotentialBlockSkyLight($x, $y, $z) - $this->skyLightReduction;
	}

	/**
	 * Returns the highest block light level available in the positions adjacent to the specified block coordinates.
	 */
	public function getHighestAdjacentBlockLight(int $x, int $y, int $z) : int{
		return $this->getHighestAdjacentLight($x, $y, $z, \Closure::fromCallable([$this, 'getBlockLightAt']));
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

	public function isInWorld(int $x, int $y, int $z) : bool{
		return (
			$x <= Limits::INT32_MAX and $x >= Limits::INT32_MIN and
			$y < $this->maxY and $y >= $this->minY and
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
		$relativeBlockHash = null;
		$chunkHash = World::chunkHash($x >> Chunk::COORD_BIT_SIZE, $z >> Chunk::COORD_BIT_SIZE);

		if($this->isInWorld($x, $y, $z)){
			$relativeBlockHash = World::chunkBlockHash($x, $y, $z);

			if($cached and isset($this->blockCache[$chunkHash][$relativeBlockHash])){
				return $this->blockCache[$chunkHash][$relativeBlockHash];
			}

			$chunk = $this->chunks[$chunkHash] ?? null;
			if($chunk !== null){
				$block = BlockFactory::getInstance()->fromFullBlock($chunk->getFullBlock($x & Chunk::COORD_MASK, $y, $z & Chunk::COORD_MASK));
			}else{
				$addToCache = false;
				$block = VanillaBlocks::AIR();
			}
		}else{
			$block = VanillaBlocks::AIR();
		}

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
		$chunkX = $x >> Chunk::COORD_BIT_SIZE;
		$chunkZ = $z >> Chunk::COORD_BIT_SIZE;
		if($this->loadChunk($chunkX, $chunkZ) === null){ //current expected behaviour is to try to load the terrain synchronously
			throw new WorldException("Cannot set a block in un-generated terrain");
		}

		$this->timings->setBlock->startTiming();

		$this->unlockChunk($chunkX, $chunkZ, null);

		$block = clone $block;

		$block->position($this, $x, $y, $z);
		$block->writeStateToWorld();
		$pos = new Vector3($x, $y, $z);

		$chunkHash = World::chunkHash($chunkX, $chunkZ);
		$relativeBlockHash = World::chunkBlockHash($x, $y, $z);

		unset($this->blockCache[$chunkHash][$relativeBlockHash]);

		if(!isset($this->changedBlocks[$chunkHash])){
			$this->changedBlocks[$chunkHash] = [];
		}
		$this->changedBlocks[$chunkHash][$relativeBlockHash] = $pos;

		foreach($this->getChunkListeners($chunkX, $chunkZ) as $listener){
			$listener->onBlockChanged($pos);
		}

		if($update){
			$this->updateAllLight($x, $y, $z);
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
			$orb = new ExperienceOrb(Location::fromObject($pos, $this, lcg_value() * 360, 0), $split);

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

		$chunkX = $vector->getFloorX() >> Chunk::COORD_BIT_SIZE;
		$chunkZ = $vector->getFloorZ() >> Chunk::COORD_BIT_SIZE;
		if(!$this->isChunkLoaded($chunkX, $chunkZ) || $this->isChunkLocked($chunkX, $chunkZ)){
			return false;
		}

		$target = $this->getBlock($vector);
		$affectedBlocks = $target->getAffectedBlocks();

		if($item === null){
			$item = ItemFactory::air();
		}

		$drops = [];
		if($player === null or $player->hasFiniteResources()){
			$drops = array_merge(...array_map(fn(Block $block) => $block->getDrops($item), $affectedBlocks));
		}

		$xpDrop = 0;
		if($player !== null and $player->hasFiniteResources()){
			$xpDrop = array_sum(array_map(fn(Block $block) => $block->getXpDropForTool($item), $affectedBlocks));
		}

		if($player !== null){
			$ev = new BlockBreakEvent($player, $target, $item, $player->isCreative(), $drops, $xpDrop);

			if($target instanceof Air or ($player->isSurvival() and !$target->getBreakInfo()->isBreakable()) or $player->isSpectator()){
				$ev->cancel();
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

				if(!$canBreak){
					$ev->cancel();
				}
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
			$this->addParticle($target->getPosition()->add(0.5, 0.5, 0.5), new BlockBreakParticle($target));
		}

		$target->onBreak($item, $player);

		$tile = $this->getTile($target->getPosition());
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

		if(!$this->isInWorld($blockReplace->getPosition()->x, $blockReplace->getPosition()->y, $blockReplace->getPosition()->z)){
			//TODO: build height limit messages for custom world heights and mcregion cap
			return false;
		}
		$chunkX = $blockReplace->getPosition()->getFloorX() >> Chunk::COORD_BIT_SIZE;
		$chunkZ = $blockReplace->getPosition()->getFloorZ() >> Chunk::COORD_BIT_SIZE;
		if(!$this->isChunkLoaded($chunkX, $chunkZ) || $this->isChunkLocked($chunkX, $chunkZ)){
			return false;
		}

		if($blockClicked->getId() === BlockLegacyIds::AIR){
			return false;
		}

		if($player !== null){
			$ev = new PlayerInteractEvent($player, $item, $blockClicked, $clickVector, $face, PlayerInteractEvent::RIGHT_CLICK_BLOCK);
			if($player->isSpectator()){
				$ev->cancel(); //set it to cancelled so plugins can bypass this
			}

			$ev->call();
			if(!$ev->isCancelled()){
				if((!$player->isSneaking() or $item->isNull()) and $blockClicked->onInteract($item, $face, $clickVector, $player)){
					return true;
				}

				$result = $item->onInteractBlock($player, $blockReplace, $blockClicked, $face, $clickVector);
				if(!$result->equals(ItemUseResult::NONE())){
					return $result->equals(ItemUseResult::SUCCESS());
				}
			}else{
				return false;
			}
		}elseif($blockClicked->onInteract($item, $face, $clickVector, $player)){
			return true;
		}

		if($item->isNull() or !$item->canBePlaced()){
			return false;
		}
		$hand = $item->getBlock($face);
		$hand->position($this, $blockReplace->getPosition()->x, $blockReplace->getPosition()->y, $blockReplace->getPosition()->z);

		if($hand->canBePlacedAt($blockClicked, $clickVector, $face, true)){
			$blockReplace = $blockClicked;
			$hand->position($this, $blockReplace->getPosition()->x, $blockReplace->getPosition()->y, $blockReplace->getPosition()->z);
		}elseif(!$hand->canBePlacedAt($blockReplace, $clickVector, $face, false)){
			return false;
		}

		$tx = new BlockTransaction($this);
		if(!$hand->place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player)){
			return false;
		}

		foreach($tx->getBlocks() as [$x, $y, $z, $block]){
			$block->position($this, $x, $y, $z);
			foreach($block->getCollisionBoxes() as $collisionBox){
				if(count($this->getCollidingEntities($collisionBox)) > 0){
					return false;  //Entity in block
				}
			}
		}

		if($player !== null){
			$ev = new BlockPlaceEvent($player, $hand, $blockReplace, $blockClicked, $item);
			if($player->isSpectator()){
				$ev->cancel();
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

				if(!$canPlace){
					$ev->cancel();
				}
			}

			$ev->call();
			if($ev->isCancelled()){
				return false;
			}
		}

		if(!$tx->apply()){
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
			$this->addSound($hand->getPosition(), new BlockPlaceSound($hand));
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
			foreach($this->getNearbyEntities($bb, $entity) as $ent){
				if($ent->canBeCollidedWith() and ($entity === null or $entity->canCollideWith($ent))){
					$nearby[] = $ent;
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

		$minX = ((int) floor($bb->minX - 2)) >> Chunk::COORD_BIT_SIZE;
		$maxX = ((int) floor($bb->maxX + 2)) >> Chunk::COORD_BIT_SIZE;
		$minZ = ((int) floor($bb->minZ - 2)) >> Chunk::COORD_BIT_SIZE;
		$maxZ = ((int) floor($bb->maxZ + 2)) >> Chunk::COORD_BIT_SIZE;

		for($x = $minX; $x <= $maxX; ++$x){
			for($z = $minZ; $z <= $maxZ; ++$z){
				if(!$this->isChunkLoaded($x, $z)){
					continue;
				}
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

		$minX = ((int) floor($pos->x - $maxDistance)) >> Chunk::COORD_BIT_SIZE;
		$maxX = ((int) floor($pos->x + $maxDistance)) >> Chunk::COORD_BIT_SIZE;
		$minZ = ((int) floor($pos->z - $maxDistance)) >> Chunk::COORD_BIT_SIZE;
		$maxZ = ((int) floor($pos->z + $maxDistance)) >> Chunk::COORD_BIT_SIZE;

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
				foreach($this->getChunkEntities($x, $z) as $entity){
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
		return ($chunk = $this->loadChunk($x >> Chunk::COORD_BIT_SIZE, $z >> Chunk::COORD_BIT_SIZE)) !== null ? $chunk->getTile($x & Chunk::COORD_MASK, $y, $z & Chunk::COORD_MASK) : null;
	}

	public function getBiomeId(int $x, int $z) : int{
		if(($chunk = $this->loadChunk($x >> Chunk::COORD_BIT_SIZE, $z >> Chunk::COORD_BIT_SIZE)) !== null){
			return $chunk->getBiomeId($x & Chunk::COORD_MASK, $z & Chunk::COORD_MASK);
		}
		return BiomeIds::OCEAN; //TODO: this should probably throw instead (terrain not generated yet)
	}

	public function getBiome(int $x, int $z) : Biome{
		return BiomeRegistry::getInstance()->getBiome($this->getBiomeId($x, $z));
	}

	public function setBiomeId(int $x, int $z, int $biomeId) : void{
		$chunkX = $x >> Chunk::COORD_BIT_SIZE;
		$chunkZ = $z >> Chunk::COORD_BIT_SIZE;
		$this->unlockChunk($chunkX, $chunkZ, null);
		if(($chunk = $this->loadChunk($chunkX, $chunkZ)) !== null){
			$chunk->setBiomeId($x & Chunk::COORD_MASK, $z & Chunk::COORD_MASK, $biomeId);
		}else{
			//if we allowed this, the modifications would be lost when the chunk is created
			throw new WorldException("Cannot set biome in a non-generated chunk");
		}
	}

	/**
	 * @return Chunk[]
	 */
	public function getLoadedChunks() : array{
		return $this->chunks;
	}

	public function getChunk(int $chunkX, int $chunkZ) : ?Chunk{
		return $this->chunks[World::chunkHash($chunkX, $chunkZ)] ?? null;
	}

	/**
	 * @return Entity[]
	 */
	public function getChunkEntities(int $chunkX, int $chunkZ) : array{
		return $this->entitiesByChunk[World::chunkHash($chunkX, $chunkZ)] ?? [];
	}

	/**
	 * Returns the chunk containing the given Vector3 position.
	 */
	public function getOrLoadChunkAtPosition(Vector3 $pos) : ?Chunk{
		return $this->loadChunk($pos->getFloorX() >> Chunk::COORD_BIT_SIZE, $pos->getFloorZ() >> Chunk::COORD_BIT_SIZE);
	}

	/**
	 * Returns the chunks adjacent to the specified chunk.
	 *
	 * @return Chunk[]|null[]
	 */
	public function getAdjacentChunks(int $x, int $z) : array{
		$result = [];
		for($xx = -1; $xx <= 1; ++$xx){
			for($zz = -1; $zz <= 1; ++$zz){
				if($xx === 0 && $zz === 0){
					continue; //center chunk
				}
				$result[World::chunkHash($xx, $zz)] = $this->loadChunk($x + $xx, $z + $zz);
			}
		}

		return $result;
	}

	/**
	 * Flags a chunk as locked, usually for async modification.
	 *
	 * This is an **advisory lock**. This means that the lock does **not** prevent the chunk from being modified on the
	 * main thread, such as by setBlock() or setBiomeId(). However, you can use it to detect when such modifications
	 * have taken place - unlockChunk() with the same lockID will fail and return false if this happens.
	 *
	 * This is used internally by the generation system to ensure that two PopulationTasks don't try to modify the same
	 * chunk at the same time. Generation will respect these locks and won't try to do generation of chunks over which
	 * a lock is held.
	 *
	 * WARNING: Be sure to release all locks once you're done with them, or you WILL have problems with terrain not
	 * being generated.
	 */
	public function lockChunk(int $chunkX, int $chunkZ, ChunkLockId $lockId) : void{
		$chunkHash = World::chunkHash($chunkX, $chunkZ);
		if(isset($this->chunkLock[$chunkHash])){
			throw new \InvalidArgumentException("Chunk $chunkX $chunkZ is already locked");
		}
		$this->chunkLock[$chunkHash] = $lockId;
	}

	/**
	 * Unlocks a chunk previously locked by lockChunk().
	 *
	 * You must provide the same lockID as provided to lockChunk().
	 * If a null lockID is given, any existing lock will be removed from the chunk, regardless of who owns it.
	 *
	 * Returns true if unlocking was successful, false otherwise.
	 */
	public function unlockChunk(int $chunkX, int $chunkZ, ?ChunkLockId $lockId) : bool{
		$chunkHash = World::chunkHash($chunkX, $chunkZ);
		if(isset($this->chunkLock[$chunkHash]) && ($lockId === null || $this->chunkLock[$chunkHash] === $lockId)){
			unset($this->chunkLock[$chunkHash]);
			return true;
		}
		return false;
	}

	/**
	 * Returns whether anyone currently has a lock on the chunk at the given coordinates.
	 * You should check this to make sure that population tasks aren't currently modifying chunks that you want to use
	 * in async tasks.
	 */
	public function isChunkLocked(int $chunkX, int $chunkZ) : bool{
		return isset($this->chunkLock[World::chunkHash($chunkX, $chunkZ)]);
	}

	public function setChunk(int $chunkX, int $chunkZ, Chunk $chunk) : void{
		$chunkHash = World::chunkHash($chunkX, $chunkZ);
		$oldChunk = $this->loadChunk($chunkX, $chunkZ);
		if($oldChunk !== null and $oldChunk !== $chunk){
			$deletedTiles = 0;
			$transferredTiles = 0;
			foreach($oldChunk->getTiles() as $oldTile){
				$tilePosition = $oldTile->getPosition();
				$localX = $tilePosition->getFloorX() & Chunk::COORD_MASK;
				$localY = $tilePosition->getFloorY();
				$localZ = $tilePosition->getFloorZ() & Chunk::COORD_MASK;

				$newBlock = BlockFactory::getInstance()->fromFullBlock($chunk->getFullBlock($localX, $localY, $localZ));
				$expectedTileClass = $newBlock->getIdInfo()->getTileClass();
				if(
					$expectedTileClass === null || //new block doesn't expect a tile
					!($oldTile instanceof $expectedTileClass) || //new block expects a different tile
					(($newTile = $chunk->getTile($localX, $localY, $localZ)) !== null && $newTile !== $oldTile) //new chunk already has a different tile
				){
					$oldTile->close();
					$deletedTiles++;
				}else{
					$transferredTiles++;
					$chunk->addTile($oldTile);
					$oldChunk->removeTile($oldTile);
				}
			}
			if($deletedTiles > 0 || $transferredTiles > 0){
				$this->logger->debug("Replacement of chunk $chunkX $chunkZ caused deletion of $deletedTiles obsolete/conflicted tiles, and transfer of $transferredTiles");
			}
		}

		$this->chunks[$chunkHash] = $chunk;

		unset($this->blockCache[$chunkHash]);
		unset($this->changedBlocks[$chunkHash]);
		$chunk->setTerrainDirty();

		if(!$this->isChunkInUse($chunkX, $chunkZ)){
			$this->unloadChunkRequest($chunkX, $chunkZ);
		}

		if($oldChunk === null){
			(new ChunkLoadEvent($this, $chunkX, $chunkZ, $chunk, true))->call();

			foreach($this->getChunkListeners($chunkX, $chunkZ) as $listener){
				$listener->onChunkLoaded($chunkX, $chunkZ, $chunk);
			}
		}else{
			foreach($this->getChunkListeners($chunkX, $chunkZ) as $listener){
				$listener->onChunkChanged($chunkX, $chunkZ, $chunk);
			}
		}

		for($cX = -1; $cX <= 1; ++$cX){
			for($cZ = -1; $cZ <= 1; ++$cZ){
				foreach($this->getChunkEntities($chunkX + $cX, $chunkZ + $cZ) as $entity){
					$entity->onNearbyBlockChange();
				}
			}
		}
	}

	/**
	 * Gets the highest block Y value at a specific $x and $z
	 *
	 * @return int|null 0-255, or null if the column is empty
	 * @throws WorldException if the terrain is not generated
	 */
	public function getHighestBlockAt(int $x, int $z) : ?int{
		if(($chunk = $this->loadChunk($x >> Chunk::COORD_BIT_SIZE, $z >> Chunk::COORD_BIT_SIZE)) !== null){
			return $chunk->getHighestBlockAt($x & Chunk::COORD_MASK, $z & Chunk::COORD_MASK);
		}
		throw new WorldException("Cannot get highest block in an ungenerated chunk");
	}

	/**
	 * Returns whether the given position is in a loaded area of terrain.
	 */
	public function isInLoadedTerrain(Vector3 $pos) : bool{
		return $this->isChunkLoaded($pos->getFloorX() >> Chunk::COORD_BIT_SIZE, $pos->getFloorZ() >> Chunk::COORD_BIT_SIZE);
	}

	public function isChunkLoaded(int $x, int $z) : bool{
		return isset($this->chunks[World::chunkHash($x, $z)]);
	}

	public function isChunkGenerated(int $x, int $z) : bool{
		return $this->loadChunk($x, $z) !== null;
	}

	public function isChunkPopulated(int $x, int $z) : bool{
		$chunk = $this->loadChunk($x, $z);
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
		if(array_key_exists($entity->getId(), $this->entities)){
			if($this->entities[$entity->getId()] === $entity){
				throw new \InvalidArgumentException("Entity " . $entity->getId() . " has already been added to this world");
			}else{
				throw new AssumptionFailedError("Found two different entities sharing entity ID " . $entity->getId());
			}
		}
		$pos = $entity->getPosition()->asVector3();
		$this->entitiesByChunk[World::chunkHash($pos->getFloorX() >> Chunk::COORD_BIT_SIZE, $pos->getFloorZ() >> Chunk::COORD_BIT_SIZE)][$entity->getId()] = $entity;
		$this->entityLastKnownPositions[$entity->getId()] = $pos;

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
		if(!array_key_exists($entity->getId(), $this->entities)){
			throw new \InvalidArgumentException("Entity is not tracked by this world (possibly already removed?)");
		}
		$pos = $this->entityLastKnownPositions[$entity->getId()];
		$chunkHash = World::chunkHash($pos->getFloorX() >> Chunk::COORD_BIT_SIZE, $pos->getFloorZ() >> Chunk::COORD_BIT_SIZE);
		if(isset($this->entitiesByChunk[$chunkHash][$entity->getId()])){
			unset($this->entitiesByChunk[$chunkHash][$entity->getId()]);
			if(count($this->entitiesByChunk[$chunkHash]) === 0){
				unset($this->entitiesByChunk[$chunkHash]);
			}
		}
		unset($this->entityLastKnownPositions[$entity->getId()]);

		if($entity instanceof Player){
			unset($this->players[$entity->getId()]);
			$this->checkSleep();
		}

		unset($this->entities[$entity->getId()]);
		unset($this->updateEntities[$entity->getId()]);
	}

	/**
	 * @internal
	 */
	public function onEntityMoved(Entity $entity) : void{
		if(!array_key_exists($entity->getId(), $this->entityLastKnownPositions)){
			//this can happen if the entity was teleported before addEntity() was called
			return;
		}
		$oldPosition = $this->entityLastKnownPositions[$entity->getId()];
		$newPosition = $entity->getPosition();

		$oldChunkX = $oldPosition->getFloorX() >> Chunk::COORD_BIT_SIZE;
		$oldChunkZ = $oldPosition->getFloorZ() >> Chunk::COORD_BIT_SIZE;
		$newChunkX = $newPosition->getFloorX() >> Chunk::COORD_BIT_SIZE;
		$newChunkZ = $newPosition->getFloorZ() >> Chunk::COORD_BIT_SIZE;

		if($oldChunkX !== $newChunkX || $oldChunkZ !== $newChunkZ){
			$oldChunkHash = World::chunkHash($oldChunkX, $oldChunkZ);
			if(isset($this->entitiesByChunk[$oldChunkHash][$entity->getId()])){
				unset($this->entitiesByChunk[$oldChunkHash][$entity->getId()]);
				if(count($this->entitiesByChunk[$oldChunkHash]) === 0){
					unset($this->entitiesByChunk[$oldChunkHash]);
				}
			}

			$newViewers = $this->getViewersForPosition($newPosition);
			foreach($entity->getViewers() as $player){
				if(!isset($newViewers[spl_object_id($player)])){
					$entity->despawnFrom($player);
				}else{
					unset($newViewers[spl_object_id($player)]);
				}
			}
			foreach($newViewers as $player){
				$entity->spawnTo($player);
			}

			$newChunkHash = World::chunkHash($newChunkX, $newChunkZ);
			$this->entitiesByChunk[$newChunkHash][$entity->getId()] = $entity;
		}
		$this->entityLastKnownPositions[$entity->getId()] = $newPosition->asVector3();
	}

	/**
	 * @internal Tiles are now bound with blocks, and their creation is automatic. They should not be directly added.
	 * @throws \InvalidArgumentException
	 */
	public function addTile(Tile $tile) : void{
		if($tile->isClosed()){
			throw new \InvalidArgumentException("Attempted to add a garbage closed Tile to world");
		}
		$pos = $tile->getPosition();
		if(!$pos->isValid() || $pos->getWorld() !== $this){
			throw new \InvalidArgumentException("Invalid Tile world");
		}

		$chunkX = $pos->getFloorX() >> Chunk::COORD_BIT_SIZE;
		$chunkZ = $pos->getFloorZ() >> Chunk::COORD_BIT_SIZE;

		if(isset($this->chunks[$hash = World::chunkHash($chunkX, $chunkZ)])){
			$this->chunks[$hash]->addTile($tile);
		}else{
			throw new \InvalidArgumentException("Attempted to create tile " . get_class($tile) . " in unloaded chunk $chunkX $chunkZ");
		}

		//delegate tile ticking to the corresponding block
		$this->scheduleDelayedBlockUpdate($pos->asVector3(), 1);
	}

	/**
	 * @internal Tiles are now bound with blocks, and their removal is automatic. They should not be directly removed.
	 * @throws \InvalidArgumentException
	 */
	public function removeTile(Tile $tile) : void{
		$pos = $tile->getPosition();
		if(!$pos->isValid() || $pos->getWorld() !== $this){
			throw new \InvalidArgumentException("Invalid Tile world");
		}

		$chunkX = $pos->getFloorX() >> Chunk::COORD_BIT_SIZE;
		$chunkZ = $pos->getFloorZ() >> Chunk::COORD_BIT_SIZE;

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
	 * Attempts to load a chunk from the world provider (if not already loaded). If the chunk is already loaded, it is
	 * returned directly.
	 *
	 * @return Chunk|null the requested chunk, or null on failure.
	 */
	public function loadChunk(int $x, int $z) : ?Chunk{
		if(isset($this->chunks[$chunkHash = World::chunkHash($x, $z)])){
			return $this->chunks[$chunkHash];
		}

		$this->timings->syncChunkLoad->startTiming();

		$this->cancelUnloadChunkRequest($x, $z);

		$this->timings->syncChunkLoadData->startTiming();

		$chunk = null;

		try{
			$chunk = $this->provider->loadChunk($x, $z);
		}catch(CorruptedChunkException $e){
			$this->logger->critical("Failed to load chunk x=$x z=$z: " . $e->getMessage());
		}

		$this->timings->syncChunkLoadData->stopTiming();

		if($chunk === null){
			$this->timings->syncChunkLoad->stopTiming();
			return null;
		}

		$this->chunks[$chunkHash] = $chunk->getChunk();
		unset($this->blockCache[$chunkHash]);

		$this->initChunk($x, $z, $chunk);

		(new ChunkLoadEvent($this, $x, $z, $this->chunks[$chunkHash], false))->call();

		if(!$this->isChunkInUse($x, $z)){
			$this->logger->debug("Newly loaded chunk $x $z has no loaders registered, will be unloaded at next available opportunity");
			$this->unloadChunkRequest($x, $z);
		}
		foreach($this->getChunkListeners($x, $z) as $listener){
			$listener->onChunkLoaded($x, $z, $this->chunks[$chunkHash]);
		}

		$this->timings->syncChunkLoad->stopTiming();

		return $this->chunks[$chunkHash];
	}

	private function initChunk(int $chunkX, int $chunkZ, ChunkData $chunkData) : void{
		$logger = new \PrefixedLogger($this->logger, "Loading chunk $chunkX $chunkZ");
		if(count($chunkData->getEntityNBT()) !== 0){
			$this->timings->syncChunkLoadEntities->startTiming();
			$entityFactory = EntityFactory::getInstance();
			foreach($chunkData->getEntityNBT() as $k => $nbt){
				try{
					$entity = $entityFactory->createFromData($this, $nbt);
				}catch(NbtDataException $e){
					$logger->error("Bad entity data at list position $k: " . $e->getMessage());
					$logger->logException($e);
					continue;
				}
				if($entity === null){
					$saveIdTag = $nbt->getTag("id") ?? $nbt->getTag("identifier");
					$saveId = "<unknown>";
					if($saveIdTag instanceof StringTag){
						$saveId = $saveIdTag->getValue();
					}elseif($saveIdTag instanceof IntTag){ //legacy MCPE format
						$saveId = "legacy(" . $saveIdTag->getValue() . ")";
					}
					$logger->warning("Deleted unknown entity type $saveId");
				}
				//TODO: we can't prevent entities getting added to unloaded chunks if they were saved in the wrong place
				//here, because entities currently add themselves to the world
			}

			$this->timings->syncChunkLoadEntities->stopTiming();
		}

		if(count($chunkData->getTileNBT()) !== 0){
			$this->timings->syncChunkLoadTileEntities->startTiming();
			$tileFactory = TileFactory::getInstance();
			foreach($chunkData->getTileNBT() as $k => $nbt){
				try{
					$tile = $tileFactory->createFromData($this, $nbt);
				}catch(NbtDataException $e){
					$logger->error("Bad tile entity data at list position $k: " . $e->getMessage());
					$logger->logException($e);
					continue;
				}
				if($tile === null){
					$logger->warning("Deleted unknown tile entity type " . $nbt->getString("id", "<unknown>"));
				}elseif(!$this->isChunkLoaded($tile->getPosition()->getFloorX() >> Chunk::COORD_BIT_SIZE, $tile->getPosition()->getFloorZ() >> Chunk::COORD_BIT_SIZE)){
					$logger->error("Found tile saved on wrong chunk - unable to fix due to correct chunk not loaded");
				}elseif($this->getTile($tilePosition = $tile->getPosition()) !== null){
					$logger->error("Cannot add tile at x=$tilePosition->x,y=$tilePosition->y,z=$tilePosition->z: Another tile is already at that position");
				}else{
					$this->addTile($tile);
				}
			}

			$this->timings->syncChunkLoadTileEntities->stopTiming();
		}
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
			$ev = new ChunkUnloadEvent($this, $x, $z, $chunk);
			$ev->call();
			if($ev->isCancelled()){
				$this->timings->doChunkUnload->stopTiming();

				return false;
			}

			if($trySave and $this->getAutoSave()){
				$this->timings->syncChunkSave->startTiming();
				try{
					$this->provider->saveChunk($x, $z, new ChunkData(
						$chunk,
						array_map(fn(Entity $e) => $e->saveNBT(), array_filter($this->getChunkEntities($x, $z), fn(Entity $e) => $e->canSaveWithChunk())),
						array_map(fn(Tile $t) => $t->saveNBT(), $chunk->getTiles()),
					));
				}finally{
					$this->timings->syncChunkSave->stopTiming();
				}
			}

			foreach($this->getChunkListeners($x, $z) as $listener){
				$listener->onChunkUnloaded($x, $z, $chunk);
			}

			foreach($this->getChunkEntities($x, $z) as $entity){
				if($entity instanceof Player){
					continue;
				}
				$entity->close();
			}

			$chunk->onUnload();
		}

		unset($this->chunks[$chunkHash]);
		unset($this->blockCache[$chunkHash]);
		unset($this->changedBlocks[$chunkHash]);

		if(array_key_exists($chunkHash, $this->chunkPopulationRequestMap)){
			$this->chunkPopulationRequestMap[$chunkHash]->reject();
			unset($this->chunkPopulationRequestMap[$chunkHash]);
		}

		$this->timings->doChunkUnload->stopTiming();

		return true;
	}

	/**
	 * Returns whether the chunk at the specified coordinates is a spawn chunk
	 */
	public function isSpawnChunk(int $X, int $Z) : bool{
		$spawn = $this->getSpawnLocation();
		$spawnX = $spawn->x >> Chunk::COORD_BIT_SIZE;
		$spawnZ = $spawn->z >> Chunk::COORD_BIT_SIZE;

		return abs($X - $spawnX) <= 1 and abs($Z - $spawnZ) <= 1;
	}

	/**
	 * @throws WorldException if the terrain is not generated
	 */
	public function getSafeSpawn(?Vector3 $spawn = null) : Position{
		if(!($spawn instanceof Vector3) or $spawn->y < 1){
			$spawn = $this->getSpawnLocation();
		}

		$max = $this->maxY;
		$v = $spawn->floor();
		$chunk = $this->getOrLoadChunkAtPosition($v);
		if($chunk === null){
			throw new WorldException("Cannot find a safe spawn point in non-generated terrain");
		}
		$x = (int) $v->x;
		$z = (int) $v->z;
		$y = (int) min($max - 2, $v->y);
		$wasAir = $this->getBlockAt($x, $y - 1, $z)->getId() === BlockLegacyIds::AIR; //TODO: bad hack, clean up
		for(; $y > $this->minY; --$y){
			if($this->getBlockAt($x, $y, $z)->isFullCube()){
				if($wasAir){
					$y++;
					break;
				}
			}else{
				$wasAir = true;
			}
		}

		for(; $y >= $this->minY and $y < $max; ++$y){
			if(!$this->getBlockAt($x, $y + 1, $z)->isFullCube()){
				if(!$this->getBlockAt($x, $y, $z)->isFullCube()){
					return new Position($spawn->x, $y === (int) $spawn->y ? $spawn->y : $y, $spawn->z, $this);
				}
			}else{
				++$y;
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

	public function getMinY() : int{
		return $this->minY;
	}

	public function getMaxY() : int{
		return $this->maxY;
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

	private function addChunkHashToPopulationRequestQueue(int $chunkHash) : void{
		if(!isset($this->chunkPopulationRequestQueueIndex[$chunkHash])){
			$this->chunkPopulationRequestQueue->enqueue($chunkHash);
			$this->chunkPopulationRequestQueueIndex[$chunkHash] = true;
		}
	}

	/**
	 * @phpstan-return Promise<Chunk>
	 */
	private function enqueuePopulationRequest(int $chunkX, int $chunkZ, ?ChunkLoader $associatedChunkLoader) : Promise{
		$chunkHash = World::chunkHash($chunkX, $chunkZ);
		$this->addChunkHashToPopulationRequestQueue($chunkHash);
		$resolver = $this->chunkPopulationRequestMap[$chunkHash] = new PromiseResolver();
		if($associatedChunkLoader === null){
			$temporaryLoader = new class implements ChunkLoader{};
			$this->registerChunkLoader($temporaryLoader, $chunkX, $chunkZ);
			$resolver->getPromise()->onCompletion(
				fn() => $this->unregisterChunkLoader($temporaryLoader, $chunkX, $chunkZ),
				static function() : void{}
			);
		}
		return $resolver->getPromise();
	}

	private function drainPopulationRequestQueue() : void{
		$failed = [];
		while(count($this->activeChunkPopulationTasks) < $this->maxConcurrentChunkPopulationTasks && !$this->chunkPopulationRequestQueue->isEmpty()){
			$nextChunkHash = $this->chunkPopulationRequestQueue->dequeue();
			unset($this->chunkPopulationRequestQueueIndex[$nextChunkHash]);
			World::getXZ($nextChunkHash, $nextChunkX, $nextChunkZ);
			if(isset($this->chunkPopulationRequestMap[$nextChunkHash])){
				assert(!isset($this->activeChunkPopulationTasks[$nextChunkHash]), "Population for chunk $nextChunkX $nextChunkZ already running");
				if(
					!$this->orderChunkPopulation($nextChunkX, $nextChunkZ, null)->isResolved() &&
					!isset($this->activeChunkPopulationTasks[$nextChunkHash])
				){
					$failed[] = $nextChunkHash;
				}
			}
		}

		//these requests failed even though they weren't rate limited; we can't directly re-add them to the back of the
		//queue because it would result in an infinite loop
		foreach($failed as $hash){
			$this->addChunkHashToPopulationRequestQueue($hash);
		}
	}

	/**
	 * Checks if a chunk needs to be populated, and whether it's ready to do so.
	 * @return bool[]|PromiseResolver[]|null[]
	 * @phpstan-return array{?PromiseResolver<Chunk>, bool}
	 */
	private function checkChunkPopulationPreconditions(int $chunkX, int $chunkZ) : array{
		$chunkHash = World::chunkHash($chunkX, $chunkZ);
		$resolver = $this->chunkPopulationRequestMap[$chunkHash] ?? null;
		if($resolver !== null && isset($this->activeChunkPopulationTasks[$chunkHash])){
			//generation is already running
			return [$resolver, false];
		}

		$temporaryChunkLoader = new class implements ChunkLoader{};
		$this->registerChunkLoader($temporaryChunkLoader, $chunkX, $chunkZ);
		$chunk = $this->loadChunk($chunkX, $chunkZ);
		$this->unregisterChunkLoader($temporaryChunkLoader, $chunkX, $chunkZ);
		if($chunk !== null && $chunk->isPopulated()){
			//chunk is already populated; return a pre-resolved promise that will directly fire callbacks assigned
			$resolver ??= new PromiseResolver();
			unset($this->chunkPopulationRequestMap[$chunkHash]);
			$resolver->resolve($chunk);
			return [$resolver, false];
		}
		return [$resolver, true];
	}

	/**
	 * Attempts to initiate asynchronous generation/population of the target chunk, if it's currently reasonable to do
	 * so (and if it isn't already generated/populated).
	 * If the generator is busy, the request will be put into a queue and delayed until a better time.
	 *
	 * A ChunkLoader can be associated with the generation request to ensure that the generation request is cancelled if
	 * no loaders are attached to the target chunk. If no loader is provided, one will be assigned (and automatically
	 * removed when the generation request completes).
	 *
	 * @phpstan-return Promise<Chunk>
	 */
	public function requestChunkPopulation(int $chunkX, int $chunkZ, ?ChunkLoader $associatedChunkLoader) : Promise{
		[$resolver, $proceedWithPopulation] = $this->checkChunkPopulationPreconditions($chunkX, $chunkZ);
		if(!$proceedWithPopulation){
			return $resolver?->getPromise() ?? $this->enqueuePopulationRequest($chunkX, $chunkZ, $associatedChunkLoader);
		}

		if(count($this->activeChunkPopulationTasks) >= $this->maxConcurrentChunkPopulationTasks){
			//too many chunks are already generating; delay resolution of the request until later
			return $resolver?->getPromise() ?? $this->enqueuePopulationRequest($chunkX, $chunkZ, $associatedChunkLoader);
		}
		return $this->internalOrderChunkPopulation($chunkX, $chunkZ, $associatedChunkLoader, $resolver);
	}

	/**
	 * Initiates asynchronous generation/population of the target chunk, if it's not already generated/populated.
	 * If generation has already been requested for the target chunk, the promise for the already active request will be
	 * returned directly.
	 *
	 * If the chunk is currently locked (for example due to another chunk using it for async generation), the request
	 * will be queued and executed at the earliest opportunity.
	 *
	 * @phpstan-return Promise<Chunk>
	 */
	public function orderChunkPopulation(int $chunkX, int $chunkZ, ?ChunkLoader $associatedChunkLoader) : Promise{
		[$resolver, $proceedWithPopulation] = $this->checkChunkPopulationPreconditions($chunkX, $chunkZ);
		if(!$proceedWithPopulation){
			return $resolver?->getPromise() ?? $this->enqueuePopulationRequest($chunkX, $chunkZ, $associatedChunkLoader);
		}

		return $this->internalOrderChunkPopulation($chunkX, $chunkZ, $associatedChunkLoader, $resolver);
	}

	/**
	 * @phpstan-param PromiseResolver<Chunk>|null $resolver
	 * @phpstan-return Promise<Chunk>
	 */
	private function internalOrderChunkPopulation(int $chunkX, int $chunkZ, ?ChunkLoader $associatedChunkLoader, ?PromiseResolver $resolver) : Promise{
		$chunkHash = World::chunkHash($chunkX, $chunkZ);

		Timings::$population->startTiming();

		for($xx = -1; $xx <= 1; ++$xx){
			for($zz = -1; $zz <= 1; ++$zz){
				if($this->isChunkLocked($chunkX + $xx, $chunkZ + $zz)){
					//chunk is already in use by another generation request; queue the request for later
					return $resolver?->getPromise() ?? $this->enqueuePopulationRequest($chunkX, $chunkZ, $associatedChunkLoader);
				}
			}
		}

		$this->activeChunkPopulationTasks[$chunkHash] = true;
		if($resolver === null){
			$resolver = new PromiseResolver();
			$this->chunkPopulationRequestMap[$chunkHash] = $resolver;
		}

		$chunkPopulationLockId = new ChunkLockId();

		$temporaryChunkLoader = new class implements ChunkLoader{};
		for($xx = -1; $xx <= 1; ++$xx){
			for($zz = -1; $zz <= 1; ++$zz){
				$this->lockChunk($chunkX + $xx, $chunkZ + $zz, $chunkPopulationLockId);
				$this->registerChunkLoader($temporaryChunkLoader, $chunkX + $xx, $chunkZ + $zz);
			}
		}

		$centerChunk = $this->loadChunk($chunkX, $chunkZ);
		$adjacentChunks = $this->getAdjacentChunks($chunkX, $chunkZ);
		$task = new PopulationTask(
			$this->worldId,
			$chunkX,
			$chunkZ,
			$centerChunk,
			$adjacentChunks,
			function(Chunk $centerChunk, array $adjacentChunks) use ($chunkPopulationLockId, $chunkX, $chunkZ, $temporaryChunkLoader) : void{
				if(!$this->isLoaded()){
					return;
				}

				$this->generateChunkCallback($chunkPopulationLockId, $chunkX, $chunkZ, $centerChunk, $adjacentChunks, $temporaryChunkLoader);
			}
		);
		$workerId = $this->workerPool->selectWorker();
		if(!isset($this->workerPool->getRunningWorkers()[$workerId]) && isset($this->generatorRegisteredWorkers[$workerId])){
			$this->logger->debug("Selected worker $workerId previously had generator registered, but is now offline");
			unset($this->generatorRegisteredWorkers[$workerId]);
		}
		if(!isset($this->generatorRegisteredWorkers[$workerId])){
			$this->registerGeneratorToWorker($workerId);
		}
		$this->workerPool->submitTaskToWorker($task, $workerId);

		Timings::$population->stopTiming();
		return $resolver->getPromise();
	}

	/**
	 * @param Chunk[] $adjacentChunks chunkHash => chunk
	 * @phpstan-param array<int, Chunk> $adjacentChunks
	 */
	private function generateChunkCallback(ChunkLockId $chunkLockId, int $x, int $z, Chunk $chunk, array $adjacentChunks, ChunkLoader $temporaryChunkLoader) : void{
		Timings::$generationCallback->startTiming();

		$dirtyChunks = 0;
		for($xx = -1; $xx <= 1; ++$xx){
			for($zz = -1; $zz <= 1; ++$zz){
				$this->unregisterChunkLoader($temporaryChunkLoader, $x + $xx, $z + $zz);
				if(!$this->unlockChunk($x + $xx, $z + $zz, $chunkLockId)){
					$dirtyChunks++;
				}
			}
		}

		$index = World::chunkHash($x, $z);
		if(!isset($this->chunkPopulationRequestMap[$index])){
			$this->logger->debug("Discarding population result for chunk x=$x,z=$z - promise was already broken");
			unset($this->activeChunkPopulationTasks[$index]);
		}elseif(isset($this->activeChunkPopulationTasks[$index])){
			if($dirtyChunks === 0){
				$oldChunk = $this->loadChunk($x, $z);
				$this->setChunk($x, $z, $chunk);

				foreach($adjacentChunks as $relativeChunkHash => $adjacentChunk){
					World::getXZ($relativeChunkHash, $relativeX, $relativeZ);
					if($relativeX < -1 || $relativeX > 1 || $relativeZ < -1 || $relativeZ > 1){
						throw new AssumptionFailedError("Adjacent chunks should be in range -1 ... +1 coordinates");
					}
					$this->setChunk($x + $relativeX, $z + $relativeZ, $adjacentChunk);
				}

				if(($oldChunk === null or !$oldChunk->isPopulated()) and $chunk->isPopulated()){
					(new ChunkPopulateEvent($this, $x, $z, $chunk))->call();

					foreach($this->getChunkListeners($x, $z) as $listener){
						$listener->onChunkPopulated($x, $z, $chunk);
					}
				}
			}else{
				$this->logger->debug("Discarding population result for chunk x=$x,z=$z - terrain was modified on the main thread before async population completed");
			}

			//This needs to be in this specific spot because user code might call back to orderChunkPopulation().
			//If it does, and finds the promise, and doesn't find an active task associated with it, it will schedule
			//another PopulationTask. We don't want that because we're here processing the results.
			//We can't remove the promise from the array before setting the chunks in the world because that would lead
			//to the same problem. Therefore, it's necessary that this code be split into two if/else, with this in the
			//middle.
			unset($this->activeChunkPopulationTasks[$index]);

			if($dirtyChunks === 0){
				$promise = $this->chunkPopulationRequestMap[$index];
				unset($this->chunkPopulationRequestMap[$index]);
				$promise->resolve($chunk);
			}else{
				//request failed, stick it back on the queue
				//we didn't resolve the promise or touch it in any way, so any fake chunk loaders are still valid and
				//don't need to be added a second time.
				$this->addChunkHashToPopulationRequestQueue($index);
			}

			$this->drainPopulationRequestQueue();
		}
		Timings::$generationCallback->stopTiming();
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
