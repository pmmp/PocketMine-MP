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

namespace pocketmine\level\format;

use pocketmine\block\BlockIds;
use function array_values;
use function assert;
use function chr;
use function define;
use function defined;
use function ord;
use function str_repeat;
use function strlen;
use function substr_count;

if(!defined(__NAMESPACE__ . '\ZERO_NIBBLE_ARRAY')){
	define(__NAMESPACE__ . '\ZERO_NIBBLE_ARRAY', str_repeat("\x00", 2048));
}

class SubChunk implements SubChunkInterface{
	/** @var PalettedBlockArray[] */
	private $blockLayers;

	/** @var string */
	protected $blockLight;
	/** @var string */
	protected $skyLight;

	private static function assignData(&$target, string $data, int $length, string $value = "\x00") : void{
		if(strlen($data) !== $length){
			assert($data === "", "Invalid non-zero length given, expected $length, got " . strlen($data));
			$target = str_repeat($value, $length);
		}else{
			$target = $data;
		}
	}

	/**
	 * SubChunk constructor.
	 *
	 * @param PalettedBlockArray[] $blocks
	 * @param string               $skyLight
	 * @param string               $blockLight
	 */
	public function __construct(array $blocks, string $skyLight = "", string $blockLight = ""){
		$this->blockLayers = $blocks;

		self::assignData($this->skyLight, $skyLight, 2048, "\xff");
		self::assignData($this->blockLight, $blockLight, 2048);
	}

	public function isEmpty(bool $checkLight = true) : bool{
		foreach($this->blockLayers as $layer){
			$palette = $layer->getPalette();
			foreach($palette as $p){
				if(($p >> 4) !== BlockIds::AIR){
					return false;
				}
			}
		}
		return
			(!$checkLight or (
				substr_count($this->skyLight, "\xff") === 2048 and
				$this->blockLight === ZERO_NIBBLE_ARRAY
			)
		);
	}

	public function getFullBlock(int $x, int $y, int $z) : int{
		if(empty($this->blockLayers)){
			return BlockIds::AIR << 4;
		}
		return $this->blockLayers[0]->get($x, $y, $z);
	}

	public function setFullBlock(int $x, int $y, int $z, int $block) : void{
		if(empty($this->blockLayers)){
			$this->blockLayers[] = new PalettedBlockArray(BlockIds::AIR << 4);
		}
		$this->blockLayers[0]->set($x, $y, $z, $block);
	}

	/**
	 * @return PalettedBlockArray[]
	 */
	public function getBlockLayers() : array{
		return $this->blockLayers;
	}

	public function getBlockLight(int $x, int $y, int $z) : int{
		return (ord($this->blockLight{($x << 7) | ($z << 3) | ($y >> 1)}) >> (($y & 1) << 2)) & 0xf;
	}

	public function setBlockLight(int $x, int $y, int $z, int $level) : bool{
		$i = ($x << 7) | ($z << 3) | ($y >> 1);

		$shift = ($y & 1) << 2;
		$byte = ord($this->blockLight{$i});
		$this->blockLight{$i} = chr(($byte & ~(0xf << $shift)) | (($level & 0xf) << $shift));

		return true;
	}

	public function getBlockSkyLight(int $x, int $y, int $z) : int{
		return (ord($this->skyLight{($x << 7) | ($z << 3) | ($y >> 1)}) >> (($y & 1) << 2)) & 0xf;
	}

	public function setBlockSkyLight(int $x, int $y, int $z, int $level) : bool{
		$i = ($x << 7) | ($z << 3) | ($y >> 1);

		$shift = ($y & 1) << 2;
		$byte = ord($this->skyLight{$i});
		$this->skyLight{$i} = chr(($byte & ~(0xf << $shift)) | (($level & 0xf) << $shift));

		return true;
	}

	public function getHighestBlockAt(int $x, int $z) : int{
		if(empty($this->blockLayers)){
			return -1;
		}
		for($y = 15; $y >= 0; --$y){
			if(($this->blockLayers[0]->get($x, $y, $z) >> 4) !== BlockIds::AIR){
				return $y;
			}
		}

		return -1; //highest block not in this subchunk
	}

	public function getBlockSkyLightArray() : string{
		assert(strlen($this->skyLight) === 2048, "Wrong length of skylight array, expecting 2048 bytes, got " . strlen($this->skyLight));
		return $this->skyLight;
	}

	public function setBlockSkyLightArray(string $data) : void{
		assert(strlen($data) === 2048, "Wrong length of skylight array, expecting 2048 bytes, got " . strlen($data));
		$this->skyLight = $data;
	}

	public function getBlockLightArray() : string{
		assert(strlen($this->blockLight) === 2048, "Wrong length of light array, expecting 2048 bytes, got " . strlen($this->blockLight));
		return $this->blockLight;
	}

	public function setBlockLightArray(string $data) : void{
		assert(strlen($data) === 2048, "Wrong length of light array, expecting 2048 bytes, got " . strlen($data));
		$this->blockLight = $data;
	}

	public function __debugInfo(){
		return [];
	}

	public function collectGarbage() : void{
		foreach($this->blockLayers as $k => $layer){
			$layer->collectGarbage();

			foreach($layer->getPalette() as $p){
				if(($p >> 4) !== BlockIds::AIR){
					continue 2;
				}
			}
			unset($this->blockLayers[$k]);
		}
		$this->blockLayers = array_values($this->blockLayers);

		/*
		 * This strange looking code is designed to exploit PHP's copy-on-write behaviour. Assigning will copy a
		 * reference to the const instead of duplicating the whole string. The string will only be duplicated when
		 * modified, which is perfect for this purpose.
		 */
		if($this->skyLight === ZERO_NIBBLE_ARRAY){
			$this->skyLight = ZERO_NIBBLE_ARRAY;
		}
		if($this->blockLight === ZERO_NIBBLE_ARRAY){
			$this->blockLight = ZERO_NIBBLE_ARRAY;
		}
	}
}
