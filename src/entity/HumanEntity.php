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
		if(isset($this->namedtag->nameTag)){
			$this->nameTag = $this->namedtag->nameTag;
		}
	}
	
	public function spawnTo(Player $player){
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
				
		/*
		$pk = new SetEntityMotionPacket;
		$pk->eid = $this->id;
		$pk->speedX = $this->velocity->x;
		$pk->speedY = $this->velocity->y;
		$pk->speedZ = $this->velocity->z;
		$player->dataPacket($pk);*/

		$this->sendMotion($player);
		
		/*
		$pk = new PlayerEquipmentPacket;
		$pk->eid = $this->id;
		$pk->item = $this->player->getSlot($this->player->slot)->getID();
		$pk->meta = $this->player->getSlot($this->player->slot)->getMetadata();
		$pk->slot = 0;
		$player->dataPacket($pk);*/
		
		$this->sendEquipment($player)
		
		$this->sendArmor($player);
	}
}