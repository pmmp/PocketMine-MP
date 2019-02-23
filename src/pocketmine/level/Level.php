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
use pocketmine\block\UnknownBlock;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\object\ExperienceOrb;
use pocketmine\entity\object\ItemEntity;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockUpdateEvent;
use pocketmine\event\level\ChunkLoadEvent;
use pocketmine\event\level\ChunkPopulateEvent;
use pocketmine\event\level\ChunkUnloadEvent;
use pocketmine\event\level\LevelSaveEvent;
use pocketmine\event\level\SpawnChangeEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemUseResult;
use pocketmine\level\biome\Biome;
use pocketmine\level\format\Chunk;
use pocketmine\level\format\ChunkException;
use pocketmine\level\format\EmptySubChunk;
use pocketmine\level\format\io\exception\CorruptedChunkException;
use pocketmine\level\format\io\exception\UnsupportedChunkFormatException;
use pocketmine\level\format\io\LevelProvider;
use pocketmine\level\generator\Generator;
use pocketmine\level\generator\GeneratorManager;
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
use pocketmine\math\Vector3;
use pocketmine\metadata\BlockMetadataStore;
use pocketmine\metadata\Metadatable;
use pocketmine\metadata\MetadataValue;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\ChunkRequestTask;
use pocketmine\network\mcpe\CompressBatchPromise;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\network\mcpe\protocol\BlockEntityDataPacket;
use pocketmine\network\mcpe\protocol\ClientboundPacket;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\SetDifficultyPacket;
use pocketmine\network\mcpe\protocol\SetTimePacket;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use pocketmine\tile\Spawnable;
use pocketmine\tile\Tile;
use pocketmine\timings\Timings;
use pocketmine\utils\ReversePriorityQueue;
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
use const INT32_MAX;
use const INT32_MIN;
use const M_PI;

#include <rules/Level.h>

class Level implements ChunkManager, Metadatable{

	private static $levelIdCounter = 1;

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

	/** @var CompressBatchPromise[] */
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

	/** @var ChunkListener[][] */
	private $chunkListeners = [];

	/** @var ClientboundPacket[][] */
	private $chunkPackets = [];
	/** @var ClientboundPacket[] */
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
	/** @var bool[] blockhash => dummy */
	private $neighbourBlockUpdateQueueIndex = [];

	/** @var Player[][] */
	private $chunkSendQueue = [];
	/** @var ChunkRequestTask[] */
	private $chunkSendTasks = [];

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

	/** @var BlockMetadataStore */
	private $blockMetadata;

	/** @var Position */
	private $temporalPosition;
	/** @var Vector3 */
	private $temporalVector;

	/** @var int */
	private $sleepTicks = 0;

	/** @var int */
	private $chunkTickRadius;
	/** @var int */
	private $chunksPerTick;
	/** @var \SplFixedArray<bool> */
	private $randomTickBlocks = null;

	/** @var LevelTimings */
	public $timings;

	/** @var int */
	private $tickRate;
	/** @var int */
	public $tickRateTime = 0;
	/** @var int */
	public $tickRateCounter = 0;

	/** @var bool */
	private $doingTick = false;

	/** @var string|Generator */
	private $generator;

	/** @var bool */
	private $closed = false;

	/** @var BlockLightUpdate|null */
	private $blockLightUpdate = null;
	/** @var SkyLightUpdate|null */
	private $skyLightUpdate = null;

	public static function chunkHash(int $x, int $z) : int{
		return (($x & 0xFFFFFFFF) << 32) | ($z & 0xFFFFFFFF);
	}

	public static function blockHash(int $x, int $y, int $z) : int{
		if($y < 0 or $y >= Level::Y_MAX){
			throw new \InvalidArgumentException("Y coordinate $y is out of range!");
		}
		return (($x & 0xFFFFFFF) << 36) | (($y & Level::Y_MASK) << 28) | ($z & 0xFFFFFFF);
	}

