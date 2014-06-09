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

namespace pocketmine\level\format;

use pocketmine\utils\Binary;

class SimpleChunk{

	const FLAG_GENERATED = 1;
	const FLAG_POPULATED = 2;

	protected static $HEIGHT = 8;

	/** @var string[] */
	protected $ids;
	/** @var string[] */
	protected $meta;

	protected $x;
	protected $z;

	protected $flags = 0;

	/**
	 * @param int      $chunkX
	 * @param int      $chunkZ
	 * @param int      $flags
	 * @param string[] $ids
	 * @param string[] $meta
	 */
	public function __construct($chunkX, $chunkZ, $flags = 0, array $ids = [], array $meta = []){
		$this->x = $chunkX;
		$this->z = $chunkZ;
		$this->flags = $flags;
		for($y = 0; $y < self::$HEIGHT; ++$y){
			$this->ids[$y] = isset($ids[$y]) ? $ids[$y] : str_repeat("\x00", 4096);
			$this->meta[$y] = isset($meta[$y]) ? $meta[$y] : str_repeat("\x00", 2048);
		}
	}

	/**
	 * @return int
	 */
	public function getX(){
		return $this->x;
	}

	/**
	 * @return int
	 */
	public function getZ(){
		return $this->z;
	}

	/**
	 * @param int $x
	 */
	public function setX($x){
		$this->x = $x;
	}

	/**
	 * @param int $z
	 */
	public function setZ($z){
		$this->z = $z;
	}

	/**
	 * @return bool
	 */
	public function isGenerated(){
		return ($this->flags & self::FLAG_GENERATED) > 0;
	}

	/**
	 * @return bool
	 */
	public function isPopulated(){
		return ($this->flags & self::FLAG_POPULATED) > 0;
	}

	/**
	 * @param bool $value
	 */
	public function setGenerated($value = true){
		$this->flags = ($this->flags & ~self::FLAG_GENERATED) | ($value === true ? self::FLAG_GENERATED : 0);
	}

	/**
	 * @param bool $value
	 */
	public function setPopulated($value = true){
		$this->flags = ($this->flags & ~self::FLAG_POPULATED) | ($value === true ? self::FLAG_POPULATED : 0);
	}

	/**
	 * @param int $x 0-15
	 * @param int $y 0-127
	 * @param int $z 0-15
	 *
	 * @return int 0-255
	 */
	public function getBlockId($x, $y, $z){
		return ord(@$this->ids[$y >> 4]{(($y & 0x0f) << 8) + ($z << 4) + $x});
	}

	/**
	 * @param int $x 0-15
	 * @param int $y 0-127
	 * @param int $z 0-15
	 * @param int $blockId 0-255
	 */
	public function setBlockId($x, $y, $z, $blockId){
		@$this->ids[$y >> 4]{(($y & 0x0f) << 8) + ($z << 4) + $x} = chr($blockId);
	}

	/**
	 * @param int $x 0-15
	 * @param int $y 0-127
	 * @param int $z 0-15
	 *
	 * @return int 0-15
	 */
	public function getBlockData($x, $y, $z){
		$m = ord($this->meta[$y >> 4]{(($y & 0x0f) << 7) + ($z << 3) + ($x >> 1)});
		if(($y & 1) === 0){
			return $m & 0x0F;
		}else{
			return $m >> 4;
		}
	}

	/**
	 * @param int $x 0-15
	 * @param int $y 0-127
	 * @param int $z 0-15
	 * @param int $data 0-15
	 */
	public function setBlockData($x, $y, $z, $data){
		$i = (($y & 0x0f) << 7) + ($z << 3) + ($x >> 1);
		$old_m = ord($this->meta[$y >> 4]{$i});
		if(($y & 1) === 0){
			$this->meta[$y >> 4]{$i} = chr(($old_m & 0xf0) | ($data & 0x0f));
		}else{
			$this->meta[$y >> 4]{$i} = chr((($data & 0x0f) << 4) | ($old_m & 0x0f));
		}
	}

	/**
	 * @param int $y 0-7
	 *
	 * @return string
	 */
	public function getSectionIds($y){
		return $this->ids[$y];
	}

	/**
	 * @param int $y 0-7
	 *
	 * @return string
	 */
	public function getSectionData($y){
		return $this->meta[$y];
	}

	/**
	 * @param int    $y 0-7
	 * @param string $ids
	 * @param string $meta
	 */
	public function setSection($y, $ids = null, $meta = null){
		if($ids !== null){
			$this->ids[$y] = $ids;
		}

		if($meta !== null){
			$this->meta[$y] = $meta;
		}
	}

	/**
	 * @return string
	 */
	public function toBinary(){
		$binary = Binary::writeInt($this->x) . Binary::writeInt($this->z) . chr($this->flags);
		if($this->isGenerated()){
			for($y = 0; $y < self::$HEIGHT; ++$y){
				$binary .= $this->getSectionIds($y);
				$binary .= $this->getSectionData($y);
			}
		}

		return $binary;
	}

	/**
	 * @param string $binary
	 *
	 * @return SimpleChunk
	 */
	public static function fromBinary($binary){
		$offset = 0;
		$chunkX = Binary::readInt(substr($binary, $offset, 4));
		$offset += 4;
		$chunkZ = Binary::readInt(substr($binary, $offset, 4));
		$offset += 4;
		$flags = ord($binary{$offset++});
		$ids = [];
		$meta = [];
		if(($flags & self::FLAG_GENERATED) > 0){
			for($y = 0; $y < self::$HEIGHT; ++$y){
				$ids[$y] = substr($binary, $offset, 4096);
				$offset += 4096;
				$meta[$y] = substr($binary, $offset, 2048);
				$offset += 2048;
			}
		}
		return new SimpleChunk($chunkX, $chunkZ, $flags, $ids, $meta);
	}

}