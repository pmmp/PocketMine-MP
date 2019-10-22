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

use function array_values;

class SubChunk implements SubChunkInterface{
	/** @var int */
	private $defaultBlock;
	/** @var PalettedBlockArray[] */
	private $blockLayers;

	/** @var LightArray */
	private $blockLight;
	/** @var LightArray */
	private $skyLight;

	/**
	 * SubChunk constructor.
	 *
	 * @param int                  $default
	 * @param PalettedBlockArray[] $blocks
	 * @param LightArray|null      $skyLight
	 * @param LightArray|null      $blockLight
	 */
	public function __construct(int $default, array $blocks, ?LightArray $skyLight = null, ?LightArray $blockLight = null){
		$this->defaultBlock = $default;
		$this->blockLayers = $blocks;

		$this->skyLight = $skyLight ?? new LightArray(LightArray::FIFTEEN);
		$this->blockLight = $blockLight ?? new LightArray(LightArray::ZERO);
	}

	public function isEmptyAuthoritative() : bool{
		$this->collectGarbage();
		return $this->isEmptyFast();
	}

	public function isEmptyFast() : bool{
		return empty($this->blockLayers);
	}

	public function getFullBlock(int $x, int $y, int $z) : int{
		if(empty($this->blockLayers)){
			return $this->defaultBlock;
		}
		return $this->blockLayers[0]->get($x, $y, $z);
	}

	public function setFullBlock(int $x, int $y, int $z, int $block) : void{
		if(empty($this->blockLayers)){
			$this->blockLayers[] = new PalettedBlockArray($this->defaultBlock);
		}
		$this->blockLayers[0]->set($x, $y, $z, $block);
	}

	/**
	 * @return PalettedBlockArray[]
	 */
	public function getBlockLayers() : array{
		return $this->blockLayers;
	}

	public function getHighestBlockAt(int $x, int $z) : int{
		if(empty($this->blockLayers)){
			return -1;
		}
		for($y = 15; $y >= 0; --$y){
			if($this->blockLayers[0]->get($x, $y, $z) !== $this->defaultBlock){
				return $y;
			}
		}

		return -1; //highest block not in this subchunk
	}

	public function getBlockSkyLightArray() : LightArray{
		return $this->skyLight;
	}

	public function setBlockSkyLightArray(LightArray $data) : void{
		$this->skyLight = $data;
	}

	public function getBlockLightArray() : LightArray{
		return $this->blockLight;
	}

	public function setBlockLightArray(LightArray $data) : void{
		$this->blockLight = $data;
	}

	public function __debugInfo(){
		return [];
	}

	public function collectGarbage() : void{
		foreach($this->blockLayers as $k => $layer){
			$layer->collectGarbage();

			foreach($layer->getPalette() as $p){
				if($p !== $this->defaultBlock){
					continue 2;
				}
			}
			unset($this->blockLayers[$k]);
		}
		$this->blockLayers = array_values($this->blockLayers);

		$this->skyLight->collectGarbage();
		$this->blockLight->collectGarbage();
	}
}
