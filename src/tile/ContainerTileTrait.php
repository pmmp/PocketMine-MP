<?php

/**
 *
 *  ____            _        _   __  __ _                  __  __ ____  
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \ 
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/ 
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_| 
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 * 
 *
*/

trait ContainerTileTrait{
	public function openInventory(Player $player){
		if($this instanceof ChestTile){
			$player->windowCnt++;
			$player->windowCnt = $id = max(2, $player->windowCnt % 99);
			if(($pair = $this->getPair()) !== false){				
				if(($pair->x + ($pair->z << 13)) > ($this->x + ($this->z << 13))){ //Order them correctly
					$player->windows[$id] = array(
						$pair,
						$this
					);
				}else{
					$player->windows[$id] = array(
						$this,
						$pair
					);
				}
			}else{
				$player->windows[$id] = $this;
			}
			
			$pk = new ContainerOpenPacket;
			$pk->windowid = $id;
			$pk->type = WINDOW_CHEST;
			$pk->slots = is_array($player->windows[$id]) ? ChestTile::SLOTS << 1:ChestTile::SLOTS;
			$pk->x = $this->x;
			$pk->y = $this->y;
			$pk->z = $this->z;
			$player->dataPacket($pk);
			$slots = array();
			
			if(is_array($player->windows[$id])){
				$all = $this->level->getPlayers();
				foreach($player->windows[$id] as $ob){				
					$pk = new TileEventPacket;
					$pk->x = $ob->x;
					$pk->y = $ob->y;
					$pk->z = $ob->z;
					$pk->case1 = 1;
					$pk->case2 = 2;
					Player::broadcastPacket($all, $pk);
					for($s = 0; $s < ChestTile::SLOTS; ++$s){
						$slot = $ob->getSlot($s);
						if($slot->getID() > AIR and $slot->count > 0){
							$slots[] = $slot;
						}else{
							$slots[] = BlockAPI::getItem(AIR, 0, 0);
						}
					}
				}
			}else{
				$pk = new TileEventPacket;
				$pk->x = $this->x;
				$pk->y = $this->y;
				$pk->z = $this->z;
				$pk->case1 = 1;
				$pk->case2 = 2;
				Player::broadcastPacket($this->level->getPlayers(), $pk);
				for($s = 0; $s < ChestTile::SLOTS; ++$s){
					$slot = $this->getSlot($s);
					if($slot->getID() > AIR and $slot->count > 0){
						$slots[] = $slot;
					}else{
						$slots[] = BlockAPI::getItem(AIR, 0, 0);
					}
				}
			}
			
			$pk = new ContainerSetContentPacket;
			$pk->windowid = $id;
			$pk->slots = $slots;
			$player->dataPacket($pk);
			return true;
		}elseif($this instanceof FurnaceTile){
			$player->windowCnt++;
			$player->windowCnt = $id = max(2, $player->windowCnt % 99);
			$player->windows[$id] = $this;
			
			$pk = new ContainerOpenPacket;
			$pk->windowid = $id;
			$pk->type = WINDOW_FURNACE;
			$pk->slots = FurnaceTile::SLOTS;
			$pk->x = $this->x;
			$pk->y = $this->y;
			$pk->z = $this->z;
			$player->dataPacket($pk);
			
			$slots = array();
			for($s = 0; $s < FurnaceTile::SLOTS; ++$s){
				$slot = $this->getSlot($s);
				if($slot->getID() > AIR and $slot->count > 0){
					$slots[] = $slot;
				}else{
					$slots[] = BlockAPI::getItem(AIR, 0, 0);
				}
			}
			$pk = new ContainerSetContentPacket;
			$pk->windowid = $id;
			$pk->slots = $slots;
			$player->dataPacket($pk);
			return true;
		}
	}
	
	public function getSlotIndex($s){
		foreach($this->namedtag->Items as $i => $slot){
			if($slot->Slot === $s){
				return $i;
			}
		}
		return -1;
	}
	
	public function getSlot($s){
		$i = $this->getSlotIndex($s);
		if($i === false or $i < 0){
			return BlockAPI::getItem(AIR, 0, 0);
		}else{
			return BlockAPI::getItem($this->namedtag->Items[$i]->id, $this->namedtag->Items[$i]->Damage, $this->namedtag->Items[$i]->Count);
		}
	}
	
	public function setSlot($s, Item $item, $update = true, $offset = 0){
		$i = $this->getSlotIndex($s);
		$d = new NBTTag_Compound(false, array(
			"Count" => new NBTTag_Byte("Count", $item->count),
			"Slot" => new NBTTag_Byte("Slot", $s),
			"id" => new NBTTag_Short("id", $item->getID()),
			"Damage" => new NBTTag_Short("Damage", $item->getMetadata()),
		));
		if($i === false){
			return false;
		}elseif($item->getID() === AIR or $item->count <= 0){
			if($i >= 0){
				unset($this->namedtag->Items[$i]);
			}
		}elseif($i < 0){
			$this->namedtag->Items[] = $d;
		}else{
			$this->namedtag->Items[$i] = $d;
		}
		$this->server->api->dhandle("tile.container.slot", array(
			"tile" => $this,
			"slot" => $s,
			"offset" => $offset,
			"slotdata" => $item,
		));

		if($update === true){
			$this->scheduleUpdate();
		}
		return true;
	}
}