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

		$faces = array(
			0 => 4,
			1 => 2,
			2 => 5,
			3 => 3,
		);
		$facesc = array(
			2 => 4,
			3 => 2,
			4 => 5,
			5 => 3,
		);
		$chest = false;
		for($side = 2; $side <= 5; ++$side){
			$c = $this->getSide($side);
			if($c instanceof ChestBlock){
				/*if($chest !== false){ //No chests in the middle
					return false;
				}*/
				$chest = array($side, $c);
				break;
			}
		}
		
		if($chest !== false and ($chest[1]->getSide($chest[0]) instanceof ChestBlock)){ //Already double chest
			return false;
		}
		
		if($chest !== false){			
			$this->meta = $facesc[$chest[0]];
			$this->level->setBlock($chest[1], new ChestBlock($this->meta));
		}else{
			$this->meta = $faces[$player->entity->getDirection()];
		}
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
		$server->api->player->broadcastPacket($server->api->player->getAll($this->level), MC_TILE_EVENT, array(
			"x" => $this->x,
			"y" => $this->y,
			"z" => $this->z,
			"case1" => 1,
			"case2" => 2,
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