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

use pocketmine\block\Block;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\tile\Tile;
use pocketmine\data\bedrock\BiomeIds;
use function array_fill;
use function array_map;

class Chunk{
	public const DIRTY_FLAG_TERRAIN = 1 << 0;
	public const DIRTY_FLAG_BIOMES = 1 << 3;

	public const MAX_SUBCHUNKS = 16;

	public const EDGE_LENGTH = SubChunk::EDGE_LENGTH;
	public const COORD_BIT_SIZE = SubChunk::COORD_BIT_SIZE;
	public const COORD_MASK = SubChunk::COORD_MASK;

	/** @var int */
	private $terrainDirtyFlags = 0;

	/** @var bool|null */
	protected $lightPopulated = false;
	/** @var bool */
	protected $terrainPopulated = false;

	/**
	 * @var \SplFixedArray|SubChunk[]
	 * @phpstan-var \SplFixedArray<SubChunk>
	 */
	protected $subChunks;

	/** @var Tile[] */
	protected $tiles = [];

	/** @var HeightArray */
	protected $heightMap;

	/** @var BiomeArray */
	protected $biomeIds;

	/**
	 * @param SubChunk[] $subChunks
	 */
	public function __construct(array $subChunks = [], ?BiomeArray $biomeIds = null, ?HeightArray $heightMap = null){
		$this->subChunks = new \SplFixedArray(Chunk::MAX_SUBCHUNKS);

		foreach($this->subChunks as $y => $null){
			$this->subChunks[$y] = $subChunks[$y] ?? new SubChunk(BlockLegacyIds::AIR << Block::INTERNAL_METADATA_BITS, []);
		}

		$val = ($this->subChunks->getSize() * SubChunk::EDGE_LENGTH);
		$this->heightMap = $heightMap ?? new HeightArray(array_fill(0, 256, $val));
		$this->biomeIds = $biomeIds ?? BiomeArray::fill(BiomeIds::OCEAN);
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
		return $this->getSubChunk($y >> SubChunk::COORD_BIT_SIZE)->getFullBlock($x, $y & SubChunk::COORD_MASK, $z);
	}

	/**
	 * Sets the blockstate at the given coordinate by internal ID.
	 */
	public function setFullBlock(int $x, int $y, int $z, int $block) : void{
		$this->getSubChunk($y >> SubChunk::COORD_BIT_SIZE)->setFullBlock($x, $y & SubChunk::COORD_MASK, $z, $block);
		$this->terrainDirtyFlags |= self::DIRTY_FLAG_TERRAIN;
	}

	/**
	 * Returns the Y coordinate of the highest non-air block at the specified X/Z chunk block coordinates
	 *
	 * @param int $x 0-15
	 * @param int $z 0-15
	 *
	 * @return int|null 0-255, or null if there are no blocks in the column
	 */
	public function getHighestBlockAt(int $x, int $z) : ?int{
		for($y = $this->subChunks->count() - 1; $y >= 0; --$y){
			$height = $this->getSubChunk($y)->getHighestBlockAt($x, $z);
			if($height !== null){
				return $height | ($y << SubChunk::COORD_BIT_SIZE);
			}
		}

		return null;
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
		$this->terrainDirtyFlags |= self::DIRTY_FLAG_BIOMES;
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
		$this->terrainDirtyFlags |= self::DIRTY_FLAG_TERRAIN;
	}

	public function addTile(Tile $tile) : void{
		if($tile->isClosed()){
			throw new \InvalidArgumentException("Attempted to add a garbage closed Tile to a chunk");
		}

		$pos = $tile->getPosition();
		if(isset($this->tiles[$index = Chunk::blockHash($pos->x, $pos->y, $pos->z)]) and $this->tiles[$index] !== $tile){
			throw new \InvalidArgumentException("Another tile is already at this location");
		}
		$this->tiles[$index] = $tile;
	}

	public function removeTile(Tile $tile) : void{
		$pos = $tile->getPosition();
		unset($this->tiles[Chunk::blockHash($pos->x, $pos->y, $pos->z)]);
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
		foreach($this->getTiles() as $tile){
			$tile->close();
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

	public function isTerrainDirty() : bool{
		return $this->terrainDirtyFlags !== 0;
	}

	public function getTerrainDirtyFlag(int $flag) : bool{
		return ($this->terrainDirtyFlags & $flag) !== 0;
	}

	public function getTerrainDirtyFlags() : int{
		return $this->terrainDirtyFlags;
	}

	public function setTerrainDirtyFlag(int $flag, bool $value) : void{
		if($value){
			$this->terrainDirtyFlags |= $flag;
		}else{
			$this->terrainDirtyFlags &= ~$flag;
		}
	}

	public function setTerrainDirty() : void{
		$this->terrainDirtyFlags = ~0;
	}

	public function clearTerrainDirtyFlags() : void{
		$this->terrainDirtyFlags = 0;
	}

	public function getSubChunk(int $y) : SubChunk{
		if($y < 0 || $y >= $this->subChunks->getSize()){
			throw new \InvalidArgumentException("Invalid subchunk Y coordinate $y");
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

		$this->subChunks[$y] = $subChunk ?? new SubChunk(BlockLegacyIds::AIR << Block::INTERNAL_METADATA_BITS, []);
		$this->setTerrainDirtyFlag(self::DIRTY_FLAG_TERRAIN, true);
	}

	/**
	 * @return SubChunk[]
	 * @phpstan-return array<int, SubChunk>
	 */
	public function getSubChunks() : array{
		return $this->subChunks->toArray();
	}

	/**
	 * Disposes of empty subchunks and frees data where possible
	 */
	public function collectGarbage() : void{
		foreach($this->subChunks as $y => $subChunk){
			$subChunk->collectGarbage();
		}
	}

	public function __clone(){
		//we don't bother cloning entities or tiles since it's impractical to do so (too many dependencies)
		$this->subChunks = \SplFixedArray::fromArray(array_map(function(SubChunk $subChunk) : SubChunk{
			return clone $subChunk;
		}, $this->subChunks->toArray()));
		$this->heightMap = clone $this->heightMap;
		$this->biomeIds = clone $this->biomeIds;
	}

	/**
	 * Hashes the given chunk block coordinates into a single integer.
	 *
	 * @param int $x 0-15
	 * @param int $y 0-255
	 * @param int $z 0-15
	 */
	public static function blockHash(int $x, int $y, int $z) : int{
		return ($y << (2 * SubChunk::COORD_BIT_SIZE)) |
			(($z & SubChunk::COORD_MASK) << SubChunk::COORD_BIT_SIZE) |
			($x & SubChunk::COORD_MASK);
	}
}
