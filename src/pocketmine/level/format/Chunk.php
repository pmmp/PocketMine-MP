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
 * Implementation of MCPE-style chunks with subchunks with XZY ordering.
 */
declare(strict_types=1);

namespace pocketmine\level\format;

use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIds;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityFactory;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\NetworkBinaryStream;
use pocketmine\Player;
use pocketmine\tile\Spawnable;
use pocketmine\tile\Tile;
use pocketmine\tile\TileFactory;
use pocketmine\utils\BinaryStream;
use function array_fill;
use function array_filter;
use function array_map;
use function array_values;
use function assert;
use function chr;
use function count;
use function ord;
use function pack;
use function str_repeat;
use function strlen;
use function unpack;

class Chunk{

	public const MAX_SUBCHUNKS = 16;

	/** @var int */
	protected $x;
	/** @var int */
	protected $z;

	/** @var bool */
	protected $hasChanged = false;

	/** @var bool */
	protected $isInit = false;

	/** @var bool */
	protected $lightPopulated = false;
	/** @var bool */
	protected $terrainGenerated = false;
	/** @var bool */
	protected $terrainPopulated = false;

	/** @var int */
	protected $height = Chunk::MAX_SUBCHUNKS;

	/** @var \SplFixedArray|SubChunkInterface[] */
	protected $subChunks;

	/** @var EmptySubChunk */
	protected $emptySubChunk;

	/** @var Tile[] */
	protected $tiles = [];

	/** @var Entity[] */
	protected $entities = [];

	/** @var \SplFixedArray|int[] */
	protected $heightMap;

	/** @var string */
	protected $biomeIds;

	/** @var CompoundTag[] */
	protected $NBTtiles = [];

	/** @var CompoundTag[] */
	protected $NBTentities = [];

	/**
	 * @param int                 $chunkX
	 * @param int                 $chunkZ
	 * @param SubChunkInterface[] $subChunks
	 * @param CompoundTag[]       $entities
	 * @param CompoundTag[]       $tiles
	 * @param string              $biomeIds
	 * @param int[]               $heightMap
	 */
	public function __construct(int $chunkX, int $chunkZ, array $subChunks = [], ?array $entities = null, ?array $tiles = null, string $biomeIds = "", array $heightMap = []){
		$this->x = $chunkX;
		$this->z = $chunkZ;

		$this->height = Chunk::MAX_SUBCHUNKS; //TODO: add a way of changing this

		$this->subChunks = new \SplFixedArray($this->height);
		$this->emptySubChunk = EmptySubChunk::getInstance();

		foreach($this->subChunks as $y => $null){
			$this->subChunks[$y] = $subChunks[$y] ?? $this->emptySubChunk;
		}

		if(count($heightMap) === 256){
			$this->heightMap = \SplFixedArray::fromArray($heightMap);
		}else{
			assert(count($heightMap) === 0, "Wrong HeightMap value count, expected 256, got " . count($heightMap));
			$val = ($this->height * 16);
			$this->heightMap = \SplFixedArray::fromArray(array_fill(0, 256, $val));
		}

		if(strlen($biomeIds) === 256){
			$this->biomeIds = $biomeIds;
		}else{
			assert($biomeIds === "", "Wrong BiomeIds value count, expected 256, got " . strlen($biomeIds));
			$this->biomeIds = str_repeat("\x00", 256);
		}

		$this->NBTtiles = $tiles;
		$this->NBTentities = $entities;
	}

	/**
	 * @return int
	 */
	public function getX() : int{
		return $this->x;
	}

	/**
	 * @return int
	 */
	public function getZ() : int{
		return $this->z;
	}

	public function setX(int $x) : void{
		$this->x = $x;
	}

	/**
	 * @param int $z
	 */
	public function setZ(int $z) : void{
		$this->z = $z;
	}

	/**
	 * Returns the chunk height in count of subchunks.
	 *
	 * @return int
	 */
	public function getHeight() : int{
		return $this->height;
	}

	/**
	 * Returns the internal ID of the blockstate at the given coordinates.
	 *
	 * @param int $x 0-15
	 * @param int $y
	 * @param int $z 0-15
	 *
	 * @return int bitmap, (id << 4) | meta
	 */
	public function getFullBlock(int $x, int $y, int $z) : int{
		return $this->getSubChunk($y >> 4)->getFullBlock($x, $y & 0x0f, $z);
	}

