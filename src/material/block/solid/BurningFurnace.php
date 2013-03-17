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

	public function place(BlockAPI $level, Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		if($block->inWorld === true){
			$faces = array(
				0 => 4,
				1 => 2,
				2 => 5,
				3 => 3,
			);
			$level->setBlock($block, $this->id, $faces[$player->entity->getDirection()]);
			return true;
		}
		return false;
	}
	
	public function onBreak(BlockAPI $level, Item $item, Player $player){
		if($this->inWorld === true){
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
			$level->setBlock($this, 0, 0);
			return true;
		}
		return false;
	}

	public function onActivate(BlockAPI $level, Item $item, Player $player){
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
			$furnace = $server->api->tileentity->add(TILE_FURNACE, $this->x, $this->y, $this->z, array(
				"Items" => array(),
				"id" => TILE_FURNACE,
				"x" => $this->x,
				"y" => $this->y,
				"z" => $this->z			
			));
		}
		
		if($furnace->class !== TILE_FURNACE){
			return false;
		}
		$id = $player->windowCnt = $player->windowCnt++ % 255;
		$player->windows[$id] = $furnace;
		$player->dataPacket(MC_CONTAINER_OPEN, array(
			"windowid" => $id,
			"type" => WINDOW_FURNACE,
			"slots" => 3,
			"title" => "Furnace",
		));
		for($s = 0; $s < 3; ++$s){
			$slot = $furnace->getSlot($s);
			if($slot->getID() > 0 and $slot->count > 0){
				$player->dataPacket(MC_CONTAINER_SET_SLOT, array(
					"windowid" => $id,
					"slot" => $s,
					"block" => $slot->getID(),
					"stack" => $slot->count,
					"meta" => $slot->getMetadata(),
				));
			}
		}
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