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

namespace pocketmine\world\format;

use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\tile\Tile;
use pocketmine\block\tile\TileFactory;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\world\World;
use function array_fill;
use function array_filter;
use function array_map;
use function assert;
use function chr;
use function count;
use function ord;
use function str_repeat;
use function strlen;

class Chunk{
	public const DIRTY_FLAG_TERRAIN = 1 << 0;
	public const DIRTY_FLAG_ENTITIES = 1 << 1;
	public const DIRTY_FLAG_TILES = 1 << 2;
	public const DIRTY_FLAG_BIOMES = 1 << 3;

	public const MAX_SUBCHUNKS = 16;

	/** @var int */
	protected $x;
	/** @var int */
	protected $z;

	/** @var int */
	private $dirtyFlags = 0;

	/** @var bool */
	protected $lightPopulated = false;
	/** @var bool */
	protected $terrainGenerated = false;
	/** @var bool */
	protected $terrainPopulated = false;

	/** @var \SplFixedArray|SubChunk[] */
	protected $subChunks;

	/** @var Tile[] */
	protected $tiles = [];

	/** @var Entity[] */
	protected $entities = [];

	/** @var \SplFixedArray|int[] */
	protected $heightMap;

	/** @var string */
	protected $biomeIds;

	/** @var CompoundTag[]|null */
	protected $NBTtiles;

	/** @var CompoundTag[]|null */
	protected $NBTentities;

