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

class PlayerArmorEquipmentPacket extends RakNetDataPacket{
	public $eid;
	public $slots = array();
	
	public function pid(){
		return ProtocolInfo::PLAYER_ARMOR_EQUIPMENT_PACKET;
	}
	
	public function decode(){
		$this->eid = $this->getInt();		
		$this->slots[0] = $this->getByte();
		$this->slots[1] = $this->getByte();
		$this->slots[2] = $this->getByte();
		$this->slots[3] = $this->getByte();
	}
	
	public function encode(){
		$this->reset();
		$this->putInt($this->eid);
		$this->putByte($this->slots[0]);
		$this->putByte($this->slots[1]);
		$this->putByte($this->slots[2]);
		$this->putByte($this->slots[3]);
	}

}