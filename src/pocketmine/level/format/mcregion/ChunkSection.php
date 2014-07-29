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

namespace pocketmine\level\format\mcregion;

class ChunkSection implements \pocketmine\level\format\ChunkSection{

	private $y;
	private $blocks;
	private $data;
	private $blockLight;
	private $skyLight;

	public function __construct($y, $blocks, $data, $blockLight, $skyLight){
		$this->y = $y;
		$this->blocks = $blocks;
		$this->data = $data;
		$this->blockLight = $blockLight;
		$this->skyLight = $skyLight;
	}

	public function getY(){
		return $this->y;
	}

	public function getBlockId($x, $y, $z){
		return ord($this->blocks{($z << 8) + ($x << 4) + $y});
	}

	public function setBlockId($x, $y, $z, $id){
		$this->blocks{($z << 8) + ($x << 4) + $y} = chr($id);
	}

	public function getBlockData($x, $y, $z){
		$m = ord($this->data{($z << 7) + ($x << 3) + ($y >> 1)});
		if(($y & 1) === 0){
			return $m >> 4;
		}else{
			return $m & 0x0F;
		}
	}

	public function setBlockData($x, $y, $z, $data){
		$i = ($z << 7) + ($x << 3) + ($y >> 1);
		$old_m = ord($this->data{$i});
		if(($y & 1) === 0){
			$this->data{$i} = chr((($data & 0x0f) << 4) | ($old_m & 0x0f));
		}else{
			$this->data{$i} = chr(($old_m & 0xf0) | ($data & 0x0f));
		}
	}

	public function getBlock($x, $y, $z, &$blockId, &$meta = null){
		$i = ($z << 8) + ($x << 4) + $y;
		$blockId = ord($this->blocks{$i});
		$m = ord($this->data{$i >> 1});
		if(($y & 1) === 0){
			$meta = $m >> 4;
		}else{
			$meta = $m & 0x0F;
		}
	}

	public function setBlock($x, $y, $z, $blockId = null, $meta = null){
		$i = ($z << 8) + ($x << 4) + $y;

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
			if(($y & 1) === 0){
				$this->data{$i} = chr((($meta & 0x0f) << 4) | ($old_m & 0x0f));
				if((($old_m & 0xf0) >> 4) !== $meta){
					$changed = true;
				}
			}else{
				$this->data{$i} = chr(($old_m & 0xf0) | ($meta & 0x0f));
				if(($old_m & 0x0f) !== $meta){
					$changed = true;
				}
			}
		}

		return $changed;
	}

	public function getBlockSkyLight($x, $y, $z){
		$sl = ord($this->skyLight{($z << 7) + ($x << 3) + ($y >> 1)});
		if(($y & 1) === 0){
			return $sl >> 4;
		}else{
			return $sl & 0x0F;
		}
	}

	public function setBlockSkyLight($x, $y, $z, $level){
		$i = ($z << 7) + ($x << 3) + ($y >> 1);
		$old_sl = ord($this->skyLight{$i});
		if(($y & 1) === 0){
			$this->skyLight{$i} = chr((($level & 0x0f) << 4) | ($old_sl & 0x0f));
		}else{
			$this->skyLight{$i} = chr(($old_sl & 0xf0) | ($level & 0x0f));
		}
	}

	public function getBlockLight($x, $y, $z){
		$l = ord($this->blockLight{($z << 7) + ($x << 3) + ($y >> 1)});
		if(($y & 1) === 0){
			return $l >> 4;
		}else{
			return $l & 0x0F;
		}
	}

	public function setBlockLight($x, $y, $z, $level){
		$i = ($z << 7) + ($x << 3) + ($y >> 1);
		$old_l = ord($this->blockLight{$i});
		if(($y & 1) === 0){
			$this->blockLight{$i} = chr((($level & 0x0f) << 4) | ($old_l & 0x0f));
		}else{
			$this->blockLight{$i} = chr(($old_l & 0xf0) | ($level & 0x0f));
		}
	}

	public function getBlockIdColumn($x, $z){
		return substr($this->blocks, ($z << 8) + ($x << 4), 16);
	}

	public function getBlockDataColumn($x, $z){
		return substr($this->data, ($z << 7) + ($x << 3), 8);
	}

	public function getBlockSkyLightColumn($x, $z){
		return substr($this->skyLight, ($z << 7) + ($x << 3), 8);
	}

	public function getBlockLightColumn($x, $z){
		return substr($this->blockLight, ($z << 7) + ($x << 3), 8);
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