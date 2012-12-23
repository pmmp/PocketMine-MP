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

				if($cancelPlace === true or $face < 0 or $face > 5){
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
					case 53:
						
						break;
				}
				$this->server->handle("player.block.place", $data);
				$this->updateBlocksAround($data["x"], $data["x"], $data["z"]);
		}
	}
	
	public function updateBlocksAround($x, $y, $z){
	
	}
}