	/**
	 * Sets the blockstate at the given coordinate by internal ID.
	 *
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 * @param int $block
	 */
	public function setFullBlock(int $x, int $y, int $z, int $block) : void{
		$this->getSubChunk($y >> 4, true)->setFullBlock($x, $y & 0xf, $z, $block);
		$this->hasChanged = true;
	}

	/**
	 * Returns the sky light level at the specified chunk block coordinates
	 *
	 * @param int $x 0-15
	 * @param int $y
	 * @param int $z 0-15
	 *
	 * @return int 0-15
	 */
	public function getBlockSkyLight(int $x, int $y, int $z) : int{
		return $this->getSubChunk($y >> 4)->getBlockSkyLight($x, $y & 0x0f, $z);
	}

	/**
	 * Sets the sky light level at the specified chunk block coordinates
	 *
	 * @param int $x 0-15
	 * @param int $y
	 * @param int $z 0-15
	 * @param int $level 0-15
	 */
	public function setBlockSkyLight(int $x, int $y, int $z, int $level) : void{
		$this->getSubChunk($y >> 4, true)->setBlockSkyLight($x, $y & 0x0f, $z, $level);
	}

	/**
	 * @param int $level
	 */
	public function setAllBlockSkyLight(int $level) : void{
		$char = chr(($level & 0x0f) | ($level << 4));
		$data = str_repeat($char, 2048);
		for($y = $this->getHighestSubChunkIndex(); $y >= 0; --$y){
			$this->getSubChunk($y, true)->setBlockSkyLightArray($data);
		}
	}

	/**
	 * Returns the block light level at the specified chunk block coordinates
	 *
	 * @param int $x 0-15
	 * @param int $y 0-15
	 * @param int $z 0-15
	 *
	 * @return int 0-15
	 */
	public function getBlockLight(int $x, int $y, int $z) : int{
		return $this->getSubChunk($y >> 4)->getBlockLight($x, $y & 0x0f, $z);
	}

	/**
	 * Sets the block light level at the specified chunk block coordinates
	 *
	 * @param int $x 0-15
	 * @param int $y 0-15
	 * @param int $z 0-15
	 * @param int $level 0-15
	 */
	public function setBlockLight(int $x, int $y, int $z, int $level) : void{
		$this->getSubChunk($y >> 4, true)->setBlockLight($x, $y & 0x0f, $z, $level);
	}

	/**
	 * @param int $level
	 */
	public function setAllBlockLight(int $level) : void{
		$char = chr(($level & 0x0f) | ($level << 4));
		$data = str_repeat($char, 2048);
		for($y = $this->getHighestSubChunkIndex(); $y >= 0; --$y){
			$this->getSubChunk($y, true)->setBlockLightArray($data);
		}
	}

	/**
	 * Returns the Y coordinate of the highest non-air block at the specified X/Z chunk block coordinates
	 *
	 * @param int $x 0-15
	 * @param int $z 0-15
	 *
	 * @return int 0-255, or -1 if there are no blocks in the column
	 */
	public function getHighestBlockAt(int $x, int $z) : int{
		$index = $this->getHighestSubChunkIndex();
		if($index === -1){
			return -1;
		}

		for($y = $index; $y >= 0; --$y){
			$height = $this->getSubChunk($y)->getHighestBlockAt($x, $z) | ($y << 4);
			if($height !== -1){
				return $height;
			}
		}

		return -1;
	}

	public function getMaxY() : int{
		return ($this->getHighestSubChunkIndex() << 4) | 0x0f;
	}

	/**
	 * Returns the heightmap value at the specified X/Z chunk block coordinates
	 *
	 * @param int $x 0-15
	 * @param int $z 0-15
	 *
	 * @return int
	 */
	public function getHeightMap(int $x, int $z) : int{
		return $this->heightMap[($z << 4) | $x];
	}

	/**
	 * Returns the heightmap value at the specified X/Z chunk block coordinates
	 *
	 * @param int $x 0-15
	 * @param int $z 0-15
	 * @param int $value
	 */
	public function setHeightMap(int $x, int $z, int $value) : void{
		$this->heightMap[($z << 4) | $x] = $value;
	}

