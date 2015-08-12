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

namespace pocketmine\level\format\anvil;

use pocketmine\nbt\tag\CompoundTag;

class ChunkSection implements \pocketmine\level\format\ChunkSection{

	private $y;
	private $blocks;
	private $data;
	private $blockLight;
	private $skyLight;

	public function __construct(CompoundTag $nbt){
		$this->y = (int) $nbt["Y"];
		$this->blocks = (string) $nbt["Blocks"];
		$this->data = (string) $nbt["Data"];
		$this->blockLight = (string) $nbt["BlockLight"];
		$this->skyLight = (string) $nbt["SkyLight"];
	}

	public function getY(){
		return $this->y;
	}

	public function getBlockId($x, $y, $z){
		return ord($this->blocks{($y << 8) + ($z << 4) + $x});
	}

	public function setBlockId($x, $y, $z, $id){
		$this->blocks{($y << 8) + ($z << 4) + $x} = chr($id);
	}

	public function getBlockData($x, $y, $z){
		$m = ord($this->data{($y << 7) + ($z << 3) + ($x >> 1)});
		if(($x & 1) === 0){
			return $m & 0x0F;
		}else{
			return $m >> 4;
		}
	}

	public function setBlockData($x, $y, $z, $data){
		$i = ($y << 7) + ($z << 3) + ($x >> 1);
		$old_m = ord($this->data{$i});
		if(($x & 1) === 0){
			$this->data{$i} = chr(($old_m & 0xf0) | ($data & 0x0f));
		}else{
			$this->data{$i} = chr((($data & 0x0f) << 4) | ($old_m & 0x0f));
		}
	}

	public function getBlock($x, $y, $z, &$blockId, &$meta = null){
		$full = $this->getFullBlock($x, $y, $z);
		$blockId = $full >> 4;
		$meta = $full & 0x0f;
	}

	public function getFullBlock($x, $y, $z){
		$i = ($y << 8) + ($z << 4) + $x;
		if(($x & 1) === 0){
			return (ord($this->blocks{$i}) << 4) | (ord($this->data{$i >> 1}) & 0x0F);
		}else{
			return (ord($this->blocks{$i}) << 4) | (ord($this->data{$i >> 1}) >> 4);
		}
	}

	public function setBlock($x, $y, $z, $blockId = null, $meta = null){
		$i = ($y << 8) + ($z << 4) + $x;

		$changed = false;

		if($blockId !== null){
			$blockId = chr($blockId);
			if($this->blocks{$i} !== $blockId){
				$this->blocks{$i} = $blockId;
				$changed = true;
			}
		}

		if($meta !== null){
			$i >>= 1;
			$old_m = ord($this->data{$i});
			if(($x & 1) === 0){
				$this->data{$i} = chr(($old_m & 0xf0) | ($meta & 0x0f));
				if(($old_m & 0x0f) !== $meta){
					$changed = true;
				}
			}else{
				$this->data{$i} = chr((($meta & 0x0f) << 4) | ($old_m & 0x0f));
				if((($old_m & 0xf0) >> 4) !== $meta){
					$changed = true;
				}
			}
		}

		return $changed;
	}

	public function getBlockSkyLight($x, $y, $z){
		$sl = ord($this->skyLight{($y << 7) + ($z << 3) + ($x >> 1)});
		if(($x & 1) === 0){
			return $sl & 0x0F;
		}else{
			return $sl >> 4;
		}
	}

	public function setBlockSkyLight($x, $y, $z, $level){
		$i = ($y << 7) + ($z << 3) + ($x >> 1);
		$old_sl = ord($this->skyLight{$i});
		if(($x & 1) === 0){
			$this->skyLight{$i} = chr(($old_sl & 0xf0) | ($level & 0x0f));
		}else{
			$this->skyLight{$i} = chr((($level & 0x0f) << 4) | ($old_sl & 0x0f));
		}
	}

	public function getBlockLight($x, $y, $z){
		$l = ord($this->blockLight{($y << 7) + ($z << 3) + ($x >> 1)});
		if(($x & 1) === 0){
			return $l & 0x0F;
		}else{
			return $l >> 4;
		}
	}

	public function setBlockLight($x, $y, $z, $level){
		$i = ($y << 7) + ($z << 3) + ($x >> 1);
		$old_l = ord($this->blockLight{$i});
		if(($x & 1) === 0){
			$this->blockLight{$i} = chr(($old_l & 0xf0) | ($level & 0x0f));
		}else{
			$this->blockLight{$i} = chr((($level & 0x0f) << 4) | ($old_l & 0x0f));
		}
	}

	public function getBlockIdColumn($x, $z){
		$i = ($z << 4) + $x;
		$column = "";
		for($y = 0; $y < 16; ++$y){
			$column .= $this->blocks{($y << 8) + $i};
		}

		return $column;
	}

	public function getBlockDataColumn($x, $z){
		$i = ($z << 3) + ($x >> 1);
		$column = "";
		if(($x & 1) === 0){
			for($y = 0; $y < 16; $y += 2){
				$column .= ($this->data{($y << 7) + $i} & "\x0f") | chr((ord($this->data{(($y + 1) << 7) + $i}) & 0x0f) << 4);
			}
		}else{
			for($y = 0; $y < 16; $y += 2){
				$column .= chr((ord($this->data{($y << 7) + $i}) & 0xf0) >> 4) | ($this->data{(($y + 1) << 7) + $i} & "\xf0");
			}
		}

		return $column;
	}

	public function getBlockSkyLightColumn($x, $z){
		$i = ($z << 3) + ($x >> 1);
		$column = "";
		if(($x & 1) === 0){
			for($y = 0; $y < 16; $y += 2){
				$column .= ($this->skyLight{($y << 7) + $i} & "\x0f") | chr((ord($this->skyLight{(($y + 1) << 7) + $i}) & 0x0f) << 4);
			}
		}else{
			for($y = 0; $y < 16; $y += 2){
				$column .= chr((ord($this->skyLight{($y << 7) + $i}) & 0xf0) >> 4) | ($this->skyLight{(($y + 1) << 7) + $i} & "\xf0");
			}
		}

		return $column;
	}

	public function getBlockLightColumn($x, $z){
		$i = ($z << 3) + ($x >> 1);
		$column = "";
		if(($x & 1) === 0){
			for($y = 0; $y < 16; $y += 2){
				$column .= ($this->blockLight{($y << 7) + $i} & "\x0f") | chr((ord($this->blockLight{(($y + 1) << 7) + $i}) & 0x0f) << 4);
			}
		}else{
			for($y = 0; $y < 16; $y += 2){
				$column .= chr((ord($this->blockLight{($y << 7) + $i}) & 0xf0) >> 4) | ($this->blockLight{(($y + 1) << 7) + $i} & "\xf0");
			}
		}

		return $column;
	}

	public function getIdArray(){
		return $this->blocks;
	}

	public function getDataArray(){
		return $this->data;
	}

	public function getSkyLightArray(){
		return $this->skyLight;
	}

	public function getLightArray(){
		return $this->blockLight;
	}

}