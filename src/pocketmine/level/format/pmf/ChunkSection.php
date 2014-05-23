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

namespace pocketmine\level\format\pmf;

class ChunkSection implements \pocketmine\level\format\ChunkSection{
	const METADATA_OFFSET = 16;
	const LIGHT_OFFSET = 24;
	const SKYLIGHT_OFFSET = 32;

	private $section = "";

	public function __construct($section = null){
		if($section !== null){
			if(strlen($section) !== 8192){
				trigger_error("Invalid ChunkSection generated", E_USER_WARNING);

				return;
			}
			$this->section = $section;
		}else{
			$this->section = str_repeat("\x00\x00\x00\x00\x00\x00\x00\x00", 1024);
		}
	}

	public function getBlockId($x, $y, $z){
		return $b = ord($this->section{$y + ($x << 5) + ($z << 9)});
	}

	public function getBlock($x, $y, $z, &$blockId, &$meta = null){
		$i = ($x << 5) + ($z << 9);
		$blockId = ord($this->section{$i + $y});
		$meta = ord($this->section{($y >> 1) + self::METADATA_OFFSET});
	}

	public function setBlock($x, $y, $z, $blockId = null, $meta = null){
		$i = ($x << 5) + ($z << 9);
		if($blockId !== null){
			$this->section{$i + $y} = chr($blockId);
		}
		if($meta !== null){
			$i += ($y >> 1) + self::METADATA_OFFSET;
			$m = ord($this->section{$i});
			if(($y & 1) === 0){
				$this->section{$i} = chr(($m & 0xf0) | ($meta & 0x0f));
			}else{
				$this->section{$i} = chr((($meta & 0x0f) << 4) | ($m & 0x0f));
			}
		}
	}

	public function setBlockId($x, $y, $z, $id){
		$this->section{$y + ($x << 5) + ($z << 9)} = chr($id & 0xff);
	}

	public function getBlockData($x, $y, $z){
		$m = ord($this->section{($y >> 1) + self::METADATA_OFFSET + ($x << 5) + ($z << 9)});
		if(($y & 1) === 0){
			return $m & 0x0F;
		}else{
			return $m >> 4;
		}
	}

	public function setBlockData($x, $y, $z, $data){
		$i = ($y >> 1) + self::METADATA_OFFSET + ($x << 5) + ($z << 9);
		$m = ord($this->section{$i});
		if(($y & 1) === 0){
			$this->section{$i} = chr(($m & 0xf0) | ($data & 0x0f));
		}else{
			$this->section{$i} = chr((($data & 0x0f) << 4) | ($m & 0x0f));
		}
	}

	public function getBlockIdColumn($x, $z){
		$column = "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00";
		$i = ($x << 5) + ($z << 9);
		for($y = 15; $y >= 0; --$y){
			$column{15 - $y} = $this->section{$i + $y};
		}

		return $column;
	}

	public function getBlockDataColumn($x, $z){
		$column = "\x00\x00\x00\x00\x00\x00\x00\x00";
		$i = ($x << 5) + ($z << 9) + self::METADATA_OFFSET;
		for($y = 7; $y >= 0; --$y){
			$column{7 - $y} = $this->section{$i + $y};
		}

		return $column;
	}


	public function getBlockLight($x, $y, $z){
		$l = ord($this->section{($y >> 1) + self::LIGHT_OFFSET + ($x << 5) + ($z << 9)});
		if(($y & 1) === 0){
			return $l & 0x0F;
		}else{
			return $l >> 4;
		}
	}

	public function setBlockLight($x, $y, $z, $level){
		$i = ($y >> 1) + self::LIGHT_OFFSET + ($x << 5) + ($z << 9);
		$l = ord($this->section{$i});
		if(($y & 1) === 0){
			$this->section{$i} = chr(($l & 0xf0) | ($level & 0x0f));
		}else{
			$this->section{$i} = chr((($level & 0x0f) << 4) | ($l & 0x0f));
		}
	}

	public function getBlockSkyLight($x, $y, $z){
		$sl = ord($this->section{($y >> 1) + self::SKYLIGHT_OFFSET + ($x << 5) + ($z << 9)});
		if(($y & 1) === 0){
			return $sl & 0x0F;
		}else{
			return $sl >> 4;
		}
	}

	public function setBlockSkyLight($x, $y, $z, $level){
		$i = ($y >> 1) + self::SKYLIGHT_OFFSET + ($x << 5) + ($z << 9);
		$sl = ord($this->section{$i});
		if(($y & 1) === 0){
			$this->section{$i} = chr(($sl & 0xf0) | ($level & 0x0f));
		}else{
			$this->section{$i} = chr((($level & 0x0f) << 4) | ($sl & 0x0f));
		}
	}
}