	/**
	 * Recalculates the heightmap for the whole chunk.
	 */
	public function recalculateHeightMap() : void{
		for($z = 0; $z < 16; ++$z){
			for($x = 0; $x < 16; ++$x){
				$this->recalculateHeightMapColumn($x, $z);
			}
		}
	}

	/**
	 * Recalculates the heightmap for the block column at the specified X/Z chunk coordinates
	 *
	 * @param int $x 0-15
	 * @param int $z 0-15
	 *
	 * @return int New calculated heightmap value (0-256 inclusive)
	 */
	public function recalculateHeightMapColumn(int $x, int $z) : int{
		$max = $this->getHighestBlockAt($x, $z);
		for($y = $max; $y >= 0; --$y){
			if(BlockFactory::$lightFilter[$state = $this->getFullBlock($x, $y, $z)] > 1 or BlockFactory::$diffusesSkyLight[$state]){
				break;
			}
		}

		$this->setHeightMap($x, $z, $y + 1);
		return $y + 1;
	}

	/**
	 * Performs basic sky light population on the chunk.
	 * This does not cater for adjacent sky light, this performs direct sky light population only. This may cause some strange visual artifacts
	 * if the chunk is light-populated after being terrain-populated.
	 *
	 * TODO: fast adjacent light spread
	 */
	public function populateSkyLight() : void{
		$maxY = $this->getMaxY();

		$this->setAllBlockSkyLight(0);

		for($x = 0; $x < 16; ++$x){
			for($z = 0; $z < 16; ++$z){
				$heightMap = $this->getHeightMap($x, $z);

				for($y = $maxY; $y >= $heightMap; --$y){
					$this->setBlockSkyLight($x, $y, $z, 15);
				}

				$light = 15;
				for(; $y >= 0; --$y){
					if($light > 0){
						$light -= BlockFactory::$lightFilter[$this->getFullBlock($x, $y, $z)];
						if($light <= 0){
							break;
						}
					}
					$this->setBlockSkyLight($x, $y, $z, $light);
				}
			}
		}
	}

	/**
	 * Returns the biome ID at the specified X/Z chunk block coordinates
	 *
	 * @param int $x 0-15
	 * @param int $z 0-15
	 *
	 * @return int 0-255
	 */
	public function getBiomeId(int $x, int $z) : int{
		return ord($this->biomeIds{($z << 4) | $x});
	}

	/**
	 * Sets the biome ID at the specified X/Z chunk block coordinates
	 *
	 * @param int $x 0-15
	 * @param int $z 0-15
	 * @param int $biomeId 0-255
	 */
	public function setBiomeId(int $x, int $z, int $biomeId) : void{
		$this->hasChanged = true;
		$this->biomeIds{($z << 4) | $x} = chr($biomeId & 0xff);
	}

	/**
	 * @return bool
	 */
	public function isLightPopulated() : bool{
		return $this->lightPopulated;
	}

	/**
	 * @param bool $value
	 */
	public function setLightPopulated(bool $value = true) : void{
		$this->lightPopulated = $value;
	}

	/**
	 * @return bool
	 */
	public function isPopulated() : bool{
		return $this->terrainPopulated;
	}

	/**
	 * @param bool $value
	 */
	public function setPopulated(bool $value = true) : void{
		$this->terrainPopulated = $value;
	}

	/**
	 * @return bool
	 */
	public function isGenerated() : bool{
		return $this->terrainGenerated;
	}

	/**
	 * @param bool $value
	 */
	public function setGenerated(bool $value = true) : void{
		$this->terrainGenerated = $value;
	}

	/**
	 * @param Entity $entity
	 */
	public function addEntity(Entity $entity) : void{
		if($entity->isClosed()){
			throw new \InvalidArgumentException("Attempted to add a garbage closed Entity to a chunk");
		}
		$this->entities[$entity->getId()] = $entity;
		if(!($entity instanceof Player) and $this->isInit){
			$this->hasChanged = true;
		}
	}

	/**
	 * @param Entity $entity
	 */
	public function removeEntity(Entity $entity) : void{
		unset($this->entities[$entity->getId()]);
		if(!($entity instanceof Player) and $this->isInit){
			$this->hasChanged = true;
		}
	}

