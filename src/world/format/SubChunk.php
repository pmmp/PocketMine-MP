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

namespace pocketmine\world\format;

class SubChunk{
	public const COORD_BIT_SIZE = 4;
	public const COORD_MASK = ~(~0 << self::COORD_BIT_SIZE);
	public const EDGE_LENGTH = 1 << self::COORD_BIT_SIZE;

	/**
	 * SubChunk constructor.
	 */
	public function __construct(
		private int $emptyBlockId,
		private ?PalettedBlockArray $blockLayer0,
		private ?PalettedBlockArray $blockLayer1,
		private PalettedBlockArray $biomes,
		private ?LightArray $skyLight = null,
		private ?LightArray $blockLight = null
	){}

	/**
	 * Returns whether this subchunk contains any non-air blocks.
	 * This function will do a slow check, usually by garbage collecting first.
	 * This is typically useful for disk saving.
	 */
	public function isEmptyAuthoritative() : bool{
		$this->collectGarbage();
		return $this->isEmptyFast();
	}

	/**
	 * Returns a non-authoritative bool to indicate whether the chunk contains any blocks.
	 * This may report non-empty erroneously if the chunk has been modified and not garbage-collected.
	 */
	public function isEmptyFast() : bool{
		return $this->blockLayer0 === null && $this->blockLayer1 === null;
	}

	/**
	 * Returns the block used as the default. This is assumed to refer to air.
	 * If all the blocks in a subchunk layer are equal to this block, the layer is assumed to be empty.
	 */
	public function getEmptyBlockId() : int{ return $this->emptyBlockId; }

	public function getBlockStateId(int $x, int $y, int $z) : int{
		return $this->blockLayer0?->get($x, $y, $z) ?? $this->emptyBlockId;
	}

	public function setBlockStateId(int $x, int $y, int $z, int $block) : void{
		if($this->blockLayer0 === null){
			$this->blockLayer0 = new PalettedBlockArray($this->emptyBlockId);
		}
		$this->blockLayer0->set($x, $y, $z, $block);
	}

	public function getBlockLayer0() : ?PalettedBlockArray{
		return $this->blockLayer0;
	}

	public function getBlockLayer1() : ?PalettedBlockArray{
		return $this->blockLayer1;
	}

	/**
	 * @return PalettedBlockArray[]
	 * @phpstan-return array{}|array{PalettedBlockArray}|array{PalettedBlockArray, PalettedBlockArray}
	 */
	public function getBlockLayers() : array{
		$layers = [];
		if($this->blockLayer0 !== null){
			$layers[] = $this->blockLayer0;
		}
		if($this->blockLayer1 !== null){
			$layers[] = $this->blockLayer1;
		}
		return $layers;
	}

	public function getHighestBlockAt(int $x, int $z) : ?int{
		if($this->blockLayer0 === null){
			return null;
		}
		for($y = self::EDGE_LENGTH - 1; $y >= 0; --$y){
			if($this->blockLayer0->get($x, $y, $z) !== $this->emptyBlockId){
				return $y;
			}
		}

		return null; //highest block not in this subchunk
	}

	public function getBiomeArray() : PalettedBlockArray{ return $this->biomes; }

	public function getBlockSkyLightArray() : LightArray{
		return $this->skyLight ??= LightArray::fill(0);
	}

	public function setBlockSkyLightArray(LightArray $data) : void{
		$this->skyLight = $data;
	}

	public function getBlockLightArray() : LightArray{
		return $this->blockLight ??= LightArray::fill(0);
	}

	public function setBlockLightArray(LightArray $data) : void{
		$this->blockLight = $data;
	}

	/**
	 * @return mixed[]
	 */
	public function __debugInfo() : array{
		return [];
	}

	private static function gcBlockPalette(?PalettedBlockArray $layer, int $emptyBlockId) : ?PalettedBlockArray{
		if($layer === null){
			return null;
		}
		$layer->collectGarbage();
		return $layer->getBitsPerBlock() === 0 && $layer->get(0, 0, 0) === $emptyBlockId ? null : $layer;
	}

	public function collectGarbage() : void{
		$this->blockLayer0 = self::gcBlockPalette($this->blockLayer0, $this->emptyBlockId);
		$this->blockLayer1 = self::gcBlockPalette($this->blockLayer1, $this->emptyBlockId);
		if($this->blockLayer0 === null && $this->blockLayer1 !== null){
			$this->blockLayer0 = $this->blockLayer1;
			$this->blockLayer1 = null;
		}
		$this->biomes->collectGarbage();

		if($this->skyLight !== null && $this->skyLight->isUniform(0)){
			$this->skyLight = null;
		}
		if($this->blockLight !== null && $this->blockLight->isUniform(0)){
			$this->blockLight = null;
		}
	}

	public function __clone(){
		$this->blockLayer0 = $this->blockLayer0 !== null ? clone $this->blockLayer0 : null;
		$this->blockLayer1 = $this->blockLayer1 !== null ? clone $this->blockLayer1 : null;
		$this->biomes = clone $this->biomes;

		if($this->skyLight !== null){
			$this->skyLight = clone $this->skyLight;
		}
		if($this->blockLight !== null){
			$this->blockLight = clone $this->blockLight;
		}
	}
}
