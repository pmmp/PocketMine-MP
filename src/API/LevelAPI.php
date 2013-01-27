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
		$this->server->event("player.block.break", array($this, "handle"));
		$this->server->event("player.block.place", array($this, "handle"));
		$this->server->event("player.block.update", array($this, "handle"));
	}

	public function handle($data, $event){
		switch($event){
			case "player.block.place":
			case "player.block.update":
				console("[DEBUG] EID ".$data["eid"]." placed ".$data["block"].":".$data["meta"]." at X ".$data["x"]." Y ".$data["y"]." Z ".$data["z"], true, true, 2);
				$this->setBlock($data["x"], $data["y"], $data["z"], $data["block"], $data["meta"]);
				break;
			case "player.block.break":
				$block = $this->getBlock($data["x"], $data["y"], $data["z"]);
				console("[DEBUG] EID ".$data["eid"]." broke ".$block[0].":".$block[1]." at X ".$data["x"]." Y ".$data["y"]." Z ".$data["z"], true, true, 2);
				if($block[0] === 0){
					break;
				}
				$this->setBlock($data["x"], $data["y"], $data["z"], 0, 0, true, true);
				break;
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

	public function getOrderedChunk($X, $Z, $columnsPerPacket = 2){
		$columnsPerPacket = max(1, (int) $columnsPerPacket);
		$c = $this->getChunk($X, $Z);
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
}