	/**
	 * @param Tile $tile
	 */
	public function addTile(Tile $tile) : void{
		if($tile->isClosed()){
			throw new \InvalidArgumentException("Attempted to add a garbage closed Tile to a chunk");
		}

		if(isset($this->tiles[$index = Chunk::blockHash($tile->x, $tile->y, $tile->z)]) and $this->tiles[$index] !== $tile){
			$this->tiles[$index]->close();
		}
		$this->tiles[$index] = $tile;
		if($this->isInit){
			$this->hasChanged = true;
		}
	}

	/**
	 * @param Tile $tile
	 */
	public function removeTile(Tile $tile) : void{
		unset($this->tiles[Chunk::blockHash($tile->x, $tile->y, $tile->z)]);
		if($this->isInit){
			$this->hasChanged = true;
		}
	}

	/**
	 * Returns an array of entities currently using this chunk.
	 *
	 * @return Entity[]
	 */
	public function getEntities() : array{
		return $this->entities;
	}

	/**
	 * @return Entity[]
	 */
	public function getSavableEntities() : array{
		return array_filter($this->entities, function(Entity $entity) : bool{ return $entity->canSaveWithChunk() and !$entity->isClosed(); });
	}

	/**
	 * @return Tile[]
	 */
	public function getTiles() : array{
		return $this->tiles;
	}

	/**
	 * Returns the tile at the specified chunk block coordinates, or null if no tile exists.
	 *
	 * @param int $x 0-15
	 * @param int $y
	 * @param int $z 0-15
	 *
	 * @return Tile|null
	 */
	public function getTile(int $x, int $y, int $z) : ?Tile{
		return $this->tiles[Chunk::blockHash($x, $y, $z)] ?? null;
	}

	/**
	 * Called when the chunk is unloaded, closing entities and tiles.
	 */
	public function onUnload() : void{
		foreach($this->getEntities() as $entity){
			if($entity instanceof Player){
				continue;
			}
			$entity->close();
		}

		foreach($this->getTiles() as $tile){
			$tile->close();
		}
	}

	/**
	 * @return CompoundTag[]
	 */
	public function getNBTtiles() : array{
		return $this->NBTtiles ?? array_map(function(Tile $tile) : CompoundTag{ return $tile->saveNBT(); }, $this->tiles);
	}

	/**
	 * @return CompoundTag[]
	 */
	public function getNBTentities() : array{
		return $this->NBTentities ?? array_map(function(Entity $entity) : CompoundTag{ return $entity->saveNBT(); }, $this->getSavableEntities());
	}

	/**
	 * Deserializes tiles and entities from NBT
	 *
	 * @param Level $level
	 */
	public function initChunk(Level $level) : void{
		if(!$this->isInit){
			$changed = false;
			if($this->NBTentities !== null){
				$level->timings->syncChunkLoadEntitiesTimer->startTiming();
				foreach($this->NBTentities as $nbt){
					if($nbt instanceof CompoundTag){
						try{
							$entity = EntityFactory::createFromData($level, $nbt);
							if(!($entity instanceof Entity)){
								$level->getServer()->getLogger()->warning("Chunk $this->x $this->z: Deleted unknown entity type " . $nbt->getString("id", $nbt->getString("identifier", "<unknown>", true), true));
								$changed = true;
								continue;
							}
						}catch(\Exception $t){ //TODO: this shouldn't be here
							$level->getServer()->getLogger()->logException($t);
							$changed = true;
							continue;
						}
					}
				}
				$level->timings->syncChunkLoadEntitiesTimer->stopTiming();

				$level->timings->syncChunkLoadTileEntitiesTimer->startTiming();
				foreach($this->NBTtiles as $nbt){
					if($nbt instanceof CompoundTag){
						if(($tile = TileFactory::createFromData($level, $nbt)) !== null){
							$level->addTile($tile);
						}else{
							$level->getServer()->getLogger()->warning("Chunk $this->x $this->z: Deleted unknown tile entity type " . $nbt->getString("id", "<unknown>", true));
							$changed = true;
							continue;
						}
					}
				}

				$level->timings->syncChunkLoadTileEntitiesTimer->stopTiming();

				$this->NBTentities = null;
				$this->NBTtiles = null;
			}

			$this->hasChanged = $changed;

			$this->isInit = true;
		}
	}

	/**
	 * @return string
	 */
	public function getBiomeIdArray() : string{
		return $this->biomeIds;
	}

