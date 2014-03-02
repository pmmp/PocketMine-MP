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

class HumanEntity extends CreatureEntity implements ProjectileSourceEntity{
	
	protected $nameTag;
	
	protected function initEntity(){
		if(isset($this->namedtag->NameTag)){
			$this->nameTag = $this->namedtag->NameTag;
		}
		$this->height = 1.8; //Or 1.62?
		$this->width = 0.6;
	}
	
	public function spawnTo(Player $player){
		if($player !== $this and !isset($this->hasSpawned[$player->getID()])){
			$this->hasSpawned[$player->getID()] = $player;

			$pk = new AddPlayerPacket;
			$pk->clientID = 0;
			$pk->username = $this->nameTag;
			$pk->eid = $this->id;
			$pk->x = $this->x;
			$pk->y = $this->y;
			$pk->z = $this->z;
			$pk->yaw = 0;
			$pk->pitch = 0;
			$pk->unknown1 = 0;
			$pk->unknown2 = 0;
			$pk->metadata = $this->getMetadata();
			$player->dataPacket($pk);

			$pk = new SetEntityMotionPacket;
			$pk->eid = $this->id;
			$pk->speedX = $this->motionX;
			$pk->speedY = $this->motionY;
			$pk->speedZ = $this->motionZ;
			$player->dataPacket($pk);
			
			/*
			$pk = new PlayerEquipmentPacket;
			$pk->eid = $this->id;
			$pk->item = $this->player->getSlot($this->player->slot)->getID();
			$pk->meta = $this->player->getSlot($this->player->slot)->getMetadata();
			$pk->slot = 0;
			$player->dataPacket($pk);*/
			
			//$this->sendEquipment($player);
			
			//$this->sendArmor($player);
		}
	}
	
	public function despawnFrom(Player $player){
		if(isset($this->hasSpawned[$player->getID()])){
			$pk = new RemovePlayerPacket;
			$pk->eid = $this->id;
			$pk->clientID = 0;
			$player->dataPacket($pk);
			unset($this->hasSpawned[$player->getID()]);
		}
	}
	
	public function getMetadata(){ //TODO
		$flags = 0;
		$flags |= $this->fireTicks > 0 ? 1:0;
		//$flags |= ($this->crouched === true ? 0b10:0) << 1;
		//$flags |= ($this->inAction === true ? 0b10000:0);
		$d = array(
			0 => array("type" => 0, "value" => $flags),
			1 => array("type" => 1, "value" => $this->airTicks),
			16 => array("type" => 0, "value" => 0),
			17 => array("type" => 6, "value" => array(0, 0, 0)),
		);
		/*if($this->class === ENTITY_MOB and $this->type === MOB_SHEEP){
			if(!isset($this->data["Sheared"])){
				$this->data["Sheared"] = 0;
				$this->data["Color"] = mt_rand(0,15);
			}
			$d[16]["value"] = (($this->data["Sheared"] == 1 ? 1:0) << 4) | ($this->data["Color"] & 0x0F);
		}elseif($this->type === OBJECT_PRIMEDTNT){
			$d[16]["value"] = (int) max(0, $this->data["fuse"] - (microtime(true) - $this->spawntime) * 20);
		}elseif($this->class === ENTITY_PLAYER){
			if($this->player->isSleeping !== false){
				$d[16]["value"] = 2;
				$d[17]["value"] = array($this->player->isSleeping->x, $this->player->isSleeping->y, $this->player->isSleeping->z);
			}
		}*/
		return $d;
	}
	
	public function attack($damage, $source = "generic"){
	
	}
	
	public function heal($amount, $source = "generic"){
	
	}
}