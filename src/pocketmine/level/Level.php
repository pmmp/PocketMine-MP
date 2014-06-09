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
use pocketmine\block\Block;
use pocketmine\event\level\SpawnChangeEvent;
use pocketmine\level\format\LevelProvider;
use pocketmine\math\Vector3;
use pocketmine\Server;
use pocketmine\level\generator\Generator;
use pocketmine\level\generator\populator\Populator;
use pocketmine\metadata\Metadatable;
use pocketmine\metadata\MetadataValue;
use pocketmine\plugin\Plugin;
use pocketmine\Player;
use pocketmine\entity\Entity;
use pocketmine\tile\Tile;
use pocketmine\level\format\Chunk;


class Level implements ChunkManager, Metadatable{

	private static $levelIdCounter = 1;

	/** @var Generator */
	private $generator;
	/** @var Populator[] */
	private $populators;


	const BLOCK_UPDATE_NORMAL = 1;
	const BLOCK_UPDATE_RANDOM = 2;
	const BLOCK_UPDATE_SCHEDULED = 3;
	const BLOCK_UPDATE_WEAK = 4;
	const BLOCK_UPDATE_TOUCH = 5;

	/** @var \SplObjectStorage<Tile> */
	protected $tiles;

	/** @var Tile[][] */
	public $chunkTiles = [];

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

	/**
	 * Returns the chunk unique hash/key
	 *
	 * @param int $x
	 * @param int $z
	 *
	 * @return string
	 */
	public static function chunkHash($x, $z){
		return $x .":". $z;
	}

	/**
	 * Init the default level data
	 *
	 * @param Server $server
	 * @param string $path
	 * @param string $provider Class that extends LevelProvider
	 *
	 * @throws \Exception
	 */
	public function __construct(Server $server, $path, $provider){
		$this->levelId = static::$levelIdCounter++;
		$this->server = $server;
		if(is_subclass_of($provider, "pocketmine\\level\\format\\LevelProvider", true)){
			$this->provider = new $provider($this, $path);
		}else{
			throw new \Exception("Provider is not a subclass of LevelProvider");
		}
		$this->players = new \SplObjectStorage();
		$this->entities = new \SplObjectStorage();
		$this->tiles = new \SplObjectStorage();
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

	/**
	 * Gets the Block object on the Vector3 location
	 *
	 * @param Vector3 $pos
	 *
	 * @return Block
	 */
	public function getBlock(Vector3 $pos){
		$blockId = null;
		$meta = null;
		$this->getChunkAt($pos->x >> 4, $pos->z >> 4)->getBlock($pos->x & 0x0f, $pos->y & 0x7f, $pos->z & 0x0f, $blockId, $meta);
		return Block::get($blockId, $meta, Position::fromObject(clone $pos, $this));
	}

	/**
	 * Sets on Vector3 the data from a Block object,
	 * does block updates and puts the changes to the send queue.
	 *
	 * @param Vector3 $pos
	 * @param Block   $block
	 */
	public function setBlock(Vector3 $pos, Block $block){
		$this->getChunkAt($pos->x >> 4, $pos->z >> 4)->setBlock($pos->x & 0x0f, $pos->y & 0x7f, $pos->z & 0x0f, $block->getID(), $block->getDamage());
		//TODO:
		//block updates
		//block change send queue
		//etc.
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
		return $this->getChunkAt($x >> 4, $z >> 4)->getBlockId($x & 0x0f, $y & 0x7f, $z & 0x0f);
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
		$this->getChunkAt($x >> 4, $z >> 4)->setBlockId($x & 0x0f, $y & 0x7f, $z & 0x0f, $id & 0xff);
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
		return $this->getChunkAt($x >> 4, $z >> 4)->getBlockData($x & 0x0f, $y & 0x7f, $z & 0x0f);
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
		$this->getChunkAt($x >> 4, $z >> 4)->setBlockData($x & 0x0f, $y & 0x7f, $z & 0x0f, $data & 0x0f);
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
		return $this->getChunkAt($x >> 4, $z >> 4)->getBlockSkyLight($x & 0x0f, $y & 0x7f, $z & 0x0f);
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
		$this->getChunkAt($x >> 4, $z >> 4)->setBlockSkyLight($x & 0x0f, $y & 0x7f, $z & 0x0f, $level & 0x0f);
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
		return $this->getChunkAt($x >> 4, $z >> 4)->getBlockLight($x & 0x0f, $y & 0x7f, $z & 0x0f);
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
		$this->getChunkAt($x >> 4, $z >> 4)->setBlockLight($x & 0x0f, $y & 0x7f, $z & 0x0f, $level & 0x0f);
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
		$this->provider->getChunk($x, $z, $create);
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

		return $this->getChunkAt($x >> 4, $z >> 4)->getHighestBlockAt($x & 0x0f, $z & 0x0f);
	}

	/**
	 * @param int $x
	 * @param int $z
	 * @return bool
	 */
	public function isChunkLoaded($x, $z){
		return $this->provider->isChunkLoaded($x, $z);
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
		$entity->kill();
		if($entity instanceof Player){
			$this->players->detach($entity);
			//$this->everyoneSleeping();
		}

		if($this->isChunkLoaded($entity->chunkX, $entity->chunkZ)){
			$this->getChunkAt($entity->chunkX, $entity->chunkZ)->removeEntity($entity);
		}

		$this->entities->detach($entity);
	}

	public function isChunkInUse($x, $z){
		return isset($this->usedChunks[static::chunkHash($x, $z)]);
	}

	public function loadChunk($x, $z, $generate = true){
		if($generate === true){
			return $this->getChunkAt($x, $z, true) instanceof Chunk;
		}

		$this->cancelUnloadChunkRequest($x, $z);

		$chunk = $this->provider->getChunk($x, $z, false);
		if($chunk instanceof Chunk){
			return true;
		}else{
			$this->provider->loadChunk($x, $z);
			return $this->provider->getChunk($x, $z) instanceof Chunk;
		}
	}

	protected function queueUnloadChunk($x, $z){
		//TODO
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



		$this->provider->unloadChunk($x, $z);

		return true;
	}

	public function generateChunk($x, $z){
		//TODO
	}

	public function regenerateChunk($x, $z){
		$this->unloadChunk($x, $z);

		$this->cancelUnloadChunkRequest($x, $z);

		//TODO: generate & refresh chunk from the generator object
	}

	public function doChunkGarbageCollection(){
		if(count($this->unloadQueue) > 0){
			foreach($this->unloadQueue as $index => $chunk){

				//If the chunk can't be unloaded, it stays on the queue
				if($this->unloadChunk($chunk->getX(), $chunk->getZ(), true)){
					unset($this->unloadQueue[$index]);
				}
			}
		}
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