	/**
	 * @return int[]
	 */
	public function getHeightMapArray() : array{
		return $this->heightMap->toArray();
	}

	/**
	 * @return bool
	 */
	public function hasChanged() : bool{
		return $this->hasChanged;
	}

	/**
	 * @param bool $value
	 */
	public function setChanged(bool $value = true) : void{
		$this->hasChanged = $value;
	}

	/**
	 * Returns the subchunk at the specified subchunk Y coordinate, or an empty, unmodifiable stub if it does not exist or the coordinate is out of range.
	 *
	 * @param int  $y
	 * @param bool $generateNew Whether to create a new, modifiable subchunk if there is not one in place
	 *
	 * @return SubChunkInterface
	 */
	public function getSubChunk(int $y, bool $generateNew = false) : SubChunkInterface{
		if($y < 0 or $y >= $this->height){
			return $this->emptySubChunk;
		}elseif($generateNew and $this->subChunks[$y] instanceof EmptySubChunk){
			$this->subChunks[$y] = new SubChunk([new PalettedBlockArray(BlockIds::AIR << 4)]);
		}

		return $this->subChunks[$y];
	}

	/**
	 * Sets a subchunk in the chunk index
	 *
	 * @param int                    $y
	 * @param SubChunkInterface|null $subChunk
	 * @param bool                   $allowEmpty Whether to check if the chunk is empty, and if so replace it with an empty stub
	 *
	 * @return bool
	 */
	public function setSubChunk(int $y, ?SubChunkInterface $subChunk, bool $allowEmpty = false) : bool{
		if($y < 0 or $y >= $this->height){
			return false;
		}
		if($subChunk === null or ($subChunk->isEmpty() and !$allowEmpty)){
			$this->subChunks[$y] = $this->emptySubChunk;
		}else{
			$this->subChunks[$y] = $subChunk;
		}
		$this->hasChanged = true;
		return true;
	}

	/**
	 * @return \SplFixedArray|SubChunkInterface[]
	 */
	public function getSubChunks() : \SplFixedArray{
		return $this->subChunks;
	}

	/**
	 * Returns the Y coordinate of the highest non-empty subchunk in this chunk.
	 *
	 * @return int
	 */
	public function getHighestSubChunkIndex() : int{
		for($y = $this->subChunks->count() - 1; $y >= 0; --$y){
			if($this->subChunks[$y] instanceof EmptySubChunk){
				//No need to thoroughly prune empties at runtime, this will just reduce performance.
				continue;
			}
			break;
		}

		return $y;
	}

	/**
	 * Returns the count of subchunks that need sending to players
	 *
	 * @return int
	 */
	public function getSubChunkSendCount() : int{
		return $this->getHighestSubChunkIndex() + 1;
	}

	/**
	 * Disposes of empty subchunks and frees data where possible
	 */
	public function collectGarbage() : void{
		foreach($this->subChunks as $y => $subChunk){
			if($subChunk instanceof SubChunk){
				$subChunk->collectGarbage();
				if($subChunk->isEmpty()){
					$this->subChunks[$y] = $this->emptySubChunk;
				}
			}
		}
	}

	/**
	 * Serializes the chunk for sending to players
	 *
	 * @return string
	 */
	public function networkSerialize() : string{
		$stream = new NetworkBinaryStream();
		$subChunkCount = $this->getSubChunkSendCount();
		$stream->putByte($subChunkCount);

		if(empty(BlockFactory::$staticRuntimeIdMap)){
			BlockFactory::registerStaticRuntimeIdMappings();
		}

		for($y = 0; $y < $subChunkCount; ++$y){
			$layers = $this->subChunks[$y]->getBlockLayers();
			$stream->putByte(8); //version

			$stream->putByte(count($layers));

			foreach($layers as $blocks){
				$stream->putByte(($blocks->getBitsPerBlock() << 1) | 1); //last 1-bit means "network format", but seems pointless
				$stream->put($blocks->getWordArray());
				$palette = $blocks->getPalette();
				$stream->putVarInt(count($palette)); //yes, this is intentionally zigzag
				foreach($palette as $p){
					$stream->putVarInt(BlockFactory::toStaticRuntimeId($p >> 4, $p & 0xf));
				}
			}
		}
		$stream->put(pack("v*", ...$this->heightMap));
		$stream->put($this->biomeIds);
		$stream->putByte(0); //border block array count
		//Border block entry format: 1 byte (4 bits X, 4 bits Z). These are however useless since they crash the regular client.

		foreach($this->tiles as $tile){
			if($tile instanceof Spawnable){
				$stream->put($tile->getSerializedSpawnCompound());
			}
		}

		return $stream->getBuffer();
	}

