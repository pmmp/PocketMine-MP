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

class BlockAPI{
	private $server;
	function __construct($server){
		$this->server = $server;
	}
	
	public function init(){
		$this->server->addHandler("world.block.update", array($this, "handle"));
		$this->server->addHandler("player.block.action", array($this, "handle"));
	}
	
	public function handle($data, $event){
		switch($event){
			case "player.block.action":
				$target = $this->server->api->level->getBlock($data["x"], $data["y"], $data["z"]);
				$cancelPlace = false;
				if(isset(Material::$activable[$target[0]])){
					
					switch($target[0]){
						case 2:
						case 3:
						case 6:
							break;
						default:
							$cancelPlace = true;
							break;
					}
					
				}

				if($cancelPlace === true or $data["face"] < 0 or $data["face"] > 5){
					return;
				}
				
				if(!isset(Material::$replaceable[$target[0]])){
					switch($data["face"]){
						case 0:
							--$data["y"];
							break;
						case 1:
							++$data["y"];
							break;
						case 2:
							--$data["z"];
							break;
						case 3:
							++$data["z"];
							break;
						case 4:
							--$data["x"];
							break;
						case 5:
							++$data["x"];
							break;
					}
				}
				if($data["y"] >= 127){
					return;
				}
				
				$block = $this->server->api->level->getBlock($data["x"], $data["y"], $data["z"]);
				
				if(!isset(Material::$replaceable[$block[0]])){
					return;
				}
				
				if(isset(Material::$placeable[$data["block"]])){
					$data["block"] = Material::$placeable[$data["block"]] === true ? $data["block"]:Material::$placeable[$data["block"]];
				}else{
					return;
				}
				
				$entity = $this->server->api->entity->get($this->eid);				
				
				switch($data["block"]){
					case 50: //Torch
						if(isset(Material::$transparent[$target[0]])){
							return;
						}
						$faces = array(
							0 => 6,
							1 => 5,
							2 => 4,
							3 => 3,
							4 => 2,
							5 => 1,
						);
						if(!isset($faces[$data["face"]])){
							return false;
						}
						$data["meta"] = $faces[$data["face"]];
						break;
					case 64://Door placing
						$blockUp = $this->server->api->level->getBlock($data["x"], $data["y"] + 1, $data["z"]);
						$blockDown = $this->server->api->level->getBlock($data["x"], $data["y"] - 1, $data["z"]);
						if(!isset(Material::$replaceable[$blockUp[0]]) or isset(Material::$transparent[$blockDown[0]])){
							return;
						}else{
							$data2 = $data;
							$data2["meta"] = $data2["meta"] | 0x08;
							$data["meta"] = $data["meta"] & 0x07;
							++$data2["y"];
							$this->server->handle("player.block.place", $data2);
						}
						break;
					case 65: //Ladder
						if(isset(Material::$transparent[$target[0]])){
							return;
						}
						$faces = array(
							2 => 2,
							3 => 3,
							4 => 4,
							5 => 5,
						);
						if(!isset($faces[$data["face"]])){
							return false;
						}
						$data["meta"] = $faces[$data["face"]];
						break;
					case 59://Seeds
					case 105:
						$blockDown = $this->server->api->level->getBlock($data["x"], $data["y"] - 1, $data["z"]);
						if($blockDown[0] !== 60){
							return;
						}
						$data["meta"] = 0;
						break;
					case 81: //Cactus
						$blockDown = $this->server->api->level->getBlock($data["x"], $data["y"] - 1, $data["z"]);
						$block0 = $this->server->api->level->getBlock($data["x"] + 1, $data["y"], $data["z"] + 1);
						$block1 = $this->server->api->level->getBlock($data["x"] - 1, $data["y"], $data["z"] + 1);
						$block2 = $this->server->api->level->getBlock($data["x"] + 1, $data["y"], $data["z"] - 1);
						$block3 = $this->server->api->level->getBlock($data["x"] - 1, $data["y"], $data["z"] - 1);
						if($blockDown[0] !== 12 or $block0[0] !== 0 or $block1[0] !== 0 or $block2[0] !== 0 or $block3[0] !== 0){
							return;
						}
						break;
				}
				$this->server->handle("player.block.place", $data);
				$this->updateBlocksAround($data["x"], $data["x"], $data["z"]);
		}
	}
	
	public function updateBlocksAround($x, $y, $z){
	
	}
}