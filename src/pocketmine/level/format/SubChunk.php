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

class SubChunk implements SubChunkInterface{

	protected $ids;
	protected $data;
	protected $blockLight;
	protected $skyLight;

	private static function assignData(&$target, string $data, int $length, string $value = "\x00"){
		if(strlen($data) !== $length){
			assert($data === "", "Invalid non-zero length given, expected $length, got " . strlen($data));
			$target = str_repeat($value, $length);
		}else{
			$target = $data;
		}
	}

	public function __construct(string $ids = "", string $data = "", string $skyLight = "", string $blockLight = ""){
		self::assignData($this->ids, $ids, 4096);
		self::assignData($this->data, $data, 2048);
		self::assignData($this->skyLight, $skyLight, 2048, "\xff");
		self::assignData($this->blockLight, $blockLight, 2048);
	}

	public function isEmpty(bool $checkLight = true) : bool{
		return (
			substr_count($this->ids, "\x00") === 4096 and
			(!$checkLight or (
				substr_count($this->skyLight, "\xff") === 2048 and
				substr_count($this->blockLight, "\x00") === 2048
			))
		);
	}

	public function getBlockId(int $x, int $y, int $z) : int{
		return ord($this->ids{($x << 8) | ($z << 4) | $y});
	}

	public function setBlockId(int $x, int $y, int $z, int $id) : bool{
		$this->ids{($x << 8) | ($z << 4) | $y} = chr($id);
		return true;
	}

	public function getBlockData(int $x, int $y, int $z) : int{
		return (ord($this->data{($x << 7) | ($z << 3) | ($y >> 1)}) >> (($y & 1) << 2)) & 0xf;
	}

	public function setBlockData(int $x, int $y, int $z, int $data) : bool{
		$i = ($x << 7) | ($z << 3) | ($y >> 1);

		$shift = ($y & 1) << 2;
		$byte = ord($this->data{$i});
		$this->data{$i} = chr(($byte & ~(0xf << $shift)) | (($data & 0xf) << $shift));

		return true;
	}

	public function getFullBlock(int $x, int $y, int $z) : int{
		$i = ($x << 8) | ($z << 4) | $y;
		return (ord($this->ids{$i}) << 4) | ((ord($this->data{$i >> 1}) >> (($y & 1) << 2)) & 0xf);
	}

	public function setBlock(int $x, int $y, int $z, ?int $id = null, ?int $data = null) : bool{
		$i = ($x << 8) | ($z << 4) | $y;
		$changed = false;
		if($id !== null){
			$block = chr($id);
			if($this->ids{$i} !== $block){
				$this->ids{$i} = $block;
				$changed = true;
			}
		}

		if($data !== null){
			$i >>= 1;

			$shift = ($y & 1) << 2;
			$byte = ord($this->data{$i});
			$this->data{$i} = chr(($byte & ~(0xf << $shift)) | (($data & 0xf) << $shift));

			if($this->data{$i} !== $byte){
				$changed = true;
			}
		}

		return $changed;
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
		$low = ($x << 8) | ($z << 4);
		$i = $low | 0x0f;
		for(; $i >= $low; --$i){
			if($this->ids{$i} !== "\x00"){
				return $i & 0x0f;
			}
		}

		return -1; //highest block not in this subchunk
	}

	public function getBlockIdColumn(int $x, int $z) : string{
		return substr($this->ids, ($x << 8) | ($z << 4), 16);
	}

	public function getBlockDataColumn(int $x, int $z) : string{
		return substr($this->data, ($x << 7) | ($z << 3), 8);
	}

	public function getBlockLightColumn(int $x, int $z) : string{
		return substr($this->blockLight, ($x << 7) | ($z << 3), 8);
	}

	public function getBlockSkyLightColumn(int $x, int $z) : string{
		return substr($this->skyLight, ($x << 7) | ($z << 3), 8);
	}

	public function getBlockIdArray() : string{
		assert(strlen($this->ids) === 4096, "Wrong length of ID array, expecting 4096 bytes, got " . strlen($this->ids));
		return $this->ids;
	}

	public function getBlockDataArray() : string{
		assert(strlen($this->data) === 2048, "Wrong length of data array, expecting 2048 bytes, got " . strlen($this->data));
		return $this->data;
	}

	public function getBlockSkyLightArray() : string{
		assert(strlen($this->skyLight) === 2048, "Wrong length of skylight array, expecting 2048 bytes, got " . strlen($this->skyLight));
		return $this->skyLight;
	}

	public function setBlockSkyLightArray(string $data){
		assert(strlen($data) === 2048, "Wrong length of skylight array, expecting 2048 bytes, got " . strlen($data));
		$this->skyLight = $data;
	}

	public function getBlockLightArray() : string{
		assert(strlen($this->blockLight) === 2048, "Wrong length of light array, expecting 2048 bytes, got " . strlen($this->blockLight));
		return $this->blockLight;
	}

	public function setBlockLightArray(string $data){
		assert(strlen($data) === 2048, "Wrong length of light array, expecting 2048 bytes, got " . strlen($data));
		$this->blockLight = $data;
	}

	public function networkSerialize() : string{
		return "\x00" . $this->ids . $this->data;
	}

	public function fastSerialize() : string{
		return
			$this->ids .
			$this->data .
			$this->skyLight .
			$this->blockLight;
	}

	public static function fastDeserialize(string $data) : SubChunk{
		return new SubChunk(
			substr($data,    0, 4096), //ids
			substr($data, 4096, 2048), //data
			substr($data, 6144, 2048), //sky light
			substr($data, 8192, 2048)  //block light
		);
	}

	public function __debugInfo(){
		return [];
	}
}
