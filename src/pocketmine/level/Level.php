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

/**
 * All Level related classes are here, like Generators, Populators, Noise, ...
 */
namespace pocketmine\level;

use pocketmine\block\Air;
use pocketmine\block\Beetroot;
use pocketmine\block\Block;
use pocketmine\block\BrownMushroom;
use pocketmine\block\Cactus;
use pocketmine\block\Carrot;
use pocketmine\block\Farmland;
use pocketmine\block\Grass;
use pocketmine\block\Ice;
use pocketmine\block\Leaves;
use pocketmine\block\Leaves2;
use pocketmine\block\MelonStem;
use pocketmine\block\Mycelium;
use pocketmine\block\Potato;
use pocketmine\block\PumpkinStem;
use pocketmine\block\RedMushroom;
use pocketmine\block\Sapling;
use pocketmine\block\SnowLayer;
use pocketmine\block\Sugarcane;
use pocketmine\block\Wheat;
use pocketmine\entity\DroppedItem;
use pocketmine\entity\Entity;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\level\ChunkLoadEvent;
use pocketmine\event\level\ChunkPopulateEvent;
use pocketmine\event\level\ChunkUnloadEvent;
use pocketmine\event\level\LevelSaveEvent;
use pocketmine\event\level\LevelUnloadEvent;
use pocketmine\event\level\SpawnChangeEvent;
use pocketmine\event\LevelTimings;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\inventory\InventoryHolder;
use pocketmine\item\Item;
use pocketmine\level\format\Chunk;
use pocketmine\level\format\FullChunk;
use pocketmine\level\format\generic\BaseLevelProvider;
use pocketmine\level\format\generic\EmptyChunkSection;
use pocketmine\level\format\LevelProvider;
use pocketmine\level\generator\Generator;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3;
use pocketmine\metadata\BlockMetadataStore;
use pocketmine\metadata\Metadatable;
use pocketmine\metadata\MetadataValue;
use pocketmine\nbt\tag\Byte;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Double;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Float;
use pocketmine\nbt\tag\Int;
use pocketmine\nbt\tag\Short;
use pocketmine\nbt\tag\String;
use pocketmine\network\protocol\SetTimePacket;
use pocketmine\network\protocol\UpdateBlockPacket;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\tile\Chest;
use pocketmine\tile\Sign;
use pocketmine\tile\Tile;
use pocketmine\utils\Cache;
use pocketmine\utils\ReversePriorityQueue;
use pocketmine\utils\TextFormat;


class Level implements ChunkManager, Metadatable{

	private static $levelIdCounter = 1;
	public static $COMPRESSION_LEVEL = 7;


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

	/** @var Tile[] */
	protected $tiles = [];

	/** @var Player[] */
	protected $players = [];

	/** @var Entity[] */
	protected $entities = [];

	/** @var Server */
	protected $server;

	/** @var int */
	protected $levelId;

	/** @var LevelProvider */
	protected $provider;

	/** @var Player[][] */
	protected $usedChunks = [];

	/** @var Chunk[] */
	protected $unloadQueue;

	protected $nextSave;

	protected $time;
	public $stopTime;

	private $folderName;

	/** @var Chunk[] */
	private $chunks = [];

	/** @var Block[][] */
	protected $changedBlocks = [];
	protected $changedCount = [];

	/** @var ReversePriorityQueue */
	private $updateQueue;

	/** @var Player[][] */
	private $chunkSendQueue = [];
	private $chunkSendTasks = [];

	private $chunkGenerationQueue = [];

	private $autoSave = true;

	/** @var BlockMetadataStore */
	private $blockMetadata;

	private $useSections;
	private $blockOrder;

	protected $chunkTickRadius;
	protected $chunkTickList = [];
	protected $chunksPerTick;
	protected $clearChunksOnTick;
	protected $randomTickBlocks = [
		Block::GRASS => Grass::class,
		Block::SAPLING => Sapling::class,
		Block::LEAVES => Leaves::class,
		Block::WHEAT_BLOCK => Wheat::class,
		Block::FARMLAND => Farmland::class,
		Block::SNOW_LAYER => SnowLayer::class,
		Block::ICE => Ice::class,
		Block::CACTUS => Cactus::class,
		Block::SUGARCANE_BLOCK => Sugarcane::class,
		Block::RED_MUSHROOM => RedMushroom::class,
		Block::BROWN_MUSHROOM => BrownMushroom::class,
		Block::PUMPKIN_STEM => PumpkinStem::class,
		Block::MELON_STEM => MelonStem::class,
		//Block::VINE => true,
		Block::MYCELIUM => Mycelium::class,
		//Block::COCOA_BLOCK => true,
		Block::CARROT_BLOCK => Carrot::class,
		Block::POTATO_BLOCK => Potato::class,
		Block::LEAVES2 => Leaves2::class,

		Block::BEETROOT_BLOCK => Beetroot::class,
	];

	/** @var LevelTimings */
	public $timings;

	/**
	 * Returns the chunk unique hash/key
	 *
	 * @param int $x
	 * @param int $z
	 *
	 * @return string
	 */
	public static function chunkHash($x, $z){
		return $x . ":" . $z;
	}

