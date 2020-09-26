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

use pocketmine\block\BlockLegacyIds;
use pocketmine\block\tile\Tile;
use pocketmine\block\tile\TileFactory;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\player\Player;
use pocketmine\world\World;
use function array_fill;
use function array_filter;
use function array_map;
use function count;
use function str_repeat;

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

	/** @var bool|null */
	protected $lightPopulated = false;
	/** @var bool */
	protected $terrainGenerated = false;
	/** @var bool */
	protected $terrainPopulated = false;

	/**
	 * @var \SplFixedArray|SubChunk[]
	 * @phpstan-var \SplFixedArray<SubChunk>
	 */
	protected $subChunks;

	/** @var Tile[] */
	protected $tiles = [];

	/** @var Entity[] */
	protected $entities = [];

	/** @var HeightArray */
	protected $heightMap;

	/** @var BiomeArray */
	protected $biomeIds;

	/** @var CompoundTag[]|null */
	protected $NBTtiles;

	/** @var CompoundTag[]|null */
	protected $NBTentities;

	/**
	 * @param SubChunk[]    $subChunks
	 * @param CompoundTag[] $entities
	 * @param CompoundTag[] $tiles
	 */
	public function __construct(int $chunkX, int $chunkZ, array $subChunks = [], ?array $entities = null, ?array $tiles = null, ?BiomeArray $biomeIds = null, ?HeightArray $heightMap = null){
		$this->x = $chunkX;
		$this->z = $chunkZ;

		$this->subChunks = new \SplFixedArray(Chunk::MAX_SUBCHUNKS);

		foreach($this->subChunks as $y => $null){
			$this->subChunks[$y] = $subChunks[$y] ?? new SubChunk(BlockLegacyIds::AIR << 4, []);
		}

		$val = ($this->subChunks->getSize() * 16);
		$this->heightMap = $heightMap ?? new HeightArray(array_fill(0, 256, $val));
		$this->biomeIds = $biomeIds ?? new BiomeArray(str_repeat("\x00", 256));

		$this->NBTtiles = $tiles;
		$this->NBTentities = $entities;
	}

	public function getX() : int{
		return $this->x;
	}

	public function getZ() : int{
		return $this->z;
	}

	public function setX(int $x) : void{
		$this->x = $x;
	}

	public function setZ(int $z) : void{
		$this->z = $z;
	}

	/**
	 * Returns the chunk height in count of subchunks.
	 */
	public function getHeight() : int{
		return $this->subChunks->getSize();
	}

	/**
	 * Returns the internal ID of the blockstate at the given coordinates.
	 *
	 * @param int $x 0-15
	 * @param int $y 0-255
	 * @param int $z 0-15
	 *
	 * @return int bitmap, (id << 4) | meta
	 */
	public function getFullBlock(int $x, int $y, int $z) : int{
		return $this->getSubChunk($y >> 4)->getFullBlock($x, $y & 0x0f, $z);
	}

	/**
	 * Sets the blockstate at the given coordinate by internal ID.
	 */
	public function setFullBlock(int $x, int $y, int $z, int $block) : void{
		$this->getWritableSubChunk($y >> 4)->setFullBlock($x, $y & 0xf, $z, $block);
		$this->dirtyFlags |= self::DIRTY_FLAG_TERRAIN;
	}

	/**
	 * Returns the sky light level at the specified chunk block coordinates
	 *
	 * @param int $x 0-15
	 * @param int $y 0-255
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
	 * @param int $y 0-255
	 * @param int $z 0-15
	 * @param int $level 0-15
	 */
	public function setBlockSkyLight(int $x, int $y, int $z, int $level) : void{
		$this->getWritableSubChunk($y >> 4)->getBlockSkyLightArray()->set($x & 0xf, $y & 0x0f, $z & 0xf, $level);
	}

	public function setAllBlockSkyLight(int $level) : void{
		for($y = $this->subChunks->count() - 1; $y >= 0; --$y){
			$this->getWritableSubChunk($y)->setBlockSkyLightArray(LightArray::fill($level));
		}
	}

	/**
	 * Returns the block light level at the specified chunk block coordinates
	 *
	 * @param int $x 0-15
	 * @param int $y 0-255
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
	 * @param int $y 0-255
	 * @param int $z 0-15
	 * @param int $level 0-15
	 */
	public function setBlockLight(int $x, int $y, int $z, int $level) : void{
		$this->getWritableSubChunk($y >> 4)->getBlockLightArray()->set($x & 0xf, $y & 0x0f, $z & 0xf, $level);
	}

	public function setAllBlockLight(int $level) : void{
		for($y = $this->subChunks->count() - 1; $y >= 0; --$y){
			$this->getWritableSubChunk($y)->setBlockLightArray(LightArray::fill($level));
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
	 */
	public function getHeightMap(int $x, int $z) : int{
		return $this->heightMap->get($x, $z);
	}

	/**
	 * Returns the heightmap value at the specified X/Z chunk block coordinates
	 *
	 * @param int $x 0-15
	 * @param int $z 0-15
	 */
	public function setHeightMap(int $x, int $z, int $value) : void{
		$this->heightMap->set($x, $z, $value);
	}

	/**
	 * Recalculates the heightmap for the whole chunk.
	 *
	 * @param \SplFixedArray|bool[] $directSkyLightBlockers
	 * @phpstan-param \SplFixedArray<bool> $directSkyLightBlockers
	 */
	public function recalculateHeightMap(\SplFixedArray $directSkyLightBlockers) : void{
		$maxSubChunkY = $this->subChunks->count() - 1;
		for(; $maxSubChunkY >= 0; $maxSubChunkY--){
			if(!$this->getSubChunk($maxSubChunkY)->isEmptyFast()){
				break;
			}
		}
		if($maxSubChunkY === -1){ //whole column is definitely empty
			$this->setHeightMapArray(array_fill(0, 256, 0));
			return;
		}

		for($z = 0; $z < 16; ++$z){
			for($x = 0; $x < 16; ++$x){
				$y = null;
				for($subChunkY = $maxSubChunkY; $subChunkY >= 0; $subChunkY--){
					$subHighestBlockY = $this->getSubChunk($subChunkY)->getHighestBlockAt($x, $z);
					if($subHighestBlockY !== -1){
						$y = ($subChunkY * 16) + $subHighestBlockY;
						break;
					}
				}

				if($y === null){ //no blocks in the column
					$this->setHeightMap($x, $z, 0);
				}else{
					for(; $y >= 0; --$y){
						if($directSkyLightBlockers[$this->getFullBlock($x, $y, $z)]){
							$this->setHeightMap($x, $z, $y + 1);
							break;
						}
					}
				}
			}
		}
	}

	/**
	 * Recalculates the heightmap for the block column at the specified X/Z chunk coordinates
	 *
	 * @param int $x 0-15
	 * @param int $z 0-15
	 * @param \SplFixedArray|bool[] $directSkyLightBlockers
	 * @phpstan-param \SplFixedArray<bool> $directSkyLightBlockers
	 *
	 * @return int New calculated heightmap value (0-256 inclusive)
	 */
	public function recalculateHeightMapColumn(int $x, int $z, \SplFixedArray $directSkyLightBlockers) : int{
		$y = $this->getHighestBlockAt($x, $z);
		for(; $y >= 0; --$y){
			if($directSkyLightBlockers[$this->getFullBlock($x, $y, $z)]){
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
	 * @param \SplFixedArray|int[] $lightFilters
	 * @phpstan-param \SplFixedArray<int> $lightFilters
	 *
	 * TODO: fast adjacent light spread
	 */
	public function populateSkyLight(\SplFixedArray $lightFilters) : void{
		$highestHeightMap = max($this->heightMap->getValues());
		$lowestFullyLitSubChunk = ($highestHeightMap >> 4) + (($highestHeightMap & 0xf) !== 0 ? 1 : 0);
		for($y = 0; $y < $lowestFullyLitSubChunk; $y++){
			$this->getWritableSubChunk($y)->setBlockSkyLightArray(LightArray::fill(0));
		}
		for($y = $lowestFullyLitSubChunk, $yMax = $this->subChunks->count(); $y < $yMax; $y++){
			$this->getWritableSubChunk($y)->setBlockSkyLightArray(LightArray::fill(15));
		}

		for($x = 0; $x < 16; ++$x){
			for($z = 0; $z < 16; ++$z){
				$y = ($lowestFullyLitSubChunk * 16) - 1;
				$heightMap = $this->getHeightMap($x, $z);

				for(; $y >= $heightMap; --$y){
					$this->setBlockSkyLight($x, $y, $z, 15);
				}

				$light = 15;
				for(; $y >= 0; --$y){
					$light -= $lightFilters[$this->getFullBlock($x, $y, $z)];
					if($light <= 0){
						break;
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
		return $this->biomeIds->get($x, $z);
	}

	/**
	 * Sets the biome ID at the specified X/Z chunk block coordinates
	 *
	 * @param int $x 0-15
	 * @param int $z 0-15
	 * @param int $biomeId 0-255
	 */
	public function setBiomeId(int $x, int $z, int $biomeId) : void{
		$this->biomeIds->set($x, $z, $biomeId);
		$this->dirtyFlags |= self::DIRTY_FLAG_BIOMES;
	}

	public function isLightPopulated() : ?bool{
		return $this->lightPopulated;
	}

	public function setLightPopulated(?bool $value = true) : void{
		$this->lightPopulated = $value;
	}

	public function isPopulated() : bool{
		return $this->terrainPopulated;
	}

	public function setPopulated(bool $value = true) : void{
		$this->terrainPopulated = $value;
		$this->dirtyFlags |= self::DIRTY_FLAG_TERRAIN;
	}

	public function isGenerated() : bool{
		return $this->terrainGenerated;
	}

	public function setGenerated(bool $value = true) : void{
		$this->terrainGenerated = $value;
		$this->dirtyFlags |= self::DIRTY_FLAG_TERRAIN;
	}

	public function addEntity(Entity $entity) : void{
		if($entity->isClosed()){
			throw new \InvalidArgumentException("Attempted to add a garbage closed Entity to a chunk");
		}
		$this->entities[$entity->getId()] = $entity;
		if(!($entity instanceof Player)){
			$this->dirtyFlags |= self::DIRTY_FLAG_ENTITIES;
		}
	}

	public function removeEntity(Entity $entity) : void{
		unset($this->entities[$entity->getId()]);
		if(!($entity instanceof Player)){
			$this->dirtyFlags |= self::DIRTY_FLAG_ENTITIES;
		}
	}

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
	 * @param int $y 0-255
	 * @param int $z 0-15
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
	 */
	public function initChunk(World $world) : void{
		if($this->NBTentities !== null){
			$this->dirtyFlags |= self::DIRTY_FLAG_ENTITIES;
			$world->timings->syncChunkLoadEntitiesTimer->startTiming();
			$entityFactory = EntityFactory::getInstance();
			foreach($this->NBTentities as $nbt){
				try{
					$entity = $entityFactory->createFromData($world, $nbt);
					if(!($entity instanceof Entity)){
						$saveIdTag = $nbt->getTag("id") ?? $nbt->getTag("identifier");
						$saveId = "<unknown>";
						if($saveIdTag instanceof StringTag){
							$saveId = $saveIdTag->getValue();
						}elseif($saveIdTag instanceof IntTag){ //legacy MCPE format
							$saveId = "legacy(" . $saveIdTag->getValue() . ")";
						}
						$world->getLogger()->warning("Chunk $this->x $this->z: Deleted unknown entity type $saveId");
						continue;
					}
				}catch(\Exception $t){ //TODO: this shouldn't be here
					$world->getLogger()->logException($t);
					continue;
				}
			}

			$this->NBTentities = null;
			$world->timings->syncChunkLoadEntitiesTimer->stopTiming();
		}
		if($this->NBTtiles !== null){
			$this->dirtyFlags |= self::DIRTY_FLAG_TILES;
			$world->timings->syncChunkLoadTileEntitiesTimer->startTiming();
			$tileFactory = TileFactory::getInstance();
			foreach($this->NBTtiles as $nbt){
				if(($tile = $tileFactory->createFromData($world, $nbt)) !== null){
					$world->addTile($tile);
				}else{
					$world->getLogger()->warning("Chunk $this->x $this->z: Deleted unknown tile entity type " . $nbt->getString("id", "<unknown>"));
					continue;
				}
			}

			$this->NBTtiles = null;
			$world->timings->syncChunkLoadTileEntitiesTimer->stopTiming();
		}
	}

	public function getBiomeIdArray() : string{
		return $this->biomeIds->getData();
	}

	/**
	 * @return int[]
	 */
	public function getHeightMapArray() : array{
		return $this->heightMap->getValues();
	}

	/**
	 * @param int[] $values
	 */
	public function setHeightMapArray(array $values) : void{
		$this->heightMap = new HeightArray($values);
	}

	public function isDirty() : bool{
		return $this->dirtyFlags !== 0 or count($this->tiles) > 0 or count($this->getSavableEntities()) > 0;
	}

	public function getDirtyFlag(int $flag) : bool{
		return ($this->dirtyFlags & $flag) !== 0;
	}

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
	 */
	public function getSubChunk(int $y) : SubChunkInterface{
		if($y < 0 or $y >= $this->subChunks->getSize()){
			return EmptySubChunk::getInstance(); //TODO: drop this and throw an exception here
		}

		return $this->subChunks[$y];
	}

	public function getWritableSubChunk(int $y) : SubChunk{
		if($y < 0 || $y >= $this->subChunks->getSize()){
			throw new \InvalidArgumentException("Cannot get subchunk $y for writing");
		}
		return $this->subChunks[$y];
	}

	/**
	 * Sets a subchunk in the chunk index
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
	 * @phpstan-return \SplFixedArray<SubChunk>
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
	 * @param int $y 0-255
	 * @param int $z 0-15
	 */
	public static function blockHash(int $x, int $y, int $z) : int{
		return ($y << 8) | (($z & 0x0f) << 4) | ($x & 0x0f);
	}
}
