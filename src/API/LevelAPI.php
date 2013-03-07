<?php

/*

           -
         /   \
      /         \
   /   PocketMine  \
/          MP         \
|\     @shoghicp     /|
|.   \           /   .|
| ..     \   /     .. |
|    ..    |    ..    |
|       .. | ..       |
\          |          /
   \       |       /
      \    |    /
         \ | /

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Lesser General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.


*/

class LevelAPI{
	private $server, $map;
	function __construct(PocketMinecraftServer $server){
		$this->server = $server;
		$this->map = $this->server->map;
		$this->heightMap = array_fill(0, 256, array());
	}

	public function init(){

	}

	public function handle($data, $event){
		switch($event){
		}
	}

	public function getSpawn(){
		return $this->server->spawn;
	}

	public function getChunk($X, $Z){
		return $this->map->map[$X][$Z];
	}

	public function getBlockFace($block, $face){
		$data = array("x" => $block[2][0], "y" => $block[2][1], "z" => $block[2][2]);
		BlockFace::setPosition($data, $face);
		return $this->getBlock($data["x"], $data["y"], $data["z"]);
	}

	public function getBlock($x, $y, $z){
		$b = $this->map->getBlock($x, $y, $z);
		$b[2] = array($x, $y, $z);
		return $b;
	}

	public function getFloor($x, $z){
		if(!isset($this->heightMap[$z][$x])){
			$this->heightMap[$z][$x] = $this->map->getFloor($x, $z);
		}
		return $this->heightMap[$z][$x];
	}

	public function setBlock($x, $y, $z, $block, $meta = 0, $update = true, $tiles = false){
		if($x < 0 or $y < 0 or $z < 0){
			return false;
		}
		if($this->server->api->dhandle("block.change", array(
			"x" => $x,
			"y" => $y,
			"z" => $z,
			"block" => $block,
			"meta" => $meta,
		)) !== false){
			$this->map->setBlock($x, $y, $z, $block, $meta);
			$this->heightMap[$z][$x] = $this->map->getFloor($x, $z);
			if($update === true){
				$this->server->api->block->updateBlock($x, $y, $z, BLOCK_UPDATE_NORMAL);
				$this->server->api->block->updateBlocksAround($x, $y, $z, BLOCK_UPDATE_NORMAL);
			}
			if($tiles === true){
				if(($t = $this->server->api->tileentity->get($x, $y, $z)) !== false){
					$t[0]->close();
				}
			}
		}
		return true;
	}

	public function getOrderedChunks($X, $Z, $columnsPerPacket = 2){
		$columnsPerPacket = max(1, (int) $columnsPerPacket);
		$ordered = array();
		$i = 0;
		$cnt = 0;
		$ordered[$i] = "";
		for($z = 0; $z < 16; ++$z){
			for($x = 0; $x < 16; ++$x){
				if($cnt >= $columnsPerPacket){
					++$i;
					$ordered[$i] = str_repeat("\x00", $i * $columnsPerPacket);
					$cnt = 0;
				}
				$ordered[$i] .= "\xff";
				$block = $this->map->getChunkColumn($X, $Z, $x, $z, 0);
				$meta = $this->map->getChunkColumn($X, $Z, $x, $z, 1);
				for($k = 0; $k < 8; ++$k){
					$ordered[$i] .= substr($block, $k << 4, 16);
					$ordered[$i] .= substr($meta, $k << 3, 8);
				}
				++$cnt;
			}
		}
		return $ordered;
	}
	
	public function getMiniChunk($X, $Z, $Y, $MTU){
		$ordered = array();
		$i = 0;
		$ordered[$i] = "";
		$cnt = 0;
		for($z = 0; $z < 16; ++$z){
			for($x = 0; $x < 16; ++$x){
				if((strlen($ordered[$i]) + 16 + 8 + 1) > $MTU){
					++$i;
					$ordered[$i] = str_repeat("\x00", $cnt);
				}
				$ordered[$i] .= chr(1 << $Y);
				$block = $this->map->getChunkColumn($X, $Z, $x, $z, 0);
				$meta = $this->map->getChunkColumn($X, $Z, $x, $z, 1);
				$ordered[$i] .= substr($block, $Y << 4, 16);
				$ordered[$i] .= substr($meta, $Y << 3, 8);
				++$cnt;
			}
		}
		return $ordered;
	}
}