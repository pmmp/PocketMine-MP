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

define("BLOCK_UPDATE_NORMAL", 0);
define("BLOCK_UPDATE_RANDOM", 1);
define("BLOCK_UPDATE_SCHEDULED", 2);
define("BLOCK_UPDATE_WEAK", 3);



class BlockAPI{
	private $server;
	function __construct(PocketMinecraftServer $server){
		$this->server = $server;
	}
	
	public function init(){
		$this->server->addHandler("world.block.update", array($this, "updateBlockRemote"), 1);
		$this->server->addHandler("player.block.break", array($this, "blockBreak"), 1);
		$this->server->addHandler("player.block.action", array($this, "blockAction"), 1);
	}
	
	private function cancelAction($block){
		$this->server->api->dhandle("world.block.change", array(
			"x" => $block[2][0],
			"y" => $block[2][1],
			"z" => $block[2][2],
			"block" => $block[0],
			"meta" => $block[1],
			"fake" => true,
		));
		return false;
	}
	
	public function blockBreak($data, $event){
		if($event !== "player.block.break"){
			return;
		}
		$target = $this->server->api->level->getBlock($data["x"], $data["y"], $data["z"]);
		if(isset(Material::$unbreakable[$target[0]])){
			return $this->cancelAction($target);
		}
		$drop = array(
			$target[0], //Block
			$target[1], //Meta
			1, //Count
		);
		switch($target[0]){
			case 16:
				$drop = array(263, 0, 1);
				break;
			case 21:
				$drop = array(351, 4, mt_rand(4, 8));
				break;
			case 56:
				$drop = array(264, 0, 1);
				break;
			case 73:
			case 74:
				$drop = array(351, 4, mt_rand(4, 5));
				break;
			case 18:
				$drop = false;
				if(mt_rand(1,20) === 1){ //Saplings
					$drop = array(6, $target[1], 1);
				}
				if($target[1] === 0 and mt_rand(1,200) === 1){ //Apples
					$this->drop($data["x"], $data["y"], $data["z"], 260, 0, 1);
				}
				break;
			case 59:
				if($target[1] >= 0x07){ //Seeds
					$drop = array(296, 0, 1);
					$this->drop($data["x"], $data["y"], $data["z"], 295, 0, mt_rand(0,3));
				}else{
					$drop = array(295, 0, 1);
				}
				break;
			case 31:
				$drop = false;
				if(mt_rand(1,10) === 1){ //Seeds
					$drop = array(295, 0, 1);
				}
				break;
			case 20:
				$drop = false;
				break;
			case 30:
				$drop = false;
				break;
			case 51:
				$drop = false;
				break;
			case 52:
				$drop = false;
				break;
			case 43:
				$drop = array(
					44,
					$target[1],
					2,
				);
				break;
			case 60:
			case 2:
				$drop = array(3, 0, 1);
				break;
			case 64: //Door
				$drop = array(324, 0, 1);
				if(($target[1] & 0x08) === 0x08){
					$down = $this->server->api->level->getBlock($data["x"], $data["y"] - 1, $data["z"]);
					if($down[0] === 64){
						$data2 = $data;
						--$data2["y"];
						$this->server->handle("player.block.break", $data2);
					}
				}else{
					$up = $this->server->api->level->getBlock($data["x"], $data["y"] + 1, $data["z"]);
					if($up[0] === 64){
						$data2 = $data;
						++$data2["y"];
						$this->server->handle("player.block.break", $data2);
					}
				}
				break;
		}
		if($drop !== false and $drop[0] !== 0 and $drop[2] > 0){
			$this->drop($data["x"], $data["y"], $data["z"], $drop[0], $drop[1] & 0x0F, $drop[2] & 0xFF);
		}
		$this->server->handle("player.block.break", $data);
		return false;
	}
	
