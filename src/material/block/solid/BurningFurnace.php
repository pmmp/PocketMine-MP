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
	
	public function onBreak(Item $item, Player $player){
		$this->level->setBlock($this, new AirBlock(), true, true);
		return true;
	}

	public function onActivate(Item $item, Player $player){

		$server = ServerAPI::request();
		$t = $server->api->tile->get($this);
		$furnace = false;
		if($t !== false){
			$furnace = $t;
		}else{
			$furnace = $server->api->tile->add($this->level, TILE_FURNACE, $this->x, $this->y, $this->z, array(
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
		$player->windowCnt = $id = max(2, $player->windowCnt % 16);
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
			}else{
				$slots[] = BlockAPI::getItem(AIR, 0, 0);
			}
		}
		$player->dataPacket(MC_CONTAINER_SET_CONTENT, array(
			"windowid" => $id,
			"count" => count($slots),
			"slots" => $slots
		));
		return true;
	}
	
	public function getBreakTime(Item $item, Player $player){
		if(($player->gamemode & 0x01) === 0x01){
			return 0.20;
		}		
		switch($item->isPickaxe()){
			case 5:
				return 0.7;
			case 4:
				return 0.9;
			case 3:
				return 1.35;
			case 2:
				return 0.45;
			case 1:
				return 2.65;
			default:
				return 17.5;
		}
	}
	
	public function getDrops(Item $item, Player $player){
		$drops = array();
		if($item->isPickaxe() >= 1){
			$drops[] = array(FURNACE, 0, 1);
		}
		$t = ServerAPI::request()->api->tile->get($this);
		if($t !== false and $t->class === TILE_FURNACE){
			for($s = 0; $s < FURNACE_SLOTS; ++$s){
				$slot = $t->getSlot($s);
				if($slot->getID() > AIR and $slot->count > 0){
					$drops[] = array($slot->getID(), $slot->getMetadata(), $slot->count);
				}
			}
		}
		return $drops;
	}
}