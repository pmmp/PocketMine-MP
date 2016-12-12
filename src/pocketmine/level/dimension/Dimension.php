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

declare(strict_types = 1);

namespace pocketmine\level\dimension;

use pocketmine\entity\Entity;
use pocketmine\level\format\Chunk;
use pocketmine\level\format\generic\GenericChunk;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\network\protocol\DataPacket;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\tile\Tile;

abstract class Dimension{

	const SKY_COLOR_BLUE = 0;
	const SKY_COLOR_RED = 1;
	const SKY_COLOR_PURPLE_STATIC = 2;

	/** @var Level */
	protected $level;
	/** @var string */
	protected $name;
	/** @var DimensionType */
	protected $dimensionType;
	/** @var int */
	protected $saveId;

	/** @var Chunk[] */
	protected $chunks = [];

	/** @var DataPacket[] */
	protected $chunkCache = [];

	/** @var DataPacket[] */
	protected $chunkPackets = [];

	/** @var GenericChunk */
	public $emptyChunk;

	/** @var Block */
	protected $blockCache = [];

	/** @var Player[] */
	protected $players = [];

	/** @var Entity[] */
	protected $entities = [];
	/** @var Tile[] */
	protected $tiles = [];

	/** @var Entity[] */
	public $updateEntities = [];
	/** @var Tile[] */
	public $updateTiles = [];

	protected $motionToSend = [];
	protected $moveToSend = [];

	protected $rainLevel = 0;
	protected $thunderLevel = 0;
	protected $nextWeatherChange;

	/**
	 * @param string $name   the dimension's display name
	 * @param int    $typeId defaults to Overworld, used to initialise dimension properties. Must be a constant from {@link DimensionType}
	 */
	public function __construct(string $name, int $typeId = DimensionType::OVERWORLD){
		$this->name = $name;
		$this->setDimensionType($typeId);
		$this->emptyChunk = new GenericChunk(null, 0, 0); //TODO: clone methods for chunks to get more performance creating new chunks.
	}

	/**
	 * Returns the parent level of this dimension, or null if the dimension has not yet been attached to a Level.
	 *
	 * @return Level|null
	 */
	public function getLevel(){
		return $this->level;
	}

	/**
	 * Sets the parent level of this dimension.
	 *
	 * @param Level $level
	 *
	 * @return bool indication of success
	 *
	 * @internal
	 */
	public function setLevel(Level $level) : bool{
		if($this->level instanceof Level){
			return false;
		}

		if(($saveId = $level->addDimension($this)) !== false){
			$this->level = $level;
			$this->saveId = $saveId;
			return true;
		}

		return false;
	}

	/**
	 * Returns a DimensionType object containing immutable dimension properties
	 *
	 * @return DimensionType
	 */
	public function getDimensionType() : DimensionType{
		return $this->dimensionType;
	}

	/**
	 * Sets the dimension type of this dimension.
	 *
	 * @param int $typeId the ID of the dimension type. See {@link DimensionType} for a list of possible constant values.
	 *
	 * @throws \InvalidArgumentException if the specified dimension type ID was not recognised
	 */
	public function setDimensionType(int $typeId){
		if(!(($type = DimensionType::get($typeId)) instanceof DimensionType)){
			throw new \InvalidArgumentException("Invalid dimension type ID $typeId");
		}
		$this->dimensionType = $type;
		//TODO: update sky colours seen by clients, remove skylight from chunks for The End and Nether
	}

	/**
	 * Returns the dimension's ID. Unique within levels only. This is used for world saves.
	 *
	 * NOTE: For vanilla dimensions, this will NOT match the folder name in Anvil/MCRegion formats due to inconsistencies in dimension
	 * IDs between PC and PE. For example the Nether has saveID 1, but will be saved in the DIM-1 folder in the world save.
	 * Custom dimensions will be consistent.
	 *
	 * @return int
	 */
	public function getSaveId() : int{
		return $this->saveId;
	}

	/**
	 * Returns the friendly name of this dimension.
	 *
	 * @return string
	 */
	public function getDimensionName() : string{
		return $this->name;
	}

	/**
	 * Returns the sky colour of this dimension based on the dimension type.
	 *
	 * @return int
	 */
	public function getSkyColor() : int{
		return $this->dimensionType->getSkyColor();
	}

	/**
	 * Returns the dimension max build height as per MCPE (because the Nether in PE annoyingly only has a build height of 128)
	 *
	 * @return int
	 */
	public function getMaxBuildHeight() : int{
		return $this->dimensionType->getMaxBuildHeight();
	}

	/**
	 * Returns all players in the dimension.
	 *
	 * @return Player[]
	 */
	public function getPlayers() : array{
		return $this->players;
	}

	/**
	 * Returns players in the specified chunk.
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
	 * Returns chunk loaders using the specified chunk
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
	 * Adds an entity to the dimension index.
	 *
	 * @param Entity $entity
	 */
	public function addEntity(Entity $entity){
		if($entity instanceof Player){
			$this->players[$entity->getId()] = $entity;
		}
		$this->entities[$entity->getId()] = $entity;
	}

	/**
	 * Removes an entity from the dimension's entity index. We do NOT close the entity here as it may simply be getting transferred
	 * to another dimension.
	 *
	 * @param Entity $entity
	 */
	public function removeEntity(Entity $entity){
		if($entity instanceof Player){
			unset($this->players[$entity->getId()]);
			$this->checkSleep();
		}

		unset($this->entities[$entity->getId()]);
		unset($this->updateEntities[$entity->getId()]);
	}

