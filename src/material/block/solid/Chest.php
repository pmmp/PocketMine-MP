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
				$server->api->tile->add($this->level, TILE_CHEST, $this->x, $this->y, $this->z, array(
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
			$this->level->setBlock($this, new AirBlock(), true, true);
			return true;
	}
	
	public function onActivate(Item $item, Player $player){
		$top = $this->getSide(1);
		if($top->isTransparent !== true){
			return true;
		}
	
		$server = ServerAPI::request();
		$t = $server->api->tile->get($this);
		$chest = false;
		if($t !== false){
			$chest = $t;
		}else{
			$chest = $server->api->tile->add($this->level, TILE_CHEST, $this->x, $this->y, $this->z, array(
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
		$player->windowCnt = $id = max(2, $player->windowCnt % 16);
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
			if($slot->getID() > AIR and $slot->count > 0){
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
		$drops = array(
			array($this->id, 0, 1),
		);
		$t = ServerAPI::request()->api->tile->get($this);
		if($t !== false and $t->class === TILE_CHEST){
			for($s = 0; $s < CHEST_SLOTS; ++$s){
				$slot = $t->getSlot($s);
				if($slot->getID() > AIR and $slot->count > 0){
					$drops[] = array($slot->getID(), $slot->getMetadata(), $slot->count);
				}
			}
		}
		return $drops;
	}
}