	/**
	 * Fast-serializes the chunk for passing between threads
	 * TODO: tiles and entities
	 *
	 * @return string
	 */
	public function fastSerialize() : string{
		$stream = new BinaryStream();
		$stream->putInt($this->x);
		$stream->putInt($this->z);
		$stream->putByte(($this->lightPopulated ? 4 : 0) | ($this->terrainPopulated ? 2 : 0) | ($this->terrainGenerated ? 1 : 0));
		if($this->terrainGenerated){
			//subchunks
			$count = 0;
			$subStream = new BinaryStream();
			foreach($this->subChunks as $y => $subChunk){
				if($subChunk instanceof EmptySubChunk){
					continue;
				}
				++$count;

				$subStream->putByte($y);
				$layers = $subChunk->getBlockLayers();
				$subStream->putByte(count($subChunk->getBlockLayers()));
				foreach($layers as $blocks){
					$wordArray = $blocks->getWordArray();
					$palette = $blocks->getPalette();

					$subStream->putByte($blocks->getBitsPerBlock());
					$subStream->put($wordArray);
					$subStream->putInt(count($palette));
					foreach($palette as $p){
						$subStream->putInt($p);
					}
				}

				if($this->lightPopulated){
					$subStream->put($subChunk->getBlockSkyLightArray());
					$subStream->put($subChunk->getBlockLightArray());
				}
			}
			$stream->putByte($count);
			$stream->put($subStream->getBuffer());

			//biomes
			$stream->put($this->biomeIds);
			if($this->lightPopulated){
				$stream->put(pack("v*", ...$this->heightMap));
			}
		}

		return $stream->getBuffer();
	}

	/**
	 * Deserializes a fast-serialized chunk
	 *
	 * @param string $data
	 *
	 * @return Chunk
	 */
	public static function fastDeserialize(string $data) : Chunk{
		$stream = new BinaryStream($data);

		$x = $stream->getInt();
		$z = $stream->getInt();
		$flags = $stream->getByte();
		$lightPopulated = (bool) ($flags & 4);
		$terrainPopulated = (bool) ($flags & 2);
		$terrainGenerated = (bool) ($flags & 1);

		$subChunks = [];
		$biomeIds = "";
		$heightMap = [];
		if($terrainGenerated){
			$count = $stream->getByte();
			for($subCount = 0; $subCount < $count; ++$subCount){
				$y = $stream->getByte();

				/** @var PalettedBlockArray[] $layers */
				$layers = [];
				for($i = 0, $layerCount = $stream->getByte(); $i < $layerCount; ++$i){
					$bitsPerBlock = $stream->getByte();
					$words = $stream->get(PalettedBlockArray::getExpectedWordArraySize($bitsPerBlock));
					$palette = [];
					for($k = 0, $paletteSize = $stream->getInt(); $k < $paletteSize; ++$k){
						$palette[] = $stream->getInt();
					}

					$layers[] = PalettedBlockArray::fromData($bitsPerBlock, $words, $palette);
				}
				$subChunks[$y] = new SubChunk(
					$layers, $lightPopulated ? $stream->get(2048) : "", $lightPopulated ? $stream->get(2048) : "" //blocklight
				);
			}

			$biomeIds = $stream->get(256);
			if($lightPopulated){
				$heightMap = array_values(unpack("v*", $stream->get(512)));
			}
		}

		$chunk = new Chunk($x, $z, $subChunks, null, null, $biomeIds, $heightMap);
		$chunk->setGenerated($terrainGenerated);
		$chunk->setPopulated($terrainPopulated);
		$chunk->setLightPopulated($lightPopulated);

		return $chunk;
	}

	/**
	 * Hashes the given chunk block coordinates into a single integer.
	 *
	 * @param int $x 0-15
	 * @param int $y
	 * @param int $z 0-15
	 *
	 * @return int
	 */
	public static function blockHash(int $x, int $y, int $z) : int{
		return ($y << 8) | (($z & 0x0f) << 4) | ($x & 0x0f);
	}
}