	/**
	 * Returns all entities in the dimension.
	 *
	 * @return Entity[]
	 */
	public function getEntities() : array{
		return $this->entities;
	}

	/**
	 * Returns the entity with the specified ID in this dimension, or null if it does not exist.
	 *
	 * @param int $entityId
	 *
	 * @return Entity|null
	 */
	public function getEntity(int $entityId){
		return $this->entities[$entityId] ?? null;
	}

	/**
	 * Returns a list of the entities in the specified chunk. Returns an empty array if the chunk is not loaded.
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
	 * Adds a tile to the dimension index.
	 * @param Tile $tile
	 *
	 * @throws LevelException
	 */
	public function addTile(Tile $tile){
		/*if($tile->getLevel() !== $this){
			throw new LevelException("Invalid Tile level");
		}*/
		$this->tiles[$tile->getId()] = $tile;
		$this->clearChunkCache($tile->getX() >> 4, $tile->getZ() >> 4);
	}

	/**
	 * Removes a tile from the dimension index.
	 * @param Tile $tile
	 *
	 * @throws LevelException
	 */
	public function removeTile(Tile $tile){
		/*if($tile->getLevel() !== $this){
			throw new LevelException("Invalid Tile level");
		}*/

		unset($this->tiles[$tile->getId()]);
		unset($this->updateTiles[$tile->getId()]);
		$this->clearChunkCache($tile->getX() >> 4, $tile->getZ() >> 4);
	}

	/**
	 * Returns a list of the Tiles in this dimension
	 *
	 * @return Tile[]
	 */
	public function getTiles() : array{
		return $this->tiles;
	}

	/**
	 * Returns the tile with the specified ID in this dimension
	 * @param $tileId
	 *
	 * @return Tile|null
	 */
	public function getTileById(int $tileId){
		return $this->tiles[$tileId] ?? null;
	}

	/**
	 * Returns the Tile at the specified position, or null if not found
	 *
	 * @param Vector3 $pos
	 *
	 * @return Tile|null
	 */
	public function getTile(Vector3 $pos){
		$chunk = $this->getChunk($pos->x >> 4, $pos->z >> 4, false);

		if($chunk !== null){
			return $chunk->getTile($pos->x & 0x0f, $pos->y & Level::Y_MASK, $pos->z & 0x0f);
		}

		return null;
	}

	/**
	 * Gives a list of the Tile entities in the specified chunk. Returns an empty array if the chunk is not loaded.
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
	 * Returns an array of currently loaded chunks.
	 *
	 * @return Chunk[]
	 */
	public function getChunks() : array{
		return $this->chunks;
	}

	/**
	 * Returns the chunk at the specified index, or null if it does not exist and has not been generated
	 *
	 * @param int  $chunkX
	 * @param int  $chunkZ
	 * @param bool $generate whether to generate the chunk if it does not exist.
	 *
	 * @return Chunk|null
	 */
	public function getChunk(int $x, int $z, bool $generate = false){
		//TODO: alter this to handle asynchronous chunk loading
		if(isset($this->chunks[$index = Level::chunkHash($x, $z)])){
			return $this->chunks[$index];
		}elseif($this->loadChunk($x, $z, $generate)){
			return $this->chunks[$index];
		}

		return null;
	}

	/**
	 * Executes ticks on this dimension
	 *
	 * @param int $currentTick
	 */
	public function doTick(int $currentTick){
		$this->doWeatherTick($currentTick);
		//TODO: More stuff
	}

	/**
	 * Performs weather ticks
	 *
	 * @param int $currentTick
	 */
	protected function doWeatherTick(int $currentTick){

	}

	/**
	 * Returns the current rain strength in this dimension
	 *
	 * @return int
	 */
	public function getRainLevel() : int{
		return $this->rainLevel;
	}

	/**
	 * Sets the rain level and sends changes to players.
	 *
	 * @param int $level
	 */
	public function setRainLevel(int $level){
		//TODO
	}

	/**
	 * Sends weather changes to the specified targets, or to all players in the dimension if not specified.
	 *
	 * @param Player $targets,...
	 */
	public function sendWeather(Player ...$targets){
		$rain = new LevelEventPacket();
		if($this->rainLevel > 0){
			$rain->evid = LevelEventPacket::EVENT_START_RAIN;
			$rain->data = $this->rainLevel;
		}else{
			$rain->evid = LevelEventPacket::EVENT_STOP_RAIN;
		}

		$thunder = new LevelEventPacket();
		if($this->thunderLevel > 0){
			$thunder->evid = LevelEventPacket::EVENT_START_THUNDER;
			$thunder->data = $this->thunderLevel;
		}else{
			$thunder->evid = LevelEventPacket::EVENT_STOP_THUNDER;
		}

		if(count($targets) === 0){
			Server::broadcastPacket($this->players, $rain);
			Server::broadcastPacket($this->players, $thunder);
		}else{
			Server::broadcastPacket($targets, $rain);
			Server::broadcastPacket($targets, $thunder);
		}
	}

	/**
	 * Queues a DataPacket(s) to be sent to all players using the specified chunk on the next tick.
	 *
	 * @param int        $chunkX
	 * @param int        $chunkZ
	 * @param DataPacket $packets,...
	 */
	public function addChunkPacket(int $x, int $z, ...$packets){
		if(!isset($this->chunkPackets[$index = Level::chunkHash($chunkX, $chunkZ)])){
			$this->chunkPackets[$index] = [$packet];
		}else{
			$this->chunkPackets[$index][] = $packet;
		}
	}
}