	/**
	 * @param int           $chunkX
	 * @param int           $chunkZ
	 * @param SubChunk[]    $subChunks
	 * @param CompoundTag[] $entities
	 * @param CompoundTag[] $tiles
	 * @param string        $biomeIds
	 * @param int[]         $heightMap
	 */
	public function __construct(int $chunkX, int $chunkZ, array $subChunks = [], ?array $entities = null, ?array $tiles = null, string $biomeIds = "", array $heightMap = []){
		$this->x = $chunkX;
		$this->z = $chunkZ;

		$this->subChunks = new \SplFixedArray(Chunk::MAX_SUBCHUNKS);

		foreach($this->subChunks as $y => $null){
			$this->subChunks[$y] = $subChunks[$y] ?? new SubChunk(BlockLegacyIds::AIR << 4, []);
		}

		if(count($heightMap) === 256){
			$this->heightMap = \SplFixedArray::fromArray($heightMap);
		}else{
			assert(count($heightMap) === 0, "Wrong HeightMap value count, expected 256, got " . count($heightMap));
			$val = ($this->subChunks->getSize() * 16);
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
		return $this->subChunks->getSize();
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
		$this->getSubChunk($y >> 4)->setFullBlock($x, $y & 0xf, $z, $block);
		$this->dirtyFlags |= self::DIRTY_FLAG_TERRAIN;
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
		return $this->getSubChunk($y >> 4)->getBlockSkyLightArray()->get($x & 0xf, $y & 0x0f, $z & 0xf);
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
		$this->getSubChunk($y >> 4)->getBlockSkyLightArray()->set($x & 0xf, $y & 0x0f, $z & 0xf, $level);
	}

	/**
	 * @param int $level
	 */
	public function setAllBlockSkyLight(int $level) : void{
		for($y = $this->subChunks->count() - 1; $y >= 0; --$y){
			$this->getSubChunk($y)->setBlockSkyLightArray(LightArray::fill($level));
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
		return $this->getSubChunk($y >> 4)->getBlockLightArray()->get($x & 0xf, $y & 0x0f, $z & 0xf);
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
		$this->getSubChunk($y >> 4)->getBlockLightArray()->set($x & 0xf, $y & 0x0f, $z & 0xf, $level);
	}

	/**
	 * @param int $level
	 */
	public function setAllBlockLight(int $level) : void{
		for($y = $this->subChunks->count() - 1; $y >= 0; --$y){
			$this->getSubChunk($y)->setBlockLightArray(LightArray::fill($level));
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
		for($y = $this->subChunks->count() - 1; $y >= 0; --$y){
			$height = $this->getSubChunk($y)->getHighestBlockAt($x, $z) | ($y << 4);
			if($height !== -1){
				return $height;
			}
		}

		return -1;
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
		$this->setAllBlockSkyLight(0);

		for($x = 0; $x < 16; ++$x){
			for($z = 0; $z < 16; ++$z){
				$heightMap = $this->getHeightMap($x, $z);

				for($y = ($this->subChunks->count() * 16) - 1; $y >= $heightMap; --$y){
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
		return ord($this->biomeIds[($z << 4) | $x]);
	}

	/**
	 * Sets the biome ID at the specified X/Z chunk block coordinates
	 *
	 * @param int $x 0-15
	 * @param int $z 0-15
	 * @param int $biomeId 0-255
	 */
	public function setBiomeId(int $x, int $z, int $biomeId) : void{
		$this->biomeIds[($z << 4) | $x] = chr($biomeId & 0xff);
		$this->dirtyFlags |= self::DIRTY_FLAG_BIOMES;
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
		if(!($entity instanceof Player)){
			$this->dirtyFlags |= self::DIRTY_FLAG_ENTITIES;
		}
	}

	/**
	 * @param Entity $entity
	 */
	public function removeEntity(Entity $entity) : void{
		unset($this->entities[$entity->getId()]);
		if(!($entity instanceof Player)){
			$this->dirtyFlags |= self::DIRTY_FLAG_ENTITIES;
		}
	}

	/**
	 * @param Tile $tile
	 */
	public function addTile(Tile $tile) : void{
		if($tile->isClosed()){
			throw new \InvalidArgumentException("Attempted to add a garbage closed Tile to a chunk");
		}

		$pos = $tile->getPos();
		if(isset($this->tiles[$index = Chunk::blockHash($pos->x, $pos->y, $pos->z)]) and $this->tiles[$index] !== $tile){
			$this->tiles[$index]->close();
		}
		$this->tiles[$index] = $tile;
		$this->dirtyFlags |= self::DIRTY_FLAG_TILES;
	}

	/**
	 * @param Tile $tile
	 */
	public function removeTile(Tile $tile) : void{
		$pos = $tile->getPos();
		unset($this->tiles[Chunk::blockHash($pos->x, $pos->y, $pos->z)]);
		$this->dirtyFlags |= self::DIRTY_FLAG_TILES;
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
		return array_filter($this->entities, function(Entity $entity) : bool{ return $entity->canSaveWithChunk(); });
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
	 * @param World $world
	 */
	public function initChunk(World $world) : void{
		if($this->NBTentities !== null){
			$this->dirtyFlags |= self::DIRTY_FLAG_ENTITIES;
			$world->timings->syncChunkLoadEntitiesTimer->startTiming();
			foreach($this->NBTentities as $nbt){
				if($nbt instanceof CompoundTag){
					try{
						$entity = EntityFactory::createFromData($world, $nbt);
						if(!($entity instanceof Entity)){
							$world->getLogger()->warning("Chunk $this->x $this->z: Deleted unknown entity type " . $nbt->getString("id", $nbt->getString("identifier", "<unknown>", true), true));
							continue;
						}
					}catch(\Exception $t){ //TODO: this shouldn't be here
						$world->getLogger()->logException($t);
						continue;
					}
				}
			}

			$this->NBTentities = null;
			$world->timings->syncChunkLoadEntitiesTimer->stopTiming();
		}
		if($this->NBTtiles !== null){
			$this->dirtyFlags |= self::DIRTY_FLAG_TILES;
			$world->timings->syncChunkLoadTileEntitiesTimer->startTiming();
			foreach($this->NBTtiles as $nbt){
				if($nbt instanceof CompoundTag){
					if(($tile = TileFactory::createFromData($world, $nbt)) !== null){
						$world->addTile($tile);
					}else{
						$world->getLogger()->warning("Chunk $this->x $this->z: Deleted unknown tile entity type " . $nbt->getString("id", "<unknown>", true));
						continue;
					}
				}
			}

			$this->NBTtiles = null;
			$world->timings->syncChunkLoadTileEntitiesTimer->stopTiming();
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
	 * @param int[] $values
	 * @throws \InvalidArgumentException
	 */
	public function setHeightMapArray(array $values) : void{
		if(count($values) !== 256){
			throw new \InvalidArgumentException("Expected exactly 256 values");
		}
		$this->heightMap = \SplFixedArray::fromArray($values);
	}

	/**
	 * @return bool
	 */
	public function isDirty() : bool{
		return $this->dirtyFlags !== 0 or !empty($this->tiles) or !empty($this->getSavableEntities());
	}

	public function getDirtyFlag(int $flag) : bool{
		return ($this->dirtyFlags & $flag) !== 0;
	}

	/**
	 * @return int
	 */
	public function getDirtyFlags() : int{
		return $this->dirtyFlags;
	}

	public function setDirtyFlag(int $flag, bool $value) : void{
		if($value){
			$this->dirtyFlags |= $flag;
		}else{
			$this->dirtyFlags &= ~$flag;
		}
	}

	public function setDirty() : void{
		$this->dirtyFlags = ~0;
	}

	public function clearDirtyFlags() : void{
		$this->dirtyFlags = 0;
	}

	/**
	 * Returns the subchunk at the specified subchunk Y coordinate, or an empty, unmodifiable stub if it does not exist or the coordinate is out of range.
	 *
	 * @param int $y
	 *
	 * @return SubChunkInterface
	 */
	public function getSubChunk(int $y) : SubChunkInterface{
		if($y < 0 or $y >= $this->subChunks->getSize()){
			return EmptySubChunk::getInstance(); //TODO: drop this and throw an exception here
		}

		return $this->subChunks[$y];
	}

	/**
	 * Sets a subchunk in the chunk index
	 *
	 * @param int           $y
	 * @param SubChunk|null $subChunk
	 */
	public function setSubChunk(int $y, ?SubChunk $subChunk) : void{
		if($y < 0 or $y >= $this->subChunks->getSize()){
			throw new \InvalidArgumentException("Invalid subchunk Y coordinate $y");
		}

		$this->subChunks[$y] = $subChunk ?? new SubChunk(BlockLegacyIds::AIR << 4, []);
		$this->setDirtyFlag(self::DIRTY_FLAG_TERRAIN, true);
	}

	/**
	 * @return \SplFixedArray|SubChunk[]
	 */
	public function getSubChunks() : \SplFixedArray{
		return $this->subChunks;
	}

	/**
	 * Disposes of empty subchunks and frees data where possible
	 */
	public function collectGarbage() : void{
		foreach($this->subChunks as $y => $subChunk){
			$subChunk->collectGarbage();
		}
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
