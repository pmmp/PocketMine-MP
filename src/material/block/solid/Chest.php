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

class ChestBlock extends TransparentBlock{
	public function __construct($meta = 0){
		parent::__construct(CHEST, $meta, "Chest");
		$this->isActivable = true;
	}
	public function place(Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
			$block0 = $this->getSide(2);
			$block1 = $this->getSide(3);
			$block2 = $this->getSide(4);
			$block3 = $this->getSide(5);
			if($block0->getID() !== CHEST and $block1->getID() !== CHEST and $block2->getID() !== CHEST and $block3->getID() !== CHEST){
				$faces = array(
					0 => 4,
					1 => 2,
					2 => 5,
					3 => 3,
				);
				$this->meta = $faces[$player->entity->getDirection()];
				$this->level->setBlock($block, $this);
				$server = ServerAPI::request();
				$server->api->tileentity->add($this->level, TILE_CHEST, $this->x, $this->y, $this->z, array(
					"Items" => array(),
					"id" => TILE_CHEST,
					"x" => $this->x,
					"y" => $this->y,
					"z" => $this->z
				));
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
						if($ts->class === TILE_CHEST){
							$server->api->tileentity->remove($ts->id);
						}
					}
				}elseif($t->class === TILE_CHEST){
					$server->api->tileentity->remove($t->id);
				}
			}
			$this->level->setBlock($this, new AirBlock());
			return true;
	}
	
	public function onActivate(Item $item, Player $player){
		$top = $this->getSide(1);
		if($top->isTransparent !== true){
			return true;
		}
	
		$server = ServerAPI::request();
		$t = $server->api->tileentity->get($this);
		$chest = false;
		if($t !== false){
			if(is_array($t)){
				$chest = array_shift($t);
			}else{
				$chest = $t;
			}
		}else{
			$chest = $server->api->tileentity->add($this->level, TILE_CHEST, $this->x, $this->y, $this->z, array(
				"Items" => array(),
				"id" => TILE_CHEST,
				"x" => $this->x,
				"y" => $this->y,
				"z" => $this->z			
			));
		}
		
		if($chest->class !== TILE_CHEST or ($player->gamemode & 0x01) === 0x01){
			return true;
		}
		$player->windowCnt++;
		$player->windowCnt = $id = max(1, $player->windowCnt % 255);
		$player->windows[$id] = $chest;
		$player->dataPacket(MC_CONTAINER_OPEN, array(
			"windowid" => $id,
			"type" => WINDOW_CHEST,
			"slots" => CHEST_SLOTS,
			"title" => "Chest",
		));
		$slots = array();
		for($s = 0; $s < CHEST_SLOTS; ++$s){
			$slot = $chest->getSlot($s);
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

	public function getDrops(Item $item, Player $player){
		return array(
			array($this->id, 0, 1),
		);
	}
}