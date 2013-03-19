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

class ChestBlock extends SolidBlock{
	public function __construct($meta = 0){
		parent::__construct(CHEST, $meta, "Chest");
		$this->isActivable = true;
	}
	public function place(BlockAPI $level, Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		if($block->inWorld === true){
			$block0 = $level->getBlockFace($block, 2);
			$block1 = $level->getBlockFace($block, 3);
			$block2 = $level->getBlockFace($block, 4);
			$block3 = $level->getBlockFace($block, 5);
			if($block0->getID() !== CHEST and $block1->getID() !== CHEST and $block2->getID() !== CHEST and $block3->getID() !== CHEST){
				$faces = array(
					0 => 4,
					1 => 2,
					2 => 5,
					3 => 3,
				);
				$level->setBlock($block, $this->id, $faces[$player->entity->getDirection()]);
				$server = ServerAPI::request();
				$server->api->tileentity->add(TILE_CHEST, $this->x, $this->y, $this->z, array(
					"Items" => array(),
					"id" => TILE_CHEST,
					"x" => $this->x,
					"y" => $this->y,
					"z" => $this->z
				));
				return true;
			}
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
						if($ts->class === TILE_CHEST){
							$server->api->tileentity->remove($ts->id);
						}
					}
				}elseif($t->class === TILE_CHEST){
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
		$chest = false;
		if($t !== false){
			if(is_array($t)){
				$chest = array_shift($t);
			}else{
				$chest = $t;
			}
		}else{
			$chest = $server->api->tileentity->add(TILE_CHEST, $this->x, $this->y, $this->z, array(
				"Items" => array(),
				"id" => TILE_CHEST,
				"x" => $this->x,
				"y" => $this->y,
				"z" => $this->z			
			));
		}
		
		if($chest->class !== TILE_CHEST){
			return false;
		}
		$id = $player->windowCnt = $player->windowCnt++ % 255;
		$player->windows[$id] = $chest;
		$player->dataPacket(MC_CONTAINER_OPEN, array(
			"windowid" => $id,
			"type" => WINDOW_CHEST,
			"slots" => 27,
			"title" => "Chest",
		));
		for($s = 0; $s < 3; ++$s){
			$slot = $chest->getSlot($s);
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
		return array(
			array($this->id, 0, 1),
		);
	}	
}