	/**
	 * Computes a small index relative to chunk base from the given coordinates.
	 *
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 *
	 * @return int
	 */
	public static function chunkBlockHash(int $x, int $y, int $z) : int{
		return ($y << 8) | (($z & 0xf) << 4) | ($x & 0xf);
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

	/**
	 * @param string $str
	 *
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
		$this->levelId = static::$levelIdCounter++;
		$this->blockMetadata = new BlockMetadataStore($this);
		$this->server = $server;

		$this->provider = $provider;

		$this->displayName = $this->provider->getLevelData()->getName();
		$this->worldHeight = $this->provider->getWorldHeight();

		$this->server->getLogger()->info($this->server->getLanguage()->translateString("pocketmine.level.preparing", [$this->displayName]));
		$this->generator = GeneratorManager::getGenerator($this->provider->getLevelData()->getGenerator());
		//TODO: validate generator options

		$this->folderName = $name;

		$this->scheduledBlockUpdateQueue = new ReversePriorityQueue();
		$this->scheduledBlockUpdateQueue->setExtractFlags(\SplPriorityQueue::EXTR_BOTH);

		$this->neighbourBlockUpdateQueue = new \SplQueue();

		$this->time = $this->provider->getLevelData()->getTime();

		$this->chunkTickRadius = min($this->server->getViewDistance(), max(1, (int) $this->server->getProperty("chunk-ticking.tick-radius", 4)));
		$this->chunksPerTick = (int) $this->server->getProperty("chunk-ticking.per-tick", 40);
		$this->chunkPopulationQueueSize = (int) $this->server->getProperty("chunk-generation.population-queue-size", 2);

		$dontTickBlocks = array_fill_keys($this->server->getProperty("chunk-ticking.disable-block-ticking", []), true);

		$this->randomTickBlocks = new \SplFixedArray(4096);
		foreach($this->randomTickBlocks as $i => $null){
			$id = $i >> 4;
			$meta = $i & 0xf;
			try{
				$block = BlockFactory::get($id, $meta); //Make sure it's a copy
			}catch(\InvalidArgumentException $e){
				continue;
			}
			if(!isset($dontTickBlocks[$id]) and $block->ticksRandomly()){
				$this->randomTickBlocks[($id << 4) | $meta] = true;
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

	public function registerGeneratorToWorker(int $worker) : void{
		$this->generatorRegisteredWorkers[$worker] = true;
		$this->server->getAsyncPool()->submitTaskToWorker(new GeneratorRegisterTask($this, $this->generator, $this->provider->getLevelData()->getGeneratorOptions()), $worker);
	}

	public function unregisterGenerator(){
		$pool = $this->server->getAsyncPool();
		foreach($pool->getRunningWorkers() as $i){
			if(isset($this->generatorRegisteredWorkers[$i])){
				$pool->submitTaskToWorker(new GeneratorUnregisterTask($this), $i);
			}
		}
		$this->generatorRegisteredWorkers = [];
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

	/**
	 * @internal
	 */
	public function close(){
		if($this->closed){
			throw new \InvalidStateException("Tried to close a world which is already closed");
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

	public function addSound(Vector3 $pos, Sound $sound, ?array $players = null){
		$pk = $sound->encode($pos);
		if(!is_array($pk)){
			$pk = [$pk];
		}
		if(!empty($pk)){
			if($players === null){
				foreach($pk as $e){
					$this->broadcastPacketToViewers($pos, $e);
				}
			}else{
				$this->server->broadcastPackets($players, $pk);
			}
		}
	}

	public function addParticle(Vector3 $pos, Particle $particle, ?array $players = null){
		$pk = $particle->encode($pos);
		if(!is_array($pk)){
			$pk = [$pk];
		}
		if(!empty($pk)){
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
			$this->broadcastPacketToViewers($pos, $pk);
		}else{
			$pk->position = null;
			$this->broadcastGlobalPacket($pk);
		}
	}

	/**
	 * Broadcasts a LevelSoundEvent to players in the area.
	 *
	 * @param Vector3 $pos
	 * @param int     $soundId
	 * @param int     $extraData
	 * @param int     $entityTypeId
	 * @param bool    $isBabyMob
	 * @param bool    $disableRelativeVolume If true, all players receiving this sound-event will hear the sound at full volume regardless of distance
	 */
	public function broadcastLevelSoundEvent(Vector3 $pos, int $soundId, int $extraData = -1, int $entityTypeId = -1, bool $isBabyMob = false, bool $disableRelativeVolume = false){
		$pk = new LevelSoundEventPacket();
		$pk->sound = $soundId;
		$pk->extraData = $extraData;
		$pk->entityType = AddEntityPacket::LEGACY_ID_MAP_BC[$entityTypeId] ?? ":";
		$pk->isBabyMob = $isBabyMob;
		$pk->disableRelativeVolume = $disableRelativeVolume;
		$pk->position = $pos->asVector3();
		$this->broadcastPacketToViewers($pos, $pk);
	}

	public function getAutoSave() : bool{
		return $this->autoSave;
	}

	public function setAutoSave(bool $value){
		$this->autoSave = $value;
	}

	/**
	 * @deprecated WARNING: This function has a misleading name. Contrary to what the name might imply, this function
	 * DOES NOT return players who are IN a chunk, rather, it returns players who can SEE the chunk.
	 *
	 * Returns a list of players who have the target chunk within their view distance.
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
	 * Returns an array of players who have the target position within their view distance.
	 * @param Vector3 $pos
	 *
	 * @return Player[]
	 */
	public function getViewersForPosition(Vector3 $pos) : array{
		return $this->getChunkPlayers($pos->getFloorX() >> 4, $pos->getFloorZ() >> 4);
	}

	/**
	 * Queues a packet to be sent to all players using the chunk at the specified X/Z coordinates at the end of the
	 * current tick.
	 *
	 * @param int               $chunkX
	 * @param int               $chunkZ
	 * @param ClientboundPacket $packet
	 */
	public function addChunkPacket(int $chunkX, int $chunkZ, ClientboundPacket $packet){
		if(!isset($this->chunkPackets[$index = Level::chunkHash($chunkX, $chunkZ)])){
			$this->chunkPackets[$index] = [$packet];
		}else{
			$this->chunkPackets[$index][] = $packet;
		}
	}

	/**
	 * Broadcasts a packet to every player who has the target position within their view distance.
	 *
	 * @param Vector3           $pos
	 * @param ClientboundPacket $packet
	 */
	public function broadcastPacketToViewers(Vector3 $pos, ClientboundPacket $packet) : void{
		$this->addChunkPacket($pos->getFloorX() >> 4, $pos->getFloorZ() >> 4, $packet);
	}

	/**
	 * Broadcasts a packet to every player in the level.
	 *
	 * @param ClientboundPacket $packet
	 */
	public function broadcastGlobalPacket(ClientboundPacket $packet) : void{
		$this->globalPackets[] = $packet;
	}

	public function registerChunkLoader(ChunkLoader $loader, int $chunkX, int $chunkZ, bool $autoLoad = true){
		$loaderId = spl_object_id($loader);

		if(!isset($this->chunkLoaders[$chunkHash = Level::chunkHash($chunkX, $chunkZ)])){
			$this->chunkLoaders[$chunkHash] = [];
			$this->playerLoaders[$chunkHash] = [];
		}elseif(isset($this->chunkLoaders[$chunkHash][$loaderId])){
			return;
		}

		$this->chunkLoaders[$chunkHash][$loaderId] = $loader;
		if($loader instanceof Player){
			$this->playerLoaders[$chunkHash][$loaderId] = $loader;
		}

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

	public function unregisterChunkLoader(ChunkLoader $loader, int $chunkX, int $chunkZ){
		$chunkHash = Level::chunkHash($chunkX, $chunkZ);
		$loaderId = spl_object_id($loader);
		if(isset($this->chunkLoaders[$chunkHash][$loaderId])){
			unset($this->chunkLoaders[$chunkHash][$loaderId]);
			unset($this->playerLoaders[$chunkHash][$loaderId]);
			if(count($this->chunkLoaders[$chunkHash]) === 0){
				unset($this->chunkLoaders[$chunkHash]);
				unset($this->playerLoaders[$chunkHash]);
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
	 *
	 * @param ChunkListener $listener
	 * @param int           $chunkX
	 * @param int           $chunkZ
	 */
	public function registerChunkListener(ChunkListener $listener, int $chunkX, int $chunkZ) : void{
		$hash = Level::chunkHash($chunkX, $chunkZ);
		if(isset($this->chunkListeners[$hash])){
			$this->chunkListeners[$hash][spl_object_id($listener)] = $listener;
		}else{
			$this->chunkListeners[$hash] = [spl_object_id($listener) => $listener];
		}
	}

	/**
	 * Unregisters a chunk listener previously registered.
	 * @see Level::registerChunkListener()
	 *
	 * @param ChunkListener $listener
	 * @param int           $chunkX
	 * @param int           $chunkZ
	 */
	public function unregisterChunkListener(ChunkListener $listener, int $chunkX, int $chunkZ) : void{
		$hash = Level::chunkHash($chunkX, $chunkZ);
		if(isset($this->chunkListeners[$hash])){
			unset($this->chunkListeners[$hash][spl_object_id($listener)]);
			if(empty($this->chunkListeners[$hash])){
				unset($this->chunkListeners[$hash]);
			}
		}
	}

	/**
	 * Unregisters a chunk listener from all chunks it is listening on in this Level.
	 *
	 * @param ChunkListener $listener
	 */
	public function unregisterChunkListenerFromAll(ChunkListener $listener) : void{
		$id = spl_object_id($listener);
		foreach($this->chunkListeners as $hash => $listeners){
			if(isset($listeners[$id])){
				unset($this->chunkListeners[$hash][$id]);
				if(empty($this->chunkListeners[$hash])){
					unset($this->chunkListeners[$hash]);
				}
			}
		}
	}

	/**
	 * Returns all the listeners attached to this chunk.
	 *
	 * @param int $chunkX
	 * @param int $chunkZ
	 *
	 * @return ChunkListener[]
	 */
	public function getChunkListeners(int $chunkX, int $chunkZ) : array{
		return $this->chunkListeners[Level::chunkHash($chunkX, $chunkZ)] ?? [];
	}

	/**
	 * @internal
	 *
	 * @param Player ...$targets If empty, will send to all players in the level.
	 */
	public function sendTime(Player ...$targets){
		$pk = new SetTimePacket();
		$pk->time = $this->time;

		if(empty($targets)){
			$this->broadcastGlobalPacket($pk);
		}else{
			$this->server->broadcastPacket($targets, $pk);
		}
	}

	public function isDoingTick() : bool{
		return $this->doingTick;
	}

	/**
	 * @internal
	 *
	 * @param int $currentTick
	 *
	 */
	public function doTick(int $currentTick){
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
			$this->time++;
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
			$block = $this->getBlock($this->scheduledBlockUpdateQueue->extract()["data"]);
			unset($this->scheduledBlockUpdateQueueIndex[Level::blockHash($block->x, $block->y, $block->z)]);
			$block->onScheduledUpdate();
		}

		//Normal updates
		while($this->neighbourBlockUpdateQueue->count() > 0){
			$index = $this->neighbourBlockUpdateQueue->dequeue();
			Level::getBlockXYZ($index, $x, $y, $z);

			$block = $this->getBlockAt($x, $y, $z);
			$block->readStateFromWorld(); //for blocks like fences, force recalculation of connected AABBs

			$ev = new BlockUpdateEvent($block);
			$ev->call();
			if(!$ev->isCancelled()){
				foreach($this->getNearbyEntities(AxisAlignedBB::one()->offset($block->x, $block->y, $block->z)) as $entity){
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

		$this->timings->tileEntityTick->startTiming();
		Timings::$tickTileEntityTimer->startTiming();
		//Update tiles that need update
		foreach($this->updateTiles as $blockHash => $tile){
			if(!$tile->onUpdate()){
				unset($this->updateTiles[$blockHash]);
			}
			if(!$tile->isClosed() and $tile instanceof Spawnable and $tile->isDirty()){
				$chunkHash = Level::chunkHash($tile->getFloorX() >> 4, $tile->getFloorZ() >> 4);
				if(!isset($this->changedBlocks[$chunkHash])){
					$this->changedBlocks[$chunkHash] = [$blockHash => $tile];
				}else{
					$this->changedBlocks[$chunkHash][$blockHash] = $tile;
				}
				$tile->setDirty(false);
			}
		}
		Timings::$tickTileEntityTimer->stopTiming();
		$this->timings->tileEntityTick->stopTiming();

		$this->timings->doTickTiles->startTiming();
		$this->tickChunks();
		$this->timings->doTickTiles->stopTiming();

		$this->executeQueuedLightUpdates();

		if(count($this->changedBlocks) > 0){
			if(count($this->players) > 0){
				foreach($this->changedBlocks as $index => $blocks){
					if(empty($blocks)){ //blocks can be set normally and then later re-set with direct send
						continue;
					}
					unset($this->chunkCache[$index]);
					Level::getXZ($index, $chunkX, $chunkZ);
					if(count($blocks) > 512){
						$chunk = $this->getChunk($chunkX, $chunkZ);
						foreach($this->getChunkPlayers($chunkX, $chunkZ) as $p){
							$p->onChunkChanged($chunk);
						}
					}else{
						$this->sendBlocks($this->getChunkPlayers($chunkX, $chunkZ), $blocks);
					}
				}
			}else{
				$this->chunkCache = [];
			}

			$this->changedBlocks = [];

		}

		foreach($this->players as $p){
			$p->doChunkRequests();
		}
		$this->processChunkRequests();

		if($this->sleepTicks > 0 and --$this->sleepTicks <= 0){
			$this->checkSleep();
		}

		if(!empty($this->globalPackets)){
			if(!empty($this->players)){
				$this->server->broadcastPackets($this->players, $this->globalPackets);
			}
			$this->globalPackets = [];
		}

		foreach($this->chunkPackets as $index => $entries){
			Level::getXZ($index, $chunkX, $chunkZ);
			$chunkPlayers = $this->getChunkPlayers($chunkX, $chunkZ);
			if(count($chunkPlayers) > 0){
				$this->server->broadcastPackets($chunkPlayers, $entries);
			}
		}

		$this->chunkPackets = [];
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
	 * @param Player[]  $target
	 * @param Vector3[] $blocks
	 */
	public function sendBlocks(array $target, array $blocks){
		$packets = [];

		foreach($blocks as $b){
			if(!($b instanceof Vector3)){
				throw new \TypeError("Expected Vector3 in blocks array, got " . (is_object($b) ? get_class($b) : gettype($b)));
			}
			$pk = new UpdateBlockPacket();

			$pk->x = $b->x;
			$pk->y = $b->y;
			$pk->z = $b->z;

			if($b instanceof Block){
				$pk->blockRuntimeId = $b->getRuntimeId();
			}else{
				$fullBlock = $this->getFullBlock($b->x, $b->y, $b->z);
				$pk->blockRuntimeId = BlockFactory::toStaticRuntimeId($fullBlock >> 4, $fullBlock & 0xf);
			}
			$packets[] = $pk;

			$tile = $this->getTileAt($b->x, $b->y, $b->z);
			if($tile instanceof Spawnable){
				$tilepk = new BlockEntityDataPacket();
				$tilepk->x = $tile->x;
				$tilepk->y = $tile->y;
				$tilepk->z = $tile->z;
				$tilepk->namedtag = $tile->getSerializedSpawnCompound();
				$packets[] = $tilepk;
			}
		}

		$this->server->broadcastPackets($target, $packets);
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

	public function addRandomTickedBlock(int $id, int $variant = 0){
		$block = BlockFactory::get($id, $variant);
		if($block instanceof UnknownBlock){
			throw new \InvalidArgumentException("ID $id variant $variant is unknown, cannot do random-tick");
		}
		$this->randomTickBlocks[($id << 4) | $variant] = true;
	}

	public function removeRandomTickedBlock(int $id, int $variant = 0){
		$this->randomTickBlocks[($id << 4) | $variant] = null;
	}

	private function tickChunks(){
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
				$hash = Level::chunkHash($dx + $chunkX, $dz + $chunkZ);
				if(!isset($chunkTickList[$hash]) and isset($this->chunks[$hash])){
					//check adjacent chunks are loaded
					for($cx = -1; $cx <= 1; ++$cx){
						for($cz = -1; $cz <= 1; ++$cz){
							if(!isset($this->chunks[Level::chunkHash($chunkX + $dx + $cx, $chunkZ + $dz + $cz)])){
								continue 3;
							}
						}
					}
					$chunkTickList[$hash] = true;
				}
			}
		}

		foreach($chunkTickList as $index => $_){
			Level::getXZ($index, $chunkX, $chunkZ);

			$chunk = $this->chunks[$index];
			foreach($chunk->getEntities() as $entity){
				$entity->scheduleUpdate();
			}


			foreach($chunk->getSubChunks() as $Y => $subChunk){
				if(!($subChunk instanceof EmptySubChunk)){
					$k = mt_rand(0, 0xfffffffff); //36 bits
					for($i = 0; $i < 3; ++$i){
						$x = $k & 0x0f;
						$y = ($k >> 4) & 0x0f;
						$z = ($k >> 8) & 0x0f;
						$k >>= 12;

						$state = $subChunk->getFullBlock($x, $y, $z);

						if($this->randomTickBlocks[$state & ~BlockFactory::getStateMask($state >> 4)]){
							/** @var Block $block */
							$block = BlockFactory::fromFullBlock($state, $this->temporalPosition->setComponents(
								$chunkX * 16 + $x,
								($Y << 4) + $y,
								$chunkZ * 16 + $z
							));
							$block->onRandomTick();
						}
					}
				}
			}
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

		(new LevelSaveEvent($this))->call();

		$this->provider->getLevelData()->setTime($this->time);
		$this->saveChunks();
		$this->provider->getLevelData()->save();

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

	private function tryAddToNeighbourUpdateQueue(Vector3 $pos) : void{
		if($this->isInWorld($pos->x, $pos->y, $pos->z)){
			$hash = Level::blockHash($pos->x, $pos->y, $pos->z);
			if(!isset($this->neighbourBlockUpdateQueueIndex[$hash])){
				$this->neighbourBlockUpdateQueue->enqueue($hash);
				$this->neighbourBlockUpdateQueueIndex[$hash] = true;
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
	 * @param int $x
	 * @param int $y
	 * @param int $z
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
		$relativeBlockHash = null;
		$chunkHash = Level::chunkHash($x >> 4, $z >> 4);

		if($this->isInWorld($x, $y, $z)){
			$relativeBlockHash = Level::chunkBlockHash($x, $y, $z);

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

		$block = BlockFactory::fromFullBlock($fullState);
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
		$source = $this->getBlockAt($x, $y, $z);

		$yPlusOne = $y + 1;

		if($yPlusOne === $oldHeightMap){ //Block changed directly beneath the heightmap. Check if a block was removed or changed to a different light-filter.
			$newHeightMap = $this->getChunk($x >> 4, $z >> 4)->recalculateHeightMapColumn($x & 0x0f, $z & 0x0f);
		}elseif($yPlusOne > $oldHeightMap){ //Block changed above the heightmap.
			if($source->getLightFilter() > 0 or $source->diffusesSkyLight()){
				$this->setHeightMap($x, $z, $yPlusOne);
				$newHeightMap = $yPlusOne;
			}else{ //Block changed which has no effect on direct sky light, for example placing or removing glass.
				$this->timings->doBlockSkyLightUpdates->stopTiming();
				return;
			}
		}else{ //Block changed below heightmap
			$newHeightMap = $oldHeightMap;
		}

		if($this->skyLightUpdate === null){
			$this->skyLightUpdate = new SkyLightUpdate($this);
		}
		if($newHeightMap > $oldHeightMap){ //Heightmap increase, block placed, remove sky light
			for($i = $y; $i >= $oldHeightMap; --$i){
				$this->skyLightUpdate->setAndUpdateLight($x, $i, $z, 0); //Remove all light beneath, adjacent recalculation will handle the rest.
			}
		}elseif($newHeightMap < $oldHeightMap){ //Heightmap decrease, block changed or removed, add sky light
			for($i = $y; $i >= $newHeightMap; --$i){
				$this->skyLightUpdate->setAndUpdateLight($x, $i, $z, 15);
			}
		}else{ //No heightmap change, block changed "underground" - trigger recalculation from surroundings
			$this->skyLightUpdate->setAndUpdateLight($x, $y, $z, 0);
		}

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

		$block = $this->getBlockAt($x, $y, $z);
		if($this->blockLightUpdate === null){
			$this->blockLightUpdate = new BlockLightUpdate($this);
		}
		$this->blockLightUpdate->setAndUpdateLight($x, $y, $z, $block->getLightLevel());

		$this->timings->doBlockLightUpdates->stopTiming();
	}

	public function executeQueuedLightUpdates() : void{
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
	 * Sets the block at the given Vector3 coordinates.
	 * @see Level::setBlockAt()
	 *
	 * @param Vector3 $pos
	 * @param Block   $block
	 * @param bool    $update
	 *
	 * @return bool Whether the block has been updated or not
	 *
	 * @throws \InvalidArgumentException if the position is out of the world bounds
	 */
	public function setBlock(Vector3 $pos, Block $block, bool $update = true) : bool{
		return $this->setBlockAt((int) floor($pos->x), (int) floor($pos->y), (int) floor($pos->z), $block, $update);
	}

	/**
	 * Sets the block at the given coordinates.
	 *
	 * If $update is true, it'll get the neighbour blocks (6 sides) and update them, and also update local lighting.
	 * If you are doing big changes, you might want to set this to false, then update manually.
	 *
	 * @param int   $x
	 * @param int   $y
	 * @param int   $z
	 * @param Block $block
	 * @param bool  $update
	 *
	 * @return bool Whether the block has been updated or not
	 *
	 * @throws \InvalidArgumentException if the position is out of the world bounds
	 */
	public function setBlockAt(int $x, int $y, int $z, Block $block, bool $update = true) : bool{
		if(!$this->isInWorld($x, $y, $z)){
			throw new \InvalidArgumentException("Pos x=$x,y=$y,z=$z is outside of the world bounds");
		}

		$this->timings->setBlock->startTiming();

		$block = clone $block;

		$block->position($this, $x, $y, $z);
		$block->writeStateToWorld();

		$chunkHash = Level::chunkHash($x >> 4, $z >> 4);
		$relativeBlockHash = Level::chunkBlockHash($x, $y, $z);

		unset($this->blockCache[$chunkHash][$relativeBlockHash]);

		if(!isset($this->changedBlocks[$chunkHash])){
			$this->changedBlocks[$chunkHash] = [];
		}
		$this->changedBlocks[$chunkHash][$relativeBlockHash] = $block;

		foreach($this->getChunkListeners($x >> 4, $z >> 4) as $listener){
			$listener->onBlockChanged($block);
		}

		if($update){
			$this->updateAllLight($block);
			$this->tryAddToNeighbourUpdateQueue($block);
			foreach($block->sides() as $side){
				$this->tryAddToNeighbourUpdateQueue($side);
			}
		}

		$this->timings->setBlock->stopTiming();

		return true;
	}

	/**
	 * @param Vector3 $source
	 * @param Item    $item
	 * @param Vector3 $motion
	 * @param int     $delay
	 *
	 * @return ItemEntity|null
	 */
	public function dropItem(Vector3 $source, Item $item, ?Vector3 $motion = null, int $delay = 10){
		$motion = $motion ?? new Vector3(lcg_value() * 0.2 - 0.1, 0.2, lcg_value() * 0.2 - 0.1);
		$itemTag = $item->nbtSerialize();
		$itemTag->setName("Item");

		if(!$item->isNull()){
			$nbt = EntityFactory::createBaseNBT($source, $motion, lcg_value() * 360, 0);
			$nbt->setShort("Health", 5);
			$nbt->setShort("PickupDelay", $delay);
			$nbt->setTag($itemTag);

			/** @var ItemEntity $itemEntity */
			$itemEntity = EntityFactory::create(ItemEntity::class, $this, $nbt);
			$itemEntity->spawnToAll();
			return $itemEntity;
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
			$nbt = EntityFactory::createBaseNBT(
				$pos,
				$this->temporalVector->setComponents((lcg_value() * 0.2 - 0.1) * 2, lcg_value() * 0.4, (lcg_value() * 0.2 - 0.1) * 2),
				lcg_value() * 360,
				0
			);
			$nbt->setShort(ExperienceOrb::TAG_VALUE_PC, $split);

			/** @var ExperienceOrb $orb */
			$orb = EntityFactory::create(ExperienceOrb::class, $this, $nbt);
			$orb->spawnToAll();
			$orbs[] = $orb;
		}

		return $orbs;
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
	public function useBreakOn(Vector3 $vector, Item &$item = null, ?Player $player = null, bool $createParticles = false) : bool{
		$target = $this->getBlock($vector);
		$affectedBlocks = $target->getAffectedBlocks();

		if($item === null){
			$item = ItemFactory::air();
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
			}

			if($player->isAdventure(true) and !$ev->isCancelled()){
				$tag = $item->getNamedTagEntry("CanDestroy");
				$canBreak = false;
				if($tag instanceof ListTag){
					foreach($tag as $v){
						if($v instanceof StringTag){
							$entry = ItemFactory::fromString($v->getValue());
							if($entry->getBlock()->isSameType($target)){
								$canBreak = true;
								break;
							}
						}
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
			$this->addParticle($target->add(0.5, 0.5, 0.5), new DestroyBlockParticle($target));
		}

		$target->onBreak($item, $player);

		$tile = $this->getTile($target);
		if($tile !== null){
			$tile->onBlockDestroyed();
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
	public function useItemOn(Vector3 $vector, Item &$item, int $face, ?Vector3 $clickVector = null, ?Player $player = null, bool $playSound = false) : bool{
		$blockClicked = $this->getBlock($vector);
		$blockReplace = $blockClicked->getSide($face);

		if($clickVector === null){
			$clickVector = new Vector3(0.0, 0.0, 0.0);
		}

		if(!$this->isInWorld($blockReplace->x, $blockReplace->y, $blockReplace->z)){
			//TODO: build height limit messages for custom world heights and mcregion cap
			return false;
		}

		if($blockClicked->getId() === Block::AIR){
			return false;
		}

		if($player !== null){
			$ev = new PlayerInteractEvent($player, $item, $blockClicked, $clickVector, $face, PlayerInteractEvent::RIGHT_CLICK_BLOCK);
			$ev->call();
			if(!$ev->isCancelled()){
				if(!$player->isSneaking() and $blockClicked->onActivate($item, $face, $clickVector, $player)){
					return true;
				}

				if(!$player->isSneaking()){
					$result = $item->onActivate($player, $blockReplace, $blockClicked, $face, $clickVector);
					if($result !== ItemUseResult::NONE()){
						return $result === ItemUseResult::SUCCESS();
					}
				}
			}else{
				return false;
			}
		}elseif($blockClicked->onActivate($item, $face, $clickVector, $player)){
			return true;
		}

		if($item->canBePlaced()){
			$hand = $item->getBlock();
			$hand->position($this, $blockReplace->x, $blockReplace->y, $blockReplace->z);
		}else{
			return false;
		}

		if($hand->canBePlacedAt($blockClicked, $clickVector, $face, true)){
			$blockReplace = $blockClicked;
			$hand->position($this, $blockReplace->x, $blockReplace->y, $blockReplace->z);
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
						$bb = $player->getBoundingBox()->offsetCopy($diff->x, $diff->y, $diff->z);
						if($collisionBox->intersectsWith($bb)){
							return false; //Inside player BB
						}
					}
				}
			}
		}


		if($player !== null){
			$ev = new BlockPlaceEvent($player, $hand, $blockReplace, $blockClicked, $item);
			if($player->isAdventure(true) and !$ev->isCancelled()){
				$canPlace = false;
				$tag = $item->getNamedTagEntry("CanPlaceOn");
				if($tag instanceof ListTag){
					foreach($tag as $v){
						if($v instanceof StringTag){
							$entry = ItemFactory::fromString($v->getValue());
							if($entry->getBlock()->isSameType($blockClicked)){
								$canPlace = true;
								break;
							}
						}
					}
				}

				$ev->setCancelled(!$canPlace);
			}

			$ev->call();
			if($ev->isCancelled()){
				return false;
			}
		}

		if(!$hand->place($item, $blockReplace, $blockClicked, $face, $clickVector, $player)){
			return false;
		}
		$tile = $this->getTile($hand);
		if($tile !== null){
			//TODO: seal this up inside block placement
			$tile->copyDataFromItem($item);
		}

		if($playSound){
			$this->broadcastLevelSoundEvent($hand, LevelSoundEventPacket::SOUND_PLACE, $hand->getRuntimeId());
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
	public function getCollidingEntities(AxisAlignedBB $bb, ?Entity $entity = null) : array{
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
	public function getNearbyEntities(AxisAlignedBB $bb, ?Entity $entity = null) : array{
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
		return ($chunk = $this->getChunk($x >> 4, $z >> 4)) !== null ? $chunk->getTile($x & 0x0f, $y, $z & 0x0f) : null;
	}

	/**
	 * Returns a list of the entities on a given chunk
	 *
	 * @param int $X
	 * @param int $Z
	 *
	 * @return Entity[]
	 */
	public function getChunkEntities(int $X, int $Z) : array{
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
	public function getChunkTiles(int $X, int $Z) : array{
		return ($chunk = $this->getChunk($X, $Z)) !== null ? $chunk->getTiles() : [];
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
	 * Returns the chunk containing the given Vector3 position.
	 *
	 * @param Vector3 $pos
	 * @param bool    $create
	 *
	 * @return null|Chunk
	 */
	public function getChunkAtPosition(Vector3 $pos, bool $create = false) : ?Chunk{
		return $this->getChunk($pos->getFloorX() >> 4, $pos->getFloorZ() >> 4, $create);
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

	public function lockChunk(int $chunkX, int $chunkZ) : void{
		$chunkHash = Level::chunkHash($chunkX, $chunkZ);
		if(isset($this->chunkLock[$chunkHash])){
			throw new \InvalidArgumentException("Chunk $chunkX $chunkZ is already locked");
		}
		$this->chunkLock[$chunkHash] = true;
	}

	public function unlockChunk(int $chunkX, int $chunkZ) : void{
		unset($this->chunkLock[Level::chunkHash($chunkX, $chunkZ)]);
	}

	public function isChunkLocked(int $chunkX, int $chunkZ) : bool{
		return isset($this->chunkLock[Level::chunkHash($chunkX, $chunkZ)]);
	}

	public function generateChunkCallback(int $x, int $z, ?Chunk $chunk){
		Timings::$generationCallbackTimer->startTiming();
		if(isset($this->chunkPopulationQueue[$index = Level::chunkHash($x, $z)])){
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
	 * @param int        $chunkX
	 * @param int        $chunkZ
	 * @param Chunk|null $chunk
	 * @param bool       $deleteEntitiesAndTiles Whether to delete entities and tiles on the old chunk, or transfer them to the new one
	 */
	public function setChunk(int $chunkX, int $chunkZ, ?Chunk $chunk, bool $deleteEntitiesAndTiles = true){
		if($chunk === null){
			return;
		}

		$chunk->setX($chunkX);
		$chunk->setZ($chunkZ);

		$chunkHash = Level::chunkHash($chunkX, $chunkZ);
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
		unset($this->chunkCache[$chunkHash]);
		unset($this->changedBlocks[$chunkHash]);
		if(isset($this->chunkSendTasks[$chunkHash])){ //invalidate pending caches
			$this->chunkSendTasks[$chunkHash]->cancelRun();
			unset($this->chunkSendTasks[$chunkHash]);
		}
		$chunk->setChanged();

		if(!$this->isChunkInUse($chunkX, $chunkZ)){
			$this->unloadChunkRequest($chunkX, $chunkZ);
		}else{
			foreach($this->getChunkListeners($chunkX, $chunkZ) as $listener){
				$listener->onChunkChanged($chunk);
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
	 * Returns whether the given position is in a loaded area of terrain.
	 *
	 * @param Vector3 $pos
	 *
	 * @return bool
	 */
	public function isInLoadedTerrain(Vector3 $pos) : bool{
		return $this->isChunkLoaded($pos->getFloorX() >> 4, $pos->getFloorZ() >> 4);
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
		return Position::fromObject($this->provider->getLevelData()->getSpawn(), $this);
	}

	/**
	 * Sets the level spawn location
	 *
	 * @param Vector3 $pos
	 */
	public function setSpawnLocation(Vector3 $pos){
		$previousSpawn = $this->getSpawnLocation();
		$this->provider->getLevelData()->setSpawn($pos);
		(new SpawnChangeEvent($this, $previousSpawn))->call();
	}

	public function requestChunk(int $x, int $z, Player $player){
		$index = Level::chunkHash($x, $z);
		if(!isset($this->chunkSendQueue[$index])){
			$this->chunkSendQueue[$index] = [];
		}

		$this->chunkSendQueue[$index][spl_object_id($player)] = $player;
	}

	private function sendCachedChunk(int $x, int $z){
		if(isset($this->chunkSendQueue[$index = Level::chunkHash($x, $z)])){
			foreach($this->chunkSendQueue[$index] as $player){
				/** @var Player $player */
				if($player->isConnected() and isset($player->usedChunks[$index])){
					$player->sendChunk($x, $z, $this->chunkCache[$index]);
				}
			}
			unset($this->chunkSendQueue[$index]);
		}
	}

	private function processChunkRequests(){
		if(count($this->chunkSendQueue) > 0){
			$this->timings->syncChunkSendTimer->startTiming();

			foreach($this->chunkSendQueue as $index => $players){
				Level::getXZ($index, $x, $z);

				if(isset($this->chunkSendTasks[$index])){
					if($this->chunkSendTasks[$index]->isCrashed()){
						unset($this->chunkSendTasks[$index]);
						$this->server->getLogger()->error("Failed to prepare chunk $x $z for sending, retrying");
					}else{
						//Not ready for sending yet
						continue;
					}
				}

				if(isset($this->chunkCache[$index])){
					$this->sendCachedChunk($x, $z);
					continue;
				}

				$this->timings->syncChunkSendPrepareTimer->startTiming();

				$chunk = $this->chunks[$index] ?? null;
				if(!($chunk instanceof Chunk)){
					throw new ChunkException("Invalid Chunk sent");
				}
				assert($chunk->getX() === $x and $chunk->getZ() === $z, "Chunk coordinate mismatch: expected $x $z, but chunk has coordinates " . $chunk->getX() . " " . $chunk->getZ() . ", did you forget to clone a chunk before setting?");

				/*
				 * we don't send promises directly to the players here because unresolved promises of chunk sending
				 * would slow down the sending of other packets, especially if a chunk takes a long time to prepare.
				 */

				$promise = new CompressBatchPromise();
				$promise->onResolve(function(CompressBatchPromise $promise) use ($x, $z, $index): void{
					if(!$this->closed){
						$this->timings->syncChunkSendTimer->startTiming();

						unset($this->chunkSendTasks[$index]);

						$this->chunkCache[$index] = $promise;
						$this->sendCachedChunk($x, $z);
						if(!$this->server->getMemoryManager()->canUseChunkCache()){
							unset($this->chunkCache[$index]);
						}

						$this->timings->syncChunkSendTimer->stopTiming();
					}else{
						$this->server->getLogger()->debug("Dropped prepared chunk $x $z due to world not loaded");
					}
				});
				$this->server->getAsyncPool()->submitTask($task = new ChunkRequestTask($x, $z, $chunk, $promise));
				$this->chunkSendTasks[$index] = $task;

				$this->timings->syncChunkSendPrepareTimer->stopTiming();
			}

			$this->timings->syncChunkSendTimer->stopTiming();
		}
	}

	/**
	 * @param Entity $entity
	 *
	 * @throws \InvalidArgumentException
	 */
	public function addEntity(Entity $entity){
		if($entity->isClosed()){
			throw new \InvalidArgumentException("Attempted to add a garbage closed Entity to world");
		}
		if($entity->getLevel() !== $this){
			throw new \InvalidArgumentException("Invalid Entity world");
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
	 * @throws \InvalidArgumentException
	 */
	public function removeEntity(Entity $entity){
		if($entity->getLevel() !== $this){
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
	 * @param Tile $tile
	 *
	 * @throws \InvalidArgumentException
	 */
	public function addTile(Tile $tile){
		if($tile->isClosed()){
			throw new \InvalidArgumentException("Attempted to add a garbage closed Tile to world");
		}
		if($tile->getLevel() !== $this){
			throw new \InvalidArgumentException("Invalid Tile world");
		}

		$chunkX = $tile->getFloorX() >> 4;
		$chunkZ = $tile->getFloorZ() >> 4;

		if(isset($this->chunks[$hash = Level::chunkHash($chunkX, $chunkZ)])){
			$this->chunks[$hash]->addTile($tile);
		}else{
			throw new \InvalidStateException("Attempted to create tile " . get_class($tile) . " in unloaded chunk $chunkX $chunkZ");
		}

		$tile->scheduleUpdate();
	}

	/**
	 * @param Tile $tile
	 *
	 * @throws \InvalidArgumentException
	 */
	public function removeTile(Tile $tile){
		if($tile->getLevel() !== $this){
			throw new \InvalidArgumentException("Invalid Tile world");
		}

		unset($this->updateTiles[Level::blockHash($tile->x, $tile->y, $tile->z)]);

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
		}catch(CorruptedChunkException | UnsupportedChunkFormatException $e){
			$logger = $this->server->getLogger();
			$logger->critical("Failed to load chunk x=$x z=$z: " . $e->getMessage());
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

		if(!$chunk->isLightPopulated() and $chunk->isPopulated() and $this->getServer()->getProperty("chunk-ticking.light-updates", false)){
			$this->getServer()->getAsyncPool()->submitTask(new LightPopulationTask($this, $chunk));
		}

		if($this->isChunkInUse($x, $z)){
			foreach($this->getChunkListeners($x, $z) as $listener){
				$listener->onChunkLoaded($chunk);
			}
		}else{
			$this->server->getLogger()->debug("Newly loaded chunk $x $z has no loaders registered, will be unloaded at next available opportunity");
			$this->unloadChunkRequest($x, $z);
		}

		$this->timings->syncChunkLoadTimer->stopTiming();

		return true;
	}

	private function queueUnloadChunk(int $x, int $z){
		$this->unloadQueue[Level::chunkHash($x, $z)] = microtime(true);
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
			$ev = new ChunkUnloadEvent($this, $chunk);
			$ev->call();
			if($ev->isCancelled()){
				$this->timings->doChunkUnload->stopTiming();

				return false;
			}

			if($trySave and $this->getAutoSave() and $chunk->isGenerated()){
				if($chunk->hasChanged() or count($chunk->getTiles()) > 0 or count($chunk->getSavableEntities()) > 0){
					$this->provider->saveChunk($chunk);
				}
			}

			foreach($this->getChunkListeners($x, $z) as $listener){
				$listener->onChunkUnloaded($chunk);
			}

			$chunk->onUnload();
		}

		unset($this->chunks[$chunkHash]);
		unset($this->chunkCache[$chunkHash]);
		unset($this->blockCache[$chunkHash]);
		unset($this->changedBlocks[$chunkHash]);
		unset($this->chunkSendQueue[$chunkHash]);
		unset($this->chunkSendTasks[$chunkHash]);

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
		$spawn = $this->getSpawnLocation();
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
		$chunk = $this->getChunkAtPosition($v, false);
		$x = (int) $v->x;
		$z = (int) $v->z;
		if($chunk !== null and $chunk->isGenerated()){
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
	 * Returns the Level display name.
	 * WARNING: This is NOT guaranteed to be unique. Multiple levels at runtime may share the same display name.
	 *
	 * @return string
	 */
	public function getDisplayName() : string{
		return $this->displayName;
	}

	/**
	 * Returns the Level folder name. This will not change at runtime and will be unique to a level per runtime.
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
		return $this->provider->getLevelData()->getSeed();
	}

	public function getWorldHeight() : int{
		return $this->worldHeight;
	}

	/**
	 * @return int
	 */
	public function getDifficulty() : int{
		return $this->provider->getLevelData()->getDifficulty();
	}

	/**
	 * @param int $difficulty
	 */
	public function setDifficulty(int $difficulty){
		if($difficulty < 0 or $difficulty > 3){
			throw new \InvalidArgumentException("Invalid difficulty level $difficulty");
		}
		$this->provider->getLevelData()->setDifficulty($difficulty);

		$this->sendDifficulty();
	}

	/**
	 * @param Player ...$targets
	 */
	public function sendDifficulty(Player ...$targets){
		$pk = new SetDifficultyPacket();
		$pk->difficulty = $this->getDifficulty();
		if(empty($targets)){
			$this->broadcastGlobalPacket($pk);
		}else{
			$this->server->broadcastPacket($targets, $pk);
		}
	}

	public function populateChunk(int $x, int $z, bool $force = false) : bool{
		if(isset($this->chunkPopulationQueue[$index = Level::chunkHash($x, $z)]) or (count($this->chunkPopulationQueue) >= $this->chunkPopulationQueueSize and !$force)){
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

	public function doChunkGarbageCollection(){
		$this->timings->doChunkGC->startTiming();

		foreach($this->chunks as $index => $chunk){
			if(!isset($this->unloadQueue[$index])){
				Level::getXZ($index, $X, $Z);
				if(!$this->isSpawnChunk($X, $Z)){
					$this->unloadChunkRequest($X, $Z, true);
				}
			}
			$chunk->collectGarbage();
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
