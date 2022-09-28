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

use function array_map;
use function array_values;
use function count;

class SubChunk{
	public const COORD_BIT_SIZE = 4;
	public const COORD_MASK = ~(~0 << self::COORD_BIT_SIZE);
	public const EDGE_LENGTH = 1 << self::COORD_BIT_SIZE;

	/** @var PalettedBlockArray[] */
	private array $blockLayers;

	/**
	 * SubChunk constructor.
	 *
	 * @param PalettedBlockArray[] $blocks
	 */
	public function __construct(
		private int $emptyBlockId,
		array $blocks, //TODO: promote this once we can break BC again (needs a name change)
		private ?LightArray $skyLight = null,
		private ?LightArray $blockLight = null
	){
		$this->blockLayers = $blocks;
	}

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
		return count($this->blockLayers) === 0;
	}

	/**
	 * Returns the block used as the default. This is assumed to refer to air.
	 * If all the blocks in a subchunk layer are equal to this block, the layer is assumed to be empty.
	 */
	public function getEmptyBlockId() : int{ return $this->emptyBlockId; }

	public function getFullBlock(int $x, int $y, int $z) : int{
		if(count($this->blockLayers) === 0){
			return $this->emptyBlockId;
		}
		return $this->blockLayers[0]->get($x, $y, $z);
	}

	public function setFullBlock(int $x, int $y, int $z, int $block) : void{
		if(count($this->blockLayers) === 0){
			$this->blockLayers[] = new PalettedBlockArray($this->emptyBlockId);
		}
		$this->blockLayers[0]->set($x, $y, $z, $block);
	}

	/**
	 * @return PalettedBlockArray[]
	 */
	public function getBlockLayers() : array{
		return $this->blockLayers;
	}

	public function getHighestBlockAt(int $x, int $z) : ?int{
		if(count($this->blockLayers) === 0){
			return null;
		}
		for($y = self::EDGE_LENGTH - 1; $y >= 0; --$y){
			if($this->blockLayers[0]->get($x, $y, $z) !== $this->emptyBlockId){
				return $y;
			}
		}

		return null; //highest block not in this subchunk
	}

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

	public function collectGarbage() : void{
		foreach($this->blockLayers as $k => $layer){
			$layer->collectGarbage();

			foreach($layer->getPalette() as $p){
				if($p !== $this->emptyBlockId){
					continue 2;
				}
			}
			unset($this->blockLayers[$k]);
		}
		$this->blockLayers = array_values($this->blockLayers);

		if($this->skyLight !== null && $this->skyLight->isUniform(0)){
			$this->skyLight = null;
		}
		if($this->blockLight !== null && $this->blockLight->isUniform(0)){
			$this->blockLight = null;
		}
	}

	public function __clone(){
		$this->blockLayers = array_map(function(PalettedBlockArray $array) : PalettedBlockArray{
			return clone $array;
		}, $this->blockLayers);

		if($this->skyLight !== null){
			$this->skyLight = clone $this->skyLight;
		}
		if($this->blockLight !== null){
			$this->blockLight = clone $this->blockLight;
		}
	}
}