	public function drop($x, $y, $z, $block, $meta, $stack = 1){
		if($block === 0 or $stack <= 0 or $this->server->gamemode === 1){
			return;
		}
		$data = array(
			"x" => $x,
			"y" => $y,
			"z" => $z,
			"meta" => $meta,
			"stack" => $stack,
		);
		$data["x"] += mt_rand(2, 8) / 10;
		$data["y"] += mt_rand(2, 8) / 10;
		$data["z"] += mt_rand(2, 8) / 10;
		$e = $this->server->api->entity->add(ENTITY_ITEM, $block, $data);
		$this->server->api->entity->spawnToAll($e->eid);	
	}
	
	public function blockAction($data, $event){
		if($event !== "player.block.action"){
			return;
		}
		if($data["face"] < 0 or $data["face"] > 5){
			return false;
		}
		$target = $this->server->api->level->getBlock($data["x"], $data["y"], $data["z"]);
		if($target[0] === 0){ //If no block exists
			$this->cancelAction($target);
			$block = $this->server->api->level->getBlockFace($target, $data["face"]);
			return $this->cancelAction($block);		
		}
		
		$cancelPlace = false;
		if(isset(Material::$activable[$target[0]])){
			switch($target[0]){
				case 6:
					if($data["block"] === 351 and $data["meta"] === 0x0F){ //Bonemeal
						Sapling::growTree($this->server->api->level, $target, $target[1] & 0x03);
						$cancelPlace = true;
					}
					break;
				case 2:
				case 3:
					if($data["block"] === 292){ //Hoe
						$data["block"] = 60;
						$data["meta"] = 0;
						$this->server->handle("player.block.place", $data);
						$cancelPlace = true;
					}
				case 59:
				case 105:
					if($data["block"] === 351 and $data["meta"] === 0x0F){ //Bonemeal
						$data["block"] = $target[0];
						$data["meta"] = 0x07;
						$this->server->handle("player.block.place", $data);							
						$cancelPlace = true;
					}					
					break;
				case 64: //Door
					if(($target[1] & 0x08) === 0x08){
						$down = $this->server->api->level->getBlock($data["x"], $data["y"] - 1, $data["z"]);
						if($down[0] === 64){
							$down[1] = $down[1] ^ 0x04;
							$data2 = array(
								"x" => $data["x"],
								"z" => $data["z"],
								"y" => $data["y"] - 1,
								"block" => $down[0],
								"meta" => $down[1],
								"eid" => $data["eid"],
							);
							if($this->server->handle("player.block.update", $data2) !== false){
								$this->updateBlocksAround($data["x"], $data["y"], $data["z"], BLOCK_UPDATE_NORMAL);
							}
						}
					}else{
						$data["block"] = $target[0];
						$data["meta"] = $target[1] ^ 0x04;
						if($this->server->handle("player.block.update", $data) !== false){
							$up = $this->server->api->level->getBlock($data["x"], $data["y"] + 1, $data["z"]);
							if($up[0] === 64){
								$data2 = $data;
								$data2["meta"] = $up[1];
								++$data2["y"];
								$this->updateBlocksAround($data2["x"], $data2["y"], $data2["z"], BLOCK_UPDATE_NORMAL);
							}
							$this->updateBlocksAround($data["x"], $data["y"], $data["z"], BLOCK_UPDATE_NORMAL);
						}
					}
					$cancelPlace = true;
					break;
				case 96: //Trapdoor
				case 107: //Fence gates
					$data["block"] = $target[0];
					$data["meta"] = $target[1] ^ 0x04;
					$this->server->handle("player.block.update", $data);
					$cancelPlace = true;
					break;
				default:
					$cancelPlace = true;
					break;
			}			
		}

		if($cancelPlace === true){
			return false;
		}
		
		$replace = false;
		switch($target[0]){
			case 44: //Slabs
				if($data["face"] !== 1){
					break;
				}
				if(($target[1] & 0x07) === ($data["meta"] & 0x07)){
					$replace = true;
					$data["block"] = 43;
					$data["meta"] = $data["meta"] & 0x07;
				}
				break;
		}
		
		if($replace === false){
			BlockFace::setPosition($data, $data["face"]);
		}
			
		if($data["y"] >= 127){
			return false;
		}
		
		$block = $this->server->api->level->getBlock($data["x"], $data["y"], $data["z"]);
		
		if($replace === false and !isset(Material::$replaceable[$block[0]])){
			return $this->cancelAction($block);
		}
		
		if(isset(Material::$placeable[$data["block"]])){
			$data["block"] = Material::$placeable[$data["block"]] === true ? $data["block"]:Material::$placeable[$data["block"]];
		}else{
			return $this->cancelAction($block);
		}
		
		$direction = $this->server->api->entity->get($data["eid"])->getDirection();		
		
		switch($data["block"]){
			case 6:
				if($target[0] === 60){
					break;
				}
			case 37:
			case 38:
				if($target[0] !== 2 and $target[0] !== 3){
					return false;
				}
				break;
			case 39://Mushrooms
			case 40:
				$blockDown = $this->server->api->level->getBlock($data["x"], $data["y"] - 1, $data["z"]);
				if(isset(Material::$transparent[$blockDown[0]])){
					return false;
				}
				break;
			case 83: //Sugarcane
				$blockDown = $this->server->api->level->getBlock($data["x"], $data["y"] - 1, $data["z"]);
				if($blockDown[0] !== 2 and $blockDown[0] !== 3 and $blockDown[0] !== 12){
					return false;
				}
				$block0 = $this->server->api->level->getBlock($data["x"], $data["y"], $data["z"] + 1);
				$block1 = $this->server->api->level->getBlock($data["x"], $data["y"], $data["z"] - 1);
				$block2 = $this->server->api->level->getBlock($data["x"] + 1, $data["y"], $data["z"]);
				$block3 = $this->server->api->level->getBlock($data["x"] - 1, $data["y"], $data["z"]);
				if($block0[0] === 9 or $block0[0] === 8 or $block1[0] === 9 or $block1[0] === 8 or $block2[0] === 9 or $block2[0] === 8 or $block3[0] === 9 or $block3[0] === 8){
				
				}else{
					return false;
				}
				break;
			case 50: //Torch
				if(isset(Material::$transparent[$target[0]])){
					return false;
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
			case 53://Stairs
			case 67:
			case 108:
				$faces = array(
					0 => 0,
					1 => 2,
					2 => 1,
					3 => 3,
				);
				$data["meta"] = $faces[$direction] & 0x03;
				break;
			case 96: //trapdoor
				if(isset(Material::$transparent[$target[0]])){
					return false;
				}
				$faces = array(
					2 => 0,
					3 => 1,
					4 => 2,
					5 => 3,
				);
				if(!isset($faces[$data["face"]])){
					return false;
				}
				$data["meta"] = $faces[$data["face"]] & 0x03;
				break;
			case 107: //Fence gate
				$faces = array(
					0 => 3,
					1 => 0,
					2 => 1,
					3 => 2,
				);
				$data["meta"] = $faces[$direction] & 0x03;
				break;
			case 64://Door placing
				$blockUp = $this->server->api->level->getBlock($data["x"], $data["y"] + 1, $data["z"]);
				$blockDown = $this->server->api->level->getBlock($data["x"], $data["y"] - 1, $data["z"]);
				if(!isset(Material::$replaceable[$blockUp[0]]) or isset(Material::$transparent[$blockDown[0]])){
					return false;
				}else{
					$data2 = $data;
					$data2["meta"] = 0x08;
					$data["meta"] = $direction & 0x03;
					++$data2["y"];
					$this->server->handle("player.block.place", $data2);
				}
				break;
			case 54:
			case 61:
				$faces = array(
					0 => 4,
					1 => 2,
					2 => 5,
					3 => 3,
				);
				$data["meta"] = $faces[$direction];
				break;
			case 26: //bed
				$face = array(
					0 => 3,
					1 => 4,
					2 => 2,
					3 => 5,
				);
				$next = $this->server->api->level->getBlockFace($block, $face[(($direction + 3) % 4)]);
				if(!isset(Material::$replaceable[$next[0]])){
					return false;
				}
				$data["meta"] = (($direction + 3) % 4) & 0x3;
				$data2 = $data;
				$data2["meta"] = $data2["meta"] | 0x08;
				$data2["x"] = $next[2][0];
				$data2["y"] = $next[2][1];
				$data2["z"] = $next[2][2];
				$this->server->handle("player.block.place", $data2);
				break;
			case 65: //Ladder
				if(isset(Material::$transparent[$target[0]])){
					return false;
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
					return false;
				}
				$data["meta"] = 0;
				break;
			case 81: //Cactus
				$blockDown = $this->server->api->level->getBlock($data["x"], $data["y"] - 1, $data["z"]);
				$block0 = $this->server->api->level->getBlock($data["x"], $data["y"], $data["z"] + 1);
				$block1 = $this->server->api->level->getBlock($data["x"], $data["y"], $data["z"] - 1);
				$block2 = $this->server->api->level->getBlock($data["x"] + 1, $data["y"], $data["z"]);
				$block3 = $this->server->api->level->getBlock($data["x"] - 1, $data["y"], $data["z"]);
				if($blockDown[0] !== 12 or !isset(Material::$transparent[$block0[0]]) or !isset(Material::$transparent[$block1[0]]) or !isset(Material::$transparent[$block2[0]]) or !isset(Material::$transparent[$block3[0]])){
					return false;
				}
				break;
		}
		$this->server->handle("player.block.place", $data);
		return false;
	}
	
	public function blockScheduler($data){
		$this->updateBlock($data["x"], $data["y"], $data["z"], BLOCK_UPDATE_SCHEDULED);
	}
	
	public function updateBlockRemote($data, $event){
		if($event !== "world.block.update"){
			return;
		}
		$this->updateBlock($data["x"], $data["y"], $data["z"], isset($data["type"]) ? $data["type"]:BLOCK_UPDATE_RANDOM);
	}

	public function flowLavaOn($source, $face){
		$down = 0;
		if($face === BlockFace::BOTTOM){
			$level = 0;
			$down = 1;
		}else{
			$level = ($source[1] & 0x07) + 2;
			if($level > 0x07){
				return false;
			}
		}
		$spread = $this->server->api->level->getBlockFace($source, $face);
		if(($source[0] === 10 or $source[0] === 11) and $spread[0] === 10){
			if($level < ($spread[1] & 0x07)){
				$this->server->schedule(20, array($this, "blockScheduler"), array(
					"x" => $spread[2][0],
					"y" => $spread[2][1],
					"z" => $spread[2][2],
					"type" => BLOCK_UPDATE_NORMAL,
				));
				$this->server->api->level->setBlock($spread[2][0], $spread[2][1], $spread[2][2], $spread[0], $level | $down, false);
				return true;
			}
		}elseif(isset(Material::$flowable[$spread[0]])){
			$this->server->schedule(20, array($this, "blockScheduler"), array(
				"x" => $spread[2][0],
				"y" => $spread[2][1],
				"z" => $spread[2][2],
				"type" => BLOCK_UPDATE_NORMAL,
			));
			$this->server->api->level->setBlock($spread[2][0], $spread[2][1], $spread[2][2], 10, $level | $down, false);
			return true;			
		}elseif(($source[1] & 0x08) === 0x08){
			$this->server->api->level->setBlock($spread[2][0], $spread[2][1], $spread[2][2], $source[0], $source[1] & 0x07, false);
			return true;
		}
		return false;
	}
	
	public function flowWaterOn($source, $face){
		$down = 0;
		if($face === BlockFace::BOTTOM){
			$level = 0;
			$down = 1;
		}else{
			$level = ($source[1] & 0x07) + 1;
			if($level > 0x07){
				return false;
			}
		}
		$spread = $this->server->api->level->getBlockFace($source, $face);
		if(($source[0] === 8 or $source[0] === 9) and $spread[0] === 8){
			if($level < ($spread[1] & 0x07)){
				$this->server->schedule(10, array($this, "blockScheduler"), array(
					"x" => $spread[2][0],
					"y" => $spread[2][1],
					"z" => $spread[2][2],
					"type" => BLOCK_UPDATE_NORMAL,
				));
				$this->server->api->level->setBlock($spread[2][0], $spread[2][1], $spread[2][2], $spread[0], $level | $down, false);
				return true;
			}
		}elseif(isset(Material::$flowable[$spread[0]])){
			$this->server->schedule(10, array($this, "blockScheduler"), array(
				"x" => $spread[2][0],
				"y" => $spread[2][1],
				"z" => $spread[2][2],
				"type" => BLOCK_UPDATE_NORMAL,
			));
			$this->server->api->level->setBlock($spread[2][0], $spread[2][1], $spread[2][2], 8, $level | $down, false);
			return true;			
		}elseif(($source[1] & 0x08) === 0x08){
			$this->server->api->level->setBlock($spread[2][0], $spread[2][1], $spread[2][2], $source[0], $source[1] & 0x07, false);
			return true;
		}
		return false;
	}
	
	public function updateBlock($x, $y, $z, $type = BLOCK_UPDATE_NORMAL){
		$block = $this->server->api->level->getBlock($x, $y, $z);
		$changed = false;

		switch($block[0]){
			case 8:
			case 9:
				if(!$this->flowWaterOn($block, 0) or $block[0] === 9){
					$this->flowWaterOn($block, 2);
					$this->flowWaterOn($block, 3);
					$this->flowWaterOn($block, 4);
					$this->flowWaterOn($block, 5);
				}
				if($block[0] === 8){
					$drained = true;
					$level = $block[1] & 0x07;
					$up = $this->server->api->level->getBlockFace($block, BlockFace::UP);
					if($up[0] === 8 or $up[0] === 9){
						$drained = false;
					}else{
						$b = $this->server->api->level->getBlockFace($block, BlockFace::NORTH);
						if($b[0] === 9 or ($b[0] === 8 and ($b[1] & 0x08) === 0 and ($b[1] & 0x07) < $level)){
							$drained = false;
						}else{
							$b = $this->server->api->level->getBlockFace($block, BlockFace::SOUTH);
							if($b[0] === 9 or ($b[0] === 8 and ($b[1] & 0x08) === 0 and ($b[1] & 0x07) < $level)){
								$drained = false;
							}else{
								$b = $this->server->api->level->getBlockFace($block, BlockFace::EAST);
								if($b[0] === 9 or ($b[0] === 8 and ($b[1] & 0x08) === 0 and ($b[1] & 0x07) < $level)){
									$drained = false;
								}else{
									$b = $this->server->api->level->getBlockFace($block, BlockFace::WEST);
									if($b[0] === 9 or ($b[0] === 8 and ($b[1] & 0x08) === 0 and ($b[1] & 0x07) < $level)){
										$drained = false;
									}
								}
							}
						}
					}
					if($drained === true){
						++$level;
						if($level > 0x07){
							$this->server->schedule(10, array($this, "blockScheduler"), array(
								"x" => $block[2][0] + 1,
								"y" => $block[2][1],
								"z" => $block[2][2],
								"type" => BLOCK_UPDATE_NORMAL,
							));
							$this->server->schedule(10, array($this, "blockScheduler"), array(
								"x" => $block[2][0] - 1,
								"y" => $block[2][1],
								"z" => $block[2][2],
								"type" => BLOCK_UPDATE_NORMAL,
							));
							$this->server->schedule(10, array($this, "blockScheduler"), array(
								"x" => $block[2][0],
								"y" => $block[2][1],
								"z" => $block[2][2] + 1,
								"type" => BLOCK_UPDATE_NORMAL,
							));
							$this->server->schedule(10, array($this, "blockScheduler"), array(
								"x" => $block[2][0],
								"y" => $block[2][1],
								"z" => $block[2][2] - 1,
								"type" => BLOCK_UPDATE_NORMAL,
							));
							$this->server->schedule(10, array($this, "blockScheduler"), array(
								"x" => $block[2][0],
								"y" => $block[2][1] - 1,
								"z" => $block[2][2],
								"type" => BLOCK_UPDATE_NORMAL,
							));
							$this->server->api->level->setBlock($block[2][0], $block[2][1], $block[2][2], 0, 0, false);						
						}else{
							$block[1] = ($block[1] & 0x08) | $level;
							$this->server->schedule(10, array($this, "blockScheduler"), array(
								"x" => $block[2][0] + 1,
								"y" => $block[2][1],
								"z" => $block[2][2],
								"type" => BLOCK_UPDATE_NORMAL,
							));
							$this->server->schedule(10, array($this, "blockScheduler"), array(
								"x" => $block[2][0] - 1,
								"y" => $block[2][1],
								"z" => $block[2][2],
								"type" => BLOCK_UPDATE_NORMAL,
							));
							$this->server->schedule(10, array($this, "blockScheduler"), array(
								"x" => $block[2][0],
								"y" => $block[2][1],
								"z" => $block[2][2] + 1,
								"type" => BLOCK_UPDATE_NORMAL,
							));
							$this->server->schedule(10, array($this, "blockScheduler"), array(
								"x" => $block[2][0],
								"y" => $block[2][1],
								"z" => $block[2][2] - 1,
								"type" => BLOCK_UPDATE_NORMAL,
							));
							$this->server->schedule(10, array($this, "blockScheduler"), array(
								"x" => $block[2][0],
								"y" => $block[2][1] - 1,
								"z" => $block[2][2],
								"type" => BLOCK_UPDATE_NORMAL,
							));
							$this->server->schedule(10, array($this, "blockScheduler"), array(
								"x" => $block[2][0],
								"y" => $block[2][1],
								"z" => $block[2][2],
								"type" => BLOCK_UPDATE_NORMAL,
							));
							$this->server->api->level->setBlock($block[2][0], $block[2][1], $block[2][2], $block[0], $block[1], false);
						}
					}
				}				
				break;
			case 10:
			case 11:
				if(!$this->flowLavaOn($block, 0) or $block[0] === 11){
					$this->flowLavaOn($block, 2);
					$this->flowLavaOn($block, 3);
					$this->flowLavaOn($block, 4);
					$this->flowLavaOn($block, 5);
				}
				if($block[0] === 10){
					$drained = true;
					$level = $block[1] & 0x07;
					$up = $this->server->api->level->getBlockFace($block, BlockFace::UP);
					if($up[0] === 10 or $up[0] === 11){
						$drained = false;
					}else{
						$b = $this->server->api->level->getBlockFace($block, BlockFace::NORTH);
						if($b[0] === 11 or ($b[0] === 10 and ($b[1] & 0x08) === 0 and ($b[1] & 0x07) < $level)){
							$drained = false;
						}else{
							$b = $this->server->api->level->getBlockFace($block, BlockFace::SOUTH);
							if($b[0] === 11 or ($b[0] === 10 and ($b[1] & 0x08) === 0 and ($b[1] & 0x07) < $level)){
								$drained = false;
							}else{
								$b = $this->server->api->level->getBlockFace($block, BlockFace::EAST);
								if($b[0] === 11 or ($b[0] === 10 and ($b[1] & 0x08) === 0 and ($b[1] & 0x07) < $level)){
									$drained = false;
								}else{
									$b = $this->server->api->level->getBlockFace($block, BlockFace::WEST);
									if($b[0] === 11 or ($b[0] === 10 and ($b[1] & 0x08) === 0 and ($b[1] & 0x07) < $level)){
										$drained = false;
									}
								}
							}
						}
					}
					if($drained === true){
						++$level;
						if($level > 0x07){
							$this->server->schedule(20, array($this, "blockScheduler"), array(
								"x" => $block[2][0] + 1,
								"y" => $block[2][1],
								"z" => $block[2][2],
								"type" => BLOCK_UPDATE_NORMAL,
							));
							$this->server->schedule(20, array($this, "blockScheduler"), array(
								"x" => $block[2][0] - 1,
								"y" => $block[2][1],
								"z" => $block[2][2],
								"type" => BLOCK_UPDATE_NORMAL,
							));
							$this->server->schedule(20, array($this, "blockScheduler"), array(
								"x" => $block[2][0],
								"y" => $block[2][1],
								"z" => $block[2][2] + 1,
								"type" => BLOCK_UPDATE_NORMAL,
							));
							$this->server->schedule(20, array($this, "blockScheduler"), array(
								"x" => $block[2][0],
								"y" => $block[2][1],
								"z" => $block[2][2] - 1,
								"type" => BLOCK_UPDATE_NORMAL,
							));
							$this->server->schedule(20, array($this, "blockScheduler"), array(
								"x" => $block[2][0],
								"y" => $block[2][1] - 1,
								"z" => $block[2][2],
								"type" => BLOCK_UPDATE_NORMAL,
							));
							$this->server->api->level->setBlock($block[2][0], $block[2][1], $block[2][2], 0, 0, false);						
						}else{
							$block[1] = ($block[1] & 0x08) | $level;
							$this->server->schedule(20, array($this, "blockScheduler"), array(
								"x" => $block[2][0] + 1,
								"y" => $block[2][1],
								"z" => $block[2][2],
								"type" => BLOCK_UPDATE_NORMAL,
							));
							$this->server->schedule(20, array($this, "blockScheduler"), array(
								"x" => $block[2][0] - 1,
								"y" => $block[2][1],
								"z" => $block[2][2],
								"type" => BLOCK_UPDATE_NORMAL,
							));
							$this->server->schedule(20, array($this, "blockScheduler"), array(
								"x" => $block[2][0],
								"y" => $block[2][1],
								"z" => $block[2][2] + 1,
								"type" => BLOCK_UPDATE_NORMAL,
							));
							$this->server->schedule(20, array($this, "blockScheduler"), array(
								"x" => $block[2][0],
								"y" => $block[2][1],
								"z" => $block[2][2] - 1,
								"type" => BLOCK_UPDATE_NORMAL,
							));
							$this->server->schedule(20, array($this, "blockScheduler"), array(
								"x" => $block[2][0],
								"y" => $block[2][1] - 1,
								"z" => $block[2][2],
								"type" => BLOCK_UPDATE_NORMAL,
							));
							$this->server->schedule(20, array($this, "blockScheduler"), array(
								"x" => $block[2][0],
								"y" => $block[2][1],
								"z" => $block[2][2],
								"type" => BLOCK_UPDATE_NORMAL,
							));
							$this->server->api->level->setBlock($block[2][0], $block[2][1], $block[2][2], $block[0], $block[1], false);
						}
					}
				}
				
				break;
			case 74:
				if($type === BLOCK_UPDATE_SCHEDULED or $type === BLOCK_UPDATE_RANDOM){
					$changed = true;
					$this->server->api->level->setBlock($x, $y, $z, 73, $block[1], false);
					$type = BLOCK_UPDATE_WEAK;
				}
				break;
			case 73:
				if($type === BLOCK_UPDATE_NORMAL){
					$changed = true;
					$this->server->api->level->setBlock($x, $y, $z, 74, $block[1], false);
					$this->server->schedule(mt_rand(40, 100), array($this, "blockScheduler"), array(
						"x" => $x,
						"y" => $y,
						"z" => $z,
					));
					$type = BLOCK_UPDATE_WEAK;
				}
				break;
		}
		if($type === BLOCK_TYPE_SCHEDULED){
			$type = BLOCK_UPDATE_WEAK;
		}
		if($changed === true){
			$this->updateBlocksAround($x, $y, $z, $type);
		}
	}
	
	public function updateBlocksAround($x, $y, $z, $type){
		$this->updateBlock($x + 1, $y, $z, $type);
		$this->updateBlock($x, $y + 1, $z, $type);
		$this->updateBlock($x, $y, $z + 1, $type);
		$this->updateBlock($x - 1, $y, $z, $type);
		$this->updateBlock($x, $y - 1, $z, $type);
		$this->updateBlock($x, $y, $z - 1, $type);
	}
}