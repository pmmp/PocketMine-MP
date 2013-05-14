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


class BurningFurnaceBlock extends SolidBlock{
	public function __construct($meta = 0){
		parent::__construct(BURNING_FURNACE, $meta, "Burning Furnace");
		$this->isActivable = true;
	}

	public function place(Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		if($block->inWorld === true){
			$faces = array(
				0 => 4,
				1 => 2,
				2 => 5,
				3 => 3,
			);
			$this->meta = $faces[$player->entity->getDirection()];
			$this->level->setBlock($block, $this);
			return true;
		}
		return false;
	}
	
	public function onBreak(Item $item, Player $player){
			$server = ServerAPI::request();
			$t = $server->api->tileentity->get($this);
			if($t !== false){
				if(is_array($t)){
					foreach($t as $ts){
						if($ts->class === TILE_FURNACE){
							$server->api->tileentity->remove($ts->id);
						}
					}
				}elseif($t->class === TILE_FURNACE){
					$server->api->tileentity->remove($t->id);
				}
			}
			$this->level->setBlock($this, new AirBlock());
			return true;
	}

	public function onActivate(Item $item, Player $player){

		$server = ServerAPI::request();
		$t = $server->api->tileentity->get($this);
		$furnace = false;
		if($t !== false){
			if(is_array($t)){
				$furnace = array_shift($t);
			}else{
				$furnace = $t;
			}
		}else{
			$furnace = $server->api->tileentity->add($this->level, TILE_FURNACE, $this->x, $this->y, $this->z, array(
				"Items" => array(),
				"id" => TILE_FURNACE,
				"x" => $this->x,
				"y" => $this->y,
				"z" => $this->z			
			));
		}
		
		if($furnace->class !== TILE_FURNACE or ($player->gamemode & 0x01) === 0x01){
			return true;
		}
		$player->windowCnt++;
		$player->windowCnt = $id = max(1, $player->windowCnt % 255);
		$player->windows[$id] = $furnace;
		$player->dataPacket(MC_CONTAINER_OPEN, array(
			"windowid" => $id,
			"type" => WINDOW_FURNACE,
			"slots" => FURNACE_SLOTS,
			"title" => "Furnace",
		));
		$slots = array();
		for($s = 0; $s < FURNACE_SLOTS; ++$s){
			$slot = $furnace->getSlot($s);
			if($slot->getID() > 0 and $slot->count > 0){
				$slots[] = $slot;
			}
		}
		$player->dataPacket(MC_CONTAINER_SET_CONTENT, array(
			"windowid" => $id,
			"count" => count($slots),
			"slots" => $slots
		));
		return true;
	}
	
	public function getDrops(Item $item, Player $player){
		if($item->isPickaxe() >= 1){
			return array(
				array(FURNACE, 0, 1),
			);
		}else{
			return array();
		}
	}
}