	public static function getXZ($hash, &$x, &$z){
		list($x, $z) = explode(":", $hash);
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
	public function __construct(Server $server, $name, $path, $provider){
		$this->levelId = static::$levelIdCounter++;
		$this->blockMetadata = new BlockMetadataStore($this);
		$this->server = $server;

		/** @var LevelProvider $provider */

		if(is_subclass_of($provider, "pocketmine\\level\\format\\LevelProvider", true)){
			$this->provider = new $provider($this, $path);
		}else{
			throw new \Exception("Provider is not a subclass of LevelProvider");
		}
		$this->server->getLogger()->info("Preparing level \"" . $this->provider->getName() . "\"");
		$generator = Generator::getGenerator($this->provider->getGenerator());
		$this->server->getGenerationManager()->openLevel($this, $generator, $this->provider->getGeneratorOptions());

		$this->blockOrder = $provider::getProviderOrder();
		$this->useSections = $provider::usesChunkSection();

		$this->folderName = $name;
		$this->updateQueue = new ReversePriorityQueue();
		$this->updateQueue->setExtractFlags(\SplPriorityQueue::EXTR_BOTH);
		$this->time = (int) $this->provider->getTime();
		$this->nextSave = microtime(true) + 90;

		$this->chunkTickRadius = min($this->server->getViewDistance(), max(1, (int) $this->server->getProperty("chunk-ticking.tick-radius", 3)));
		$this->chunksPerTick = (int) $this->server->getProperty("chunk-ticking.per-tick", 128);
		$this->chunkTickList = [];
		$this->clearChunksOnTick = (bool) $this->server->getProperty("chunk-ticking.clear-tick-list", false);
		$this->timings = new LevelTimings($this);

	}

	/**
	 * @return BlockMetadataStore
	 */
	public function getBlockMetadata(){
		return $this->blockMetadata;
	}

	/**
	 * @return Server
	 */
	public function getServer(){
		return $this->server;
	}

	/**
	 * @return LevelProvider
	 */
	final public function getProvider(){
		return $this->provider;
	}

	/**
	 * Returns the unique level identifier
	 *
	 * @return int
	 */
	final public function getID(){
		return $this->levelId;
	}

	public function close(){
		if($this->getAutoSave()){
			$this->provider->saveChunks();
		}
		$this->provider->close();
	}

	/**
	 * @return bool
	 */
	public function getAutoSave(){
		return $this->autoSave === true;
	}

	/**
	 * @param bool $value
	 */
	public function setAutoSave($value){
		$this->autoSave = $value;
	}

	/**
	 * Unloads the current level from memory safely
	 *
	 * @param bool $force default false, force unload of default level
	 *
	 * @return bool
	 */
	public function unload($force = false){

		$ev = new LevelUnloadEvent($this);

		if($this === $this->server->getDefaultLevel() and $force !== true){
			$ev->setCancelled(true);
		}

		$this->server->getPluginManager()->callEvent($ev);

		if($ev->isCancelled()){
			return false;
		}

		$this->server->getLogger()->info("Unloading level \"" . $this->getName() . "\"");
		$this->nextSave = PHP_INT_MAX;
		$defaultLevel = $this->server->getDefaultLevel();
		foreach($this->getPlayers() as $player){
			if($this === $defaultLevel or $defaultLevel === null){
				$player->close(TextFormat::YELLOW . $player->getName() . " has left the game", "Forced default level unload");
			}elseif($defaultLevel instanceof Level){
				$player->teleport($this->server->getDefaultLevel()->getSafeSpawn());
			}
		}
		$this->close();
		if($this === $defaultLevel){
			$this->server->setDefaultLevel(null);
		}

		return true;
	}

	/**
	 * Gets the chunks being used by players
	 *
	 * @param int $X
	 * @param int $Z
	 *
	 * @return Player[][]
	 */
	public function getUsingChunk($X, $Z){
		$index = Level::chunkHash($X, $Z);

		return isset($this->usedChunks[$index]) ? $this->usedChunks[$index] : [];
	}

	/**
	 * WARNING: Do not use this, it's only for internal use.
	 * Changes to this function won't be recorded on the version.
	 *
	 * @param int    $X
	 * @param int    $Z
	 * @param Player $player
	 */
	public function useChunk($X, $Z, Player $player){
		$index = Level::chunkHash($X, $Z);
		$this->loadChunk($X, $Z);
		$this->usedChunks[$index][$player->getID()] = $player;
	}

	/**
	 * WARNING: Do not use this, it's only for internal use.
	 * Changes to this function won't be recorded on the version.
	 *
	 * @param Player $player
	 */
	public function freeAllChunks(Player $player){
		foreach($this->usedChunks as $i => $c){
			unset($this->usedChunks[$i][$player->getID()]);
		}
	}

	/**
	 * WARNING: Do not use this, it's only for internal use.
	 * Changes to this function won't be recorded on the version.
	 *
	 * @param int    $X
	 * @param int    $Z
	 * @param Player $player
	 */
	public function freeChunk($X, $Z, Player $player){
		unset($this->usedChunks[$index = Level::chunkHash($X, $Z)][$player->getID()]);
		$this->unloadChunkRequest($X, $Z, true);
	}

	/**
	 * WARNING: Do not use this, it's only for internal use.
	 * Changes to this function won't be recorded on the version.
	 */
	public function checkTime(){
		if($this->stopTime == true){
			return;
		}else{
			$this->time += 2.5;
		}
	}

	/**
	 * WARNING: Do not use this, it's only for internal use.
	 * Changes to this function won't be recorded on the version.
	 */
	public function sendTime(){
		$pk = new SetTimePacket;
		$pk->time = (int) $this->time;
		$pk->started = $this->stopTime == false;
		foreach($this->players as $player){
			$player->directDataPacket($pk);
		}
	}

	/**
	 * WARNING: Do not use this, it's only for internal use.
	 * Changes to this function won't be recorded on the version.
	 *
	 * @param int $currentTick
	 *
	 * @return bool
	 */
	public function doTick($currentTick){

		$this->timings->doTick->startTiming();

		$this->checkTime();

		if(($currentTick % 200) === 0){
			$this->sendTime();
		}

		if(count($this->changedCount) > 0){
			if(count($this->players) > 0){
				foreach($this->changedCount as $index => $mini){
					for($Y = 0; $Y < 8; ++$Y){
						if(($mini & (1 << $Y)) === 0){
							continue;
						}
						if(count($this->changedBlocks[$index][$Y]) < 582){ //Optimal value, calculated using the relation between minichunks and single packets
							continue;
						}else{
							$X = null;
							$Z = null;
							Level::getXZ($index, $X, $Z);
							foreach($this->getUsingChunk($X, $Z) as $p){
								$p->unloadChunk($X, $Z);
							}
							unset($this->changedBlocks[$index][$Y]);
						}
					}
				}
				$this->changedCount = [];
				if(count($this->changedBlocks) > 0){
					foreach($this->changedBlocks as $index => $mini){
						foreach($mini as $blocks){
							/** @var Block $b */
							foreach($blocks as $b){
								$pk = new UpdateBlockPacket();
								$pk->x = $b->x;
								$pk->y = $b->y;
								$pk->z = $b->z;
								$pk->block = $b->getID();
								$pk->meta = $b->getDamage();
								foreach($this->getUsingChunk($b->x >> 4, $b->z >> 4) as $player){
									$player->dataPacket($pk);
								}
							}
						}
					}
					$this->changedBlocks = [];
				}
			}else{
				$this->changedCount = [];
				$this->changedBlocks = [];
			}

		}

		$X = null;
		$Z = null;

		//Do block updates
		$this->timings->doTickPending->startTiming();
		while($this->updateQueue->count() > 0 and $this->updateQueue->current()["priority"] <= $currentTick){
			$block = $this->getBlock($this->updateQueue->extract()["data"]);
			$block->onUpdate(self::BLOCK_UPDATE_SCHEDULED);
		}
		$this->timings->doTickPending->stopTiming();

		$this->timings->doTickTiles->startTiming();
		$this->tickChunks();
		$this->timings->doTickTiles->stopTiming();

		$this->processChunkRequest();

		$this->timings->doTick->stopTiming();
	}

	private function tickChunks(){
		if($this->chunksPerTick <= 0 or count($this->players) === 0){
			return;
		}

		$chunksPerPlayer = min(200, max(1, (int) ((($this->chunksPerTick - count($this->players)) / count($this->players)) + 0.5)));
		$randRange = 3 + $chunksPerPlayer / 30;
		$randRange = $randRange > $this->chunkTickRadius ? $this->chunkTickRadius : $randRange;

		foreach($this->players as $player){
			$x = $player->x >> 4;
			$z = $player->x >> 4;

			$index = "$x:$z";
			$existingPlayers = max(0, isset($this->chunkTickList[$index]) ? $this->chunkTickList[$index] : 0);
			$this->chunkTickList[$index] = $existingPlayers + 1;
			for($chunk = 0; $chunk < $chunksPerPlayer; ++$chunk){
				$dx = mt_rand(-$randRange, $randRange);
				$dz = mt_rand(-$randRange, $randRange);
				$hash = ($dx + $x) .":". ($dz + $z);
				if(!isset($this->chunkTickList[$hash]) and $this->isChunkLoaded($dx + $x, $dz + $z)){
					$this->chunkTickList[$hash] = -1;
				}
			}
		}

		$chunkX = $chunkZ = null;

		foreach($this->chunkTickList as $index => $players){
			Level::getXZ($index, $chunkX, $chunkZ);

			if(!$this->isChunkLoaded($chunkX, $chunkZ) or isset($this->unloadQueue[$index]) and $players > 0){
				unset($this->chunkTickList[$index]);
				continue;
			}
			$chunk = $this->getChunkAt($chunkX, $chunkZ, true);


			if($this->useSections){
				foreach($chunk->getSections() as $section){
					if(!($section instanceof EmptyChunkSection)){
						$Y = $section->getY();
						$k = mt_rand(0, PHP_INT_MAX);
						for($i = 0; $i < 3; ++$i){
							$j = $k >> 2;
							$x = $j & 0x0f;
							$y = ($j >> 8) & 0x0f;
							$z = ($j >> 16) & 0x0f;
							$k %= 1073741827;
							$blockId = $section->getBlockId($x, $y, $z);
							if(isset($this->randomTickBlocks[$blockId])){
								$class = $this->randomTickBlocks[$blockId];
								/** @var Block $block */
								$block = new $class($section->getBlockData($x, $y, $z));
								$block->x = $chunkX * 16 + $x;
								$block->y = ($Y << 4) + $y;
								$block->z = $chunkZ * 16 + $z;
								$block->level = $this;
								$block->onUpdate(self::BLOCK_UPDATE_RANDOM);
							}
						}
					}
				}
			}else{
				for($Y = 0; $Y < 8; ++$Y){
					$k = mt_rand(0, PHP_INT_MAX);
					for($i = 0; $i < 3; ++$i){
						$j = $k >> 2;
						$x = $j & 0x0f;
						$y = ($j >> 8) & 0x0f;
						$z = ($j >> 16) & 0x0f;
						$k %= 1073741827;
						$blockId = $chunk->getBlockId($x, $y + ($Y << 4), $z);
						if(isset($this->randomTickBlocks[$blockId])){
							$class = $this->randomTickBlocks[$blockId];
							/** @var Block $block */
							$block = new $class($chunk->getBlockData($x, $y + ($Y << 4), $z));
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

	/**
	 * @param bool $force
	 *
	 * @return bool
	 */
	public function save($force = false){

		if($this->getAutoSave() === false and $force === false){
			return false;
		}

		$this->server->getPluginManager()->callEvent(new LevelSaveEvent($this));

		$this->provider->setTime((int) $this->time);
		$this->provider->saveChunks(); //TODO: only save changed chunks
		if($this->provider instanceof BaseLevelProvider){
			$this->provider->saveLevelData();
		}
		$this->nextSave = microtime(true) + 45;

		return true;
	}

	/**
	 * @param Vector3 $pos
	 * @param int     $type
	 */
	public function updateAround(Vector3 $pos, $type = self::BLOCK_UPDATE_NORMAL){
		$block = $this->getBlock($pos);
		$block->getSide(0)->onUpdate($type);
		$block->getSide(1)->onUpdate($type);
		$block->getSide(2)->onUpdate($type);
		$block->getSide(3)->onUpdate($type);
		$block->getSide(4)->onUpdate($type);
		$block->getSide(5)->onUpdate($type);
	}

	/**
	 * @param Vector3 $pos
	 * @param int     $delay
	 */
	public function scheduleUpdate(Vector3 $pos, $delay){
		$this->updateQueue->insert($pos, (int) $delay);
	}

	/**
	 * @param AxisAlignedBB $bb
	 *
	 * @return Block[]
	 */
	public function getCollisionBlocks(AxisAlignedBB $bb){
		$minX = floor($bb->minX);
		$minY = floor($bb->minY);
		$minZ = floor($bb->minZ);
		$maxX = floor($bb->maxX + 1);
		$maxY = floor($bb->maxY + 1);
		$maxZ = floor($bb->maxZ + 1);

		$collides = [];

		for($z = $minZ; $z < $maxZ; ++$z){
			for($x = $minX; $x < $maxX; ++$x){
				if($this->isChunkLoaded($x >> 4, $z >> 4)){
					for($y = $minY - 1; $y < $maxY; ++$y){
						$this->getBlock(new Vector3($x, $y, $z))->collidesWithBB($bb, $collides);
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
	public function isFullBlock(Vector3 $pos){
		$bb = $this->getBlock($pos)->getBoundingBox();

		return $bb instanceof AxisAlignedBB and $bb->getAverageEdgeLength() >= 1;
	}

	/**
	 * @param Entity        $entity
	 * @param AxisAlignedBB $bb
	 *
	 * @return AxisAlignedBB[]
	 */
	public function getCollisionCubes(Entity $entity, AxisAlignedBB $bb){
		$minX = floor($bb->minX);
		$minY = floor($bb->minY);
		$minZ = floor($bb->minZ);
		$maxX = floor($bb->maxX + 1);
		$maxY = floor($bb->maxY + 1);
		$maxZ = floor($bb->maxZ + 1);

		$collides = [];

		//TODO: optimize this loop, check collision cube boundaries
		for($z = $minZ; $z < $maxZ; ++$z){
			for($x = $minX; $x < $maxX; ++$x){
				if($this->isChunkLoaded($x >> 4, $z >> 4)){
					for($y = $minY - 1; $y < $maxY; ++$y){
						$this->getBlock(new Vector3($x, $y, $z))->collidesWithBB($bb, $collides);
					}
				}
			}
		}

		foreach($this->getCollidingEntities($bb->expand(0.25, 0.25, 0.25), $entity) as $ent){
			$collides[] = clone $ent->boundingBox;
		}

		return $collides;
	}


	/**
	 * Gets the Block object on the Vector3 location
	 *
	 * @param Vector3 $pos
	 *
	 * @return Block
	 */
	public function getBlock(Vector3 $pos){
		$blockId = 0;
		$meta = 0;
		if($pos->y >= 0 and $pos->y < 128){
			$this->getChunkAt($pos->x >> 4, $pos->z >> 4, true)->getBlock($pos->x & 0x0f, $pos->y & 0x7f, $pos->z & 0x0f, $blockId, $meta);
		}

		if($blockId === 0){
			$air = new Air();
			$air->x = $pos->x;
			$air->y = $pos->y;
			$air->z = $pos->z;
			$air->level = $this;
			return $air;
		}

		return Block::get($blockId, $meta, Position::fromObject($pos, $this));
	}

	/**
	 * Sets on Vector3 the data from a Block object,
	 * does block updates and puts the changes to the send queue.
	 *
	 * @param Vector3 $pos
	 * @param Block   $block
	 * @param bool    $direct
	 * @param bool    $update
	 *
	 * @return bool
	 */
	public function setBlock(Vector3 $pos, Block $block, $direct = false, $update = true){
		if($pos->y < 0 or $pos->y >= 128 or !($block instanceof Block)){
			return false;
		}

		if($this->getChunkAt($pos->x >> 4, $pos->z >> 4, true)->setBlock($pos->x & 0x0f, $pos->y & 0x7f, $pos->z & 0x0f, $block->getID(), $block->getDamage())){
			if(!($pos instanceof Position)){
				$pos = new Position($pos->x, $pos->y, $pos->z, $this);
			}
			$block->position($pos);

			if($direct === true){
				$pk = new UpdateBlockPacket;
				$pk->x = $pos->x;
				$pk->y = $pos->y;
				$pk->z = $pos->z;
				$pk->block = $block->getID();
				$pk->meta = $block->getDamage();

				foreach($this->getUsingChunk($pos->x >> 4, $pos->z >> 4) as $player){
					$player->dataPacket($pk);
				}
			}else{
				if(!($pos instanceof Position)){
					$pos = new Position($pos->x, $pos->y, $pos->z, $this);
				}
				$block->position($pos);
				$index = Level::chunkHash($pos->x >> 4, $pos->z >> 4);
				if(ADVANCED_CACHE == true){
					Cache::remove("world:{$this->getID()}:{$index}");
				}
				if(!isset($this->changedBlocks[$index])){
					$this->changedBlocks[$index] = [];
					$this->changedCount[$index] = 0;
				}
				$Y = $pos->y >> 4;
				if(!isset($this->changedBlocks[$index][$Y])){
					$this->changedBlocks[$index][$Y] = [];
					$this->changedCount[$index] |= 1 << $Y;
				}
				$this->changedBlocks[$index][$Y][] = clone $block;
			}
			if($update === true){
				$this->updateAround($pos, self::BLOCK_UPDATE_NORMAL);
				$block->onUpdate(self::BLOCK_UPDATE_NORMAL);
			}
		}
	}

	/**
	 * @param Vector3 $source
	 * @param Item    $item
	 * @param Vector3 $motion
	 */
	public function dropItem(Vector3 $source, Item $item, Vector3 $motion = null){
		$motion = $motion === null ? new Vector3(0, 0, 0) : $motion;
		if($item->getID() !== Item::AIR and $item->getCount() > 0){
			$itemEntity = new DroppedItem($this->getChunkAt($source->getX() >> 4, $source->getZ() >> 4), new Compound("", [
				"Pos" => new Enum("Pos", [
						new Double("", $source->getX()),
						new Double("", $source->getY()),
						new Double("", $source->getZ())
					]),
				//TODO: add random motion with physics
				"Motion" => new Enum("Motion", [
						new Double("", $motion->x + (lcg_value() * 0.2 - 0.1)),
						new Double("", $motion->y + 0.2),
						new Double("", $motion->z + (lcg_value() * 0.2 - 0.1))
					]),
				"Rotation" => new Enum("Rotation", [
						new Float("", lcg_value() * 360),
						new Float("", 0)
					]),
				"Health" => new Short("Health", 5),
				"Item" => new Compound("Item", [
						"id" => new Short("id", $item->getID()),
						"Damage" => new Short("Damage", $item->getDamage()),
						"Count" => new Byte("Count", $item->getCount())
					]),
				"PickupDelay" => new Short("PickupDelay", 50)
			]));

			$itemEntity->spawnToAll();
		}
	}

	/**
	 * Tries to break a block using a item, including Player time checks if available
	 *
	 * @param Vector3 $vector
	 * @param Item    &$item (if null, can break anything)
	 * @param Player  $player
	 *
	 * @return boolean
	 */
	public function useBreakOn(Vector3 $vector, Item &$item = null, Player $player = null){
		$target = $this->getBlock($vector);
		//TODO: Adventure mode checks

		if($item === null){
			$item = Item::get(Item::AIR, 0, 0);
		}

		if($player instanceof Player){
			$ev = new BlockBreakEvent($player, $target, $item, ($player->getGamemode() & 0x01) === 1 ? true : false);

			$lastTime = $player->lastBreak - 0.1; //TODO: replace with true lag
			if(($player->getGamemode() & 0x01) > 0){
				$ev->setInstaBreak(true);
			}elseif(($lastTime + $target->getBreakTime($item)) >= microtime(true)){
				$ev->setCancelled();
			}

			if($item instanceof Item and !$target->isBreakable($item) and $ev->getInstaBreak() === false){
				$ev->setCancelled();
			}
			if(!$player->isOp() and ($distance = $this->server->getConfigInt("spawn-protection", 16)) > -1){
				$t = new Vector2($target->x, $target->z);
				$s = new Vector2($this->getSpawn()->x, $this->getSpawn()->z);
				if($t->distance($s) <= $distance){ //set it to cancelled so plugins can bypass this
					$ev->setCancelled();
				}
			}
			$this->server->getPluginManager()->callEvent($ev);
			if($ev->isCancelled()){
				return false;
			}

			$player->lastBreak = microtime(true);

		}elseif($item instanceof Item and !$target->isBreakable($item)){
			return false;
		}

		$level = $target->getLevel();

		if($level instanceof Level){
			$above = $level->getBlock(new Vector3($target->x, $target->y + 1, $target->z));
			if($above instanceof Block){
				if($above->getID() === Item::FIRE){
					$level->setBlock($above, new Air(), true, false, true);
				}
			}
		}
		$drops = $target->getDrops($item); //Fixes tile entities being deleted before getting drops
		$target->onBreak($item);
		$tile = $this->getTile($target);
		if($tile instanceof Tile){
			if($tile instanceof InventoryHolder){
				if($tile instanceof Chest){
					$tile->unpair();
				}

				foreach($tile->getInventory()->getContents() as $item){
					$this->dropItem($target, $item);
				}
			}

			$tile->close();
		}

		if($item instanceof Item){
			$item->useOn($target);
			if($item->isTool() and $item->getDamage() >= $item->getMaxDurability()){
				$item = Item::get(Item::AIR, 0, 0);
			}
		}

		if(!($player instanceof Player) or ($player->getGamemode() & 0x01) === 0){
			foreach($drops as $drop){
				if($drop[2] > 0){
					$this->dropItem($vector->add(0.5, 0.5, 0.5), Item::get($drop[0], $drop[1], $drop[2]));
				}
			}
		}

		return true;
	}

	/**
	 * Uses a item on a position and face, placing it or activating the block
	 *
	 * @param Vector3 $vector
	 * @param Item    $item
	 * @param int     $face
	 * @param float   $fx     default 0.0
	 * @param float   $fy     default 0.0
	 * @param float   $fz     default 0.0
	 * @param Player  $player default null
	 *
	 * @return boolean
	 */
	public function useItemOn(Vector3 $vector, Item &$item, $face, $fx = 0.0, $fy = 0.0, $fz = 0.0, Player $player = null){
		$target = $this->getBlock($vector);
		$block = $target->getSide($face);

		if($block->y > 127 or $block->y < 0){
			return false;
		}

		if($target->getID() === Item::AIR){
			return false;
		}

		if($player instanceof Player){
			$ev = new PlayerInteractEvent($player, $item, $target, $face);
			if(!$player->isOp() and ($distance = $this->server->getConfigInt("spawn-protection", 16)) > -1){
				$t = new Vector2($target->x, $target->z);
				$s = new Vector2($this->getSpawn()->x, $this->getSpawn()->z);
				if($t->distance($s) <= $distance){ //set it to cancelled so plugins can bypass this
					$ev->setCancelled();
				}
			}
			$this->server->getPluginManager()->callEvent($ev);
			if(!$ev->isCancelled()){
				$target->onUpdate(self::BLOCK_UPDATE_TOUCH);
				if($target->isActivable === true and $target->onActivate($item, $player) === true){
					return true;
				}
			}

			if($item->isActivable and $item->onActivate($this, $player, $block, $target, $face, $fx, $fy, $fz)){
				if($item->getCount() <= 0){
					$item = Item::get(Item::AIR, 0, 0);

					return true;
				}
			}
		}elseif($target->isActivable === true and $target->onActivate($item, $player) === true){
			return true;
		}

		if($item->isPlaceable()){
			$hand = $item->getBlock();
			$hand->position($block);
		}elseif($block->getID() === Item::FIRE){
			$this->setBlock($block, new Air(), true, false, true);

			return false;
		}else{
			return false;
		}

		if(!($block->isReplaceable === true or ($hand->getID() === Item::SLAB and $block->getID() === Item::SLAB))){
			return false;
		}

		if($target->isReplaceable === true){
			$block = $target;
			$hand->position($block);
			//$face = -1;
		}

		if($hand->isSolid === true and $hand->getBoundingBox() !== null and count($this->getCollidingEntities($hand->getBoundingBox())) > 0){
			return false; //Entity in block
		}


		if($player instanceof Player){
			$ev = new BlockPlaceEvent($player, $hand, $block, $target, $item);
			if(!$player->isOp() and ($distance = $this->server->getConfigInt("spawn-protection", 16)) > -1){
				$t = new Vector2($target->x, $target->z);
				$s = new Vector2($this->getSpawn()->x, $this->getSpawn()->z);
				if($t->distance($s) <= $distance){ //set it to cancelled so plugins can bypass this
					$ev->setCancelled();
				}
			}
			$this->server->getPluginManager()->callEvent($ev);
			if($ev->isCancelled()){
				return false;
			}
		}

		if($hand->place($item, $block, $target, $face, $fx, $fy, $fz, $player) === false){
			return false;
		}

		if($hand->getID() === Item::SIGN_POST or $hand->getID() === Item::WALL_SIGN){
			$tile = new Sign($this->getChunkAt($block->x >> 4, $block->z >> 4), new Compound(false, array(
				new String("id", Tile::SIGN),
				new Int("x", $block->x),
				new Int("y", $block->y),
				new Int("z", $block->z),
				new String("Text1", ""),
				new String("Text2", ""),
				new String("Text3", ""),
				new String("Text4", "")
			)));
			if($player instanceof Player){
				$tile->namedtag->Creator = new String("Creator", $player->getName());
			}
		}
		$item->setCount($item->getCount() - 1);
		if($item->getCount() <= 0){
			$item = Item::get(Item::AIR, 0, 0);
		}

		return true;
	}

	/**
	 * @param int $entityId
	 *
	 * @return Entity
	 */
	public function getEntity($entityId){
		return isset($this->entities[$entityId]) ? $this->entities[$entityId] : null;
	}

	/**
	 * Gets the list of all the entitites in this level
	 *
	 * @return Entity[]
	 */
	public function getEntities(){
		return $this->entities;
	}

	/**
	 * Returns the entities near the current one inside the AxisAlignedBB
	 *
	 * @param AxisAlignedBB $bb
	 * @param Entity        $entity
	 *
	 * @return Entity[]
	 */
	public function getCollidingEntities(AxisAlignedBB $bb, Entity $entity = null){
		$nearby = [];

		$minX = ($bb->minX - 2) >> 4;
		$maxX = ($bb->maxX + 2) >> 4;
		$minZ = ($bb->minZ - 2) >> 4;
		$maxZ = ($bb->maxZ + 2) >> 4;

		for($x = $minX; $x <= $maxX; ++$x){
			for($z = $minZ; $z <= $maxZ; ++$z){
				if($this->isChunkLoaded($x, $z)){
					foreach($this->getChunkEntities($x, $z) as $ent){
						if($ent !== $entity and ($entity === null or ($ent->canCollideWith($entity) and $entity->canCollideWith($ent))) and $ent->boundingBox->intersectsWith($bb)){
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
	public function getNearbyEntities(AxisAlignedBB $bb, Entity $entity = null){
		$nearby = [];

		$minX = ($bb->minX - 2) >> 4;
		$maxX = ($bb->maxX + 2) >> 4;
		$minZ = ($bb->minZ - 2) >> 4;
		$maxZ = ($bb->maxZ + 2) >> 4;

		for($x = $minX; $x <= $maxX; ++$x){
			for($z = $minZ; $z <= $maxZ; ++$z){
				if($this->isChunkLoaded($x, $z)){
					foreach($this->getChunkEntities($x, $z) as $ent){
						if($ent !== $entity and $ent->boundingBox->intersectsWith($bb)){
							$nearby[] = $ent;
						}
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
	public function getTiles(){
		return $this->tiles;
	}

	/**
	 * @param $tileId
	 *
	 * @return Tile
	 */
	public function getTileById($tileId){
		return isset($this->tiles[$tileId]) ? $this->tiles[$tileId] : null;
	}

	/**
	 * Returns a list of the players in this level
	 *
	 * @return Player[]
	 */
	public function getPlayers(){
		return $this->players;
	}

	/**
	 * Returns the Tile in a position, or false if not found
	 *
	 * @param Vector3 $pos
	 *
	 * @return bool|Tile
	 */
	public function getTile(Vector3 $pos){
		if($pos instanceof Position and $pos->getLevel() !== $this){
			return false;
		}
		$tiles = $this->getChunkTiles($pos->x >> 4, $pos->z >> 4);
		if(count($tiles) > 0){
			foreach($tiles as $tile){
				if($tile->x === (int) $pos->x and $tile->y === (int) $pos->y and $tile->z === (int) $pos->z){
					return $tile;
				}
			}
		}

		return false;
	}

	/**
	 * Returns a list of the entities on a given chunk
	 *
	 * @param int $X
	 * @param int $Z
	 *
	 * @return Entity[]
	 */
	public function getChunkEntities($X, $Z){
		return $this->getChunkAt($X, $Z, true)->getEntities();
	}

	/**
	 * Gives a list of the Tile entities on a given chunk
	 *
	 * @param int $X
	 * @param int $Z
	 *
	 * @return Tile[]
	 */
	public function getChunkTiles($X, $Z){
		return $this->getChunkAt($X, $Z, true)->getTiles();
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
	public function getBlockIdAt($x, $y, $z){
		return $this->getChunkAt($x >> 4, $z >> 4, true)->getBlockId($x & 0x0f, $y & 0x7f, $z & 0x0f);
	}

	/**
	 * Sets the raw block id.
	 *
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 * @param int $id 0-255
	 */
	public function setBlockIdAt($x, $y, $z, $id){
		$this->getChunkAt($x >> 4, $z >> 4, true)->setBlockId($x & 0x0f, $y & 0x7f, $z & 0x0f, $id & 0xff);
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
	public function getBlockDataAt($x, $y, $z){
		return $this->getChunkAt($x >> 4, $z >> 4, true)->getBlockData($x & 0x0f, $y & 0x7f, $z & 0x0f);
	}

	/**
	 * Sets the raw block metadata.
	 *
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 * @param int $data 0-15
	 */
	public function setBlockDataAt($x, $y, $z, $data){
		$this->getChunkAt($x >> 4, $z >> 4, true)->setBlockData($x & 0x0f, $y & 0x7f, $z & 0x0f, $data & 0x0f);
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
	public function getBlockSkyLightAt($x, $y, $z){
		return $this->getChunkAt($x >> 4, $z >> 4, true)->getBlockSkyLight($x & 0x0f, $y & 0x7f, $z & 0x0f);
	}

	/**
	 * Sets the raw block skylight level.
	 *
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 * @param int $level 0-15
	 */
	public function setBlockSkyLightAt($x, $y, $z, $level){
		$this->getChunkAt($x >> 4, $z >> 4, true)->setBlockSkyLight($x & 0x0f, $y & 0x7f, $z & 0x0f, $level & 0x0f);
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
	public function getBlockLightAt($x, $y, $z){
		return $this->getChunkAt($x >> 4, $z >> 4, true)->getBlockLight($x & 0x0f, $y & 0x7f, $z & 0x0f);
	}

	/**
	 * Sets the raw block light level.
	 *
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 * @param int $level 0-15
	 */
	public function setBlockLightAt($x, $y, $z, $level){
		$this->getChunkAt($x >> 4, $z >> 4, true)->setBlockLight($x & 0x0f, $y & 0x7f, $z & 0x0f, $level & 0x0f);
	}

	/**
	 * @param int $x
	 * @param int $z
	 *
	 * @return int
	 */
	public function getBiomeId($x, $z){
		return $this->getChunkAt($x >> 4, $z >> 4, true)->getBiomeId($x & 0x0f, $z & 0x0f);
	}

	/**
	 * @param int $x
	 * @param int $z
	 *
	 * @return int[]
	 */
	public function getBiomeColor($x, $z){
		return $this->getChunkAt($x >> 4, $z >> 4, true)->getBiomeColor($x & 0x0f, $z & 0x0f);
	}

	/**
	 * @param int $x
	 * @param int $z
	 * @param int $biomeId
	 */
	public function setBiomeId($x, $z, $biomeId){
		$this->getChunkAt($x >> 4, $z >> 4, true)->setBiomeId($x & 0x0f, $z & 0x0f, $biomeId);
	}

	/**
	 * @param int $x
	 * @param int $z
	 * @param int $R
	 * @param int $G
	 * @param int $B
	 */
	public function setBiomeColor($x, $z, $R, $G, $B){
		$this->getChunkAt($x >> 4, $z >> 4, true)->setBiomeColor($x & 0x0f, $z & 0x0f, $R, $G, $B);
	}

	/**
	 * Gets the Chunk object
	 *
	 * @param int  $x
	 * @param int  $z
	 * @param bool $create Whether to generate the chunk if it does not exist
	 *
	 * @return Chunk
	 */
	public function getChunkAt($x, $z, $create = false){
		if(isset($this->chunks[$index = "$x:$z"])){
			return $this->chunks[$index];
		}elseif(($chunk = $this->provider->getChunk($x, $z, $create)) instanceof FullChunk){
			$this->chunks[$index] = $chunk;

			return $chunk;
		}

		return null;
	}

	public function generateChunkCallback($x, $z, FullChunk $chunk){
		$oldChunk = $this->getChunkAt($x, $z);
		unset($this->chunkGenerationQueue["$x:$z"]);
		$this->setChunk($x, $z, $chunk);
		$chunk = $this->getChunkAt($x, $z);
		if($chunk instanceof FullChunk){
			if(!($oldChunk instanceof FullChunk) or ($oldChunk->isPopulated() === false and $chunk->isPopulated())){
				$this->server->getPluginManager()->callEvent(new ChunkPopulateEvent($chunk));
			}
		}
	}

	public function setChunk($x, $z, FullChunk $chunk){
		$index = Level::chunkHash($x, $z);
		foreach($this->getUsingChunk($x, $z) as $player){
			$player->unloadChunk($x, $z);
		}
		unset($this->chunks[$index]);
		$this->provider->setChunk($x, $z, $chunk);
		$this->loadChunk($x, $z);
	}

	/**
	 * Gets the highest block Y value at a specific $x and $z
	 *
	 * @param int $x
	 * @param int $z
	 *
	 * @return int 0-127
	 */
	public function getHighestBlockAt($x, $z){
		if(!$this->isChunkLoaded($x >> 4, $z >> 4)){
			$this->loadChunk($x >> 4, $z >> 4);
		}

		return $this->getChunkAt($x >> 4, $z >> 4, true)->getHighestBlockAt($x & 0x0f, $z & 0x0f);
	}

	/**
	 * @param int $x
	 * @param int $z
	 *
	 * @return bool
	 */
	public function isChunkLoaded($x, $z){
		return isset($this->chunks["$x:$z"]) or $this->provider->isChunkLoaded($x, $z);
	}

	/**
	 * @param int $x
	 * @param int $z
	 *
	 * @return bool
	 */
	public function isChunkGenerated($x, $z){
		$chunk = $this->getChunkAt($x, $z);
		return $chunk instanceof FullChunk ? $chunk->isGenerated() : false;
	}

	/**
	 * @param int $x
	 * @param int $z
	 *
	 * @return bool
	 */
	public function isChunkPopulated($x, $z){
		$chunk = $this->getChunkAt($x, $z);
		return $chunk instanceof FullChunk ? $chunk->isPopulated() : false;
	}

	/**
	 * Returns a Position pointing to the spawn
	 *
	 * @return Position
	 */
	public function getSpawnLocation(){
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

	public function requestChunk($x, $z, Player $player, $order = LevelProvider::ORDER_ZXY){
		$index = Level::chunkHash($x, $z);
		if(!isset($this->chunkSendQueue[$index])){
			$this->chunkSendQueue[$index] = [];
		}

		$this->chunkSendQueue[$index][spl_object_hash($player)] = $player;
	}

	protected function processChunkRequest(){
		if(count($this->chunkSendQueue) > 0){
			$x = null;
			$z = null;
			foreach($this->chunkSendQueue as $index => $players){
				if(isset($this->chunkSendTasks[$index])){
					continue;
				}
				Level::getXZ($index, $x, $z);
				if(ADVANCED_CACHE == true and ($cache = Cache::get("world:" . $this->getID() . ":" . $index)) !== false){
					/** @var Player[] $players */
					foreach($players as $player){
						if(isset($player->usedChunks[$index])){
							$player->sendChunk($x, $z, $cache);
						}
					}
					unset($this->chunkSendQueue[$index]);
				}else{
					$this->chunkSendTasks[$index] = true;
					$task = $this->provider->requestChunkTask($x, $z);
					if($task instanceof AsyncTask){
						$this->server->getScheduler()->scheduleAsyncTask($task);
					}
				}
			}
		}
	}

	public function chunkRequestCallback($x, $z, $payload){
		$index = Level::chunkHash($x, $z);
		if(isset($this->chunkSendTasks[$index])){

			if(ADVANCED_CACHE == true){
				Cache::add("world:" . $this->getID() . ":" . $index, $payload, 60);
			}
			foreach($this->chunkSendQueue[$index] as $player){
				/** @var Player $player */
				if(isset($player->usedChunks[$index])){
					$player->sendChunk($x, $z, $payload);
				}
			}
			unset($this->chunkSendQueue[$index]);
			unset($this->chunkSendTasks[$index]);
		}
	}

	/**
	 * Removes the entity from the level index
	 *
	 * @param Entity $entity
	 *
	 * @throws \RuntimeException
	 */
	public function removeEntity(Entity $entity){
		if($entity->getLevel() !== $this){
			throw new \RuntimeException("Invalid Entity level");
		}

		if($entity instanceof Player){
			unset($this->players[$entity->getID()]);
			//$this->everyoneSleeping();
		}else{
			$entity->kill();
		}

		if($this->isChunkLoaded($entity->chunkX, $entity->chunkZ)){
			$this->getChunkAt($entity->chunkX, $entity->chunkZ, true)->removeEntity($entity);
		}

		unset($this->entities[$entity->getID()]);
	}

	/**
	 * @param Entity $entity
	 *
	 * @throws \RuntimeException
	 */
	public function addEntity(Entity $entity){
		if($entity->getLevel() !== $this){
			throw new \RuntimeException("Invalid Entity level");
		}
		if($entity instanceof Player){
			$this->players[$entity->getID()] = $entity;
		}
		$this->entities[$entity->getID()] = $entity;
	}

	/**
	 * @param Tile $tile
	 *
	 * @throws \RuntimeException
	 */
	public function addTile(Tile $tile){
		if($tile->getLevel() !== $this){
			throw new \RuntimeException("Invalid Tile level");
		}
		$this->tiles[$tile->getID()] = $tile;
	}

	/**
	 * @param Tile $tile
	 *
	 * @throws \RuntimeException
	 */
	public function removeTile(Tile $tile){
		if($tile->getLevel() !== $this){
			throw new \RuntimeException("Invalid Tile level");
		}
		if($this->isChunkLoaded($tile->chunk->getX(), $tile->chunk->getZ())){
			$this->getChunkAt($tile->chunk->getX(), $tile->chunk->getZ(), true)->removeTile($tile);
		}
		unset($this->tiles[$tile->getID()]);
	}

	/**
	 * @param int $x
	 * @param int $z
	 *
	 * @return bool
	 */
	public function isChunkInUse($x, $z){
		return isset($this->usedChunks[static::chunkHash($x, $z)]) and count($this->usedChunks[static::chunkHash($x, $z)]) > 0;
	}

	/**
	 * @param int  $x
	 * @param int  $z
	 * @param bool $generate
	 *
	 * @return bool
	 */
	public function loadChunk($x, $z, $generate = true){
		if(isset($this->chunks[$index = Level::chunkHash($x, $z)])){
			return true;
		}

		$this->cancelUnloadChunkRequest($x, $z);

		$chunk = $this->provider->getChunk($x, $z, $generate);
		if($chunk instanceof FullChunk){
			$this->chunks[$index] = $chunk;
		}else{
			$this->timings->syncChunkLoadTimer->startTiming();
			$this->provider->loadChunk($x, $z, $generate);
			$this->timings->syncChunkLoadTimer->stopTiming();

			if(($chunk = $this->provider->getChunk($x, $z)) instanceof FullChunk){
				$this->chunks[$index] = $chunk;
			}else{
				return false;
			}
		}

		$this->server->getPluginManager()->callEvent(new ChunkLoadEvent($chunk, !$chunk->isGenerated()));

		return true;
	}

	protected function queueUnloadChunk($x, $z){
		$this->unloadQueue[Level::chunkHash($x, $z)] = microtime(true);
	}

	public function unloadChunkRequest($x, $z, $safe = true){
		if($safe === true and $this->isChunkInUse($x, $z)){
			return false;
		}

		$this->queueUnloadChunk($x, $z);

		return true;
	}

	public function cancelUnloadChunkRequest($x, $z){
		unset($this->unloadQueue[static::chunkHash($x, $z)]);
	}

	public function unloadChunk($x, $z, $safe = true){
		if($safe === true and $this->isChunkInUse($x, $z)){
			return false;
		}

		$chunk = $this->getChunkAt($x, $z);

		if($chunk instanceof FullChunk){
			$this->server->getPluginManager()->callEvent($ev = new ChunkUnloadEvent($chunk));
			if($ev->isCancelled()){
				return false;
			}
		}

		$this->timings->doChunkUnload->startTiming();

		if($this->getAutoSave()){
			$this->provider->saveChunk($x, $z);
		}

		unset($this->chunks[$index = Level::chunkHash($x, $z)]);
		$this->provider->unloadChunk($x, $z, $safe);
		unset($this->usedChunks[$index]);
		Cache::remove("world:" . $this->getID() . ":$x:$z");

		$this->timings->doChunkUnload->stopTiming();

		return true;
	}

	/**
	 * Returns true if the spawn is part of the spawn
	 *
	 * @param int $X
	 * @param int $Z
	 *
	 * @return bool
	 */
	public function isSpawnChunk($X, $Z){
		$spawnX = $this->provider->getSpawn()->getX() >> 4;
		$spawnZ = $this->provider->getSpawn()->getZ() >> 4;

		return abs($X - $spawnX) <= 1 and abs($Z - $spawnZ) <= 1;
	}

	/**
	 * Returns the raw spawnpoint
	 *
	 * @return Position
	 */
	public function getSpawn(){
		return Position::fromObject($this->provider->getSpawn(), $this);
	}

	/**
	 * @param Vector3 $spawn default null
	 *
	 * @return bool|Position
	 */
	public function getSafeSpawn($spawn = null){
		if(!($spawn instanceof Vector3)){
			$spawn = $this->getSpawn();
		}
		if($spawn instanceof Vector3){
			$x = (int) round($spawn->x);
			$y = (int) round($spawn->y);
			$z = (int) round($spawn->z);
			for(; $y > 0; --$y){
				$v = new Vector3($x, $y, $z);
				$b = $this->getBlock($v->getSide(0));
				if($b === false){
					return $spawn;
				}elseif(!($b instanceof Air)){
					break;
				}
			}
			for(; $y < 128; ++$y){
				$v = new Vector3($x, $y, $z);
				if($this->getBlock($v->getSide(1)) instanceof Air){
					if($this->getBlock($v) instanceof Air){
						return new Position($x, $y, $z, $this);
					}
				}else{
					++$y;
				}
			}

			return new Position($x, $y, $z, $this);
		}

		return false;
	}

	/**
	 * Sets the spawnpoint
	 *
	 * @param Vector3 $pos
	 *
	 * @deprecated
	 */
	public function setSpawn(Vector3 $pos){
		$this->setSpawnLocation($pos);
	}

	/**
	 * Gets the current time
	 *
	 * @return int
	 */
	public function getTime(){
		return (int) $this->time;
	}

	/**
	 * Returns the Level name
	 *
	 * @return string
	 */
	public function getName(){
		return $this->provider->getName();
	}

	/**
	 * Returns the Level folder name
	 *
	 * @return string
	 */
	public function getFolderName(){
		return $this->folderName;
	}

	/**
	 * Sets the current time on the level
	 *
	 * @param int $time
	 */
	public function setTime($time){
		$this->time = (int) $time;
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
	public function getSeed(){
		return $this->provider->getSeed();
	}

	/**
	 * Sets the seed for the level
	 *
	 * @param int $seed
	 */
	public function setSeed($seed){
		$this->provider->setSeed($seed);
	}


	public function generateChunk($x, $z){
		if(!isset($this->chunkGenerationQueue["$x:$z"])){
			$this->chunkGenerationQueue["$x:$z"] = true;
			$this->server->getGenerationManager()->requestChunk($this, $x, $z);
		}
	}

	public function regenerateChunk($x, $z){
		$this->unloadChunk($x, $z, false);

		$this->cancelUnloadChunkRequest($x, $z);

		$this->generateChunk($x, $z);
		//TODO: generate & refresh chunk from the generator object
	}

	public function doChunkGarbageCollection(){
		$this->timings->doChunkGC->startTiming();

		$X = null;
		$Z = null;

		foreach($this->chunks as $index => $chunk){
			if(!isset($this->usedChunks[$index])){
				Level::getXZ($index, $X, $Z);
				$this->unloadChunkRequest($X, $Z, true);
			}
		}

		if(count($this->unloadQueue) > 0){
			foreach($this->unloadQueue as $index => $time){
				Level::getXZ($index, $X, $Z);

				if($this->getAutoSave()){
					$this->provider->saveChunk($X, $Z);
				}
				//If the chunk can't be unloaded, it stays on the queue
				if($this->unloadChunk($X, $Z, true)){
					unset($this->unloadQueue[$index]);
				}
			}
		}

		foreach($this->usedChunks as $i => $c){
			if(count($c) === 0){
				Level::getXZ($i, $X, $Z);
				if(!$this->isSpawnChunk($X, $Z)){
					if($this->getAutoSave()){
						$this->provider->saveChunk($X, $Z);
					}

					$this->unloadChunk($X, $Z, true);
				}
			}
		}


		$this->timings->doChunkGC->stopTiming();
	}


	public function setMetadata($metadataKey, MetadataValue $metadataValue){
		$this->server->getPlayerMetadata()->setMetadata($this, $metadataKey, $metadataValue);
	}

	public function getMetadata($metadataKey){
		return $this->server->getPlayerMetadata()->getMetadata($this, $metadataKey);
	}

	public function hasMetadata($metadataKey){
		return $this->server->getPlayerMetadata()->hasMetadata($this, $metadataKey);
	}

	public function removeMetadata($metadataKey, Plugin $plugin){
		$this->server->getPlayerMetadata()->removeMetadata($this, $metadataKey, $plugin);
	}
}
