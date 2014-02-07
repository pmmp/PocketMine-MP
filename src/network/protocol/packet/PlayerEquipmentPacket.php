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

class PlayerEquipmentPacket extends RakNetDataPacket{
	public $eid;
	public $item;
	public $meta;
	public $slot;
	
	public function pid(){
		return ProtocolInfo::PLAYER_EQUIPMENT_PACKET;
	}
	
	public function decode(){
		$this->eid = $this->getInt();
		$this->item = $this->getShort();
		$this->meta = $this->getShort();
		$this->slot = $this->getByte();
	}
	
	public function encode(){
		$this->reset();
		$this->putInt($this->eid);
		$this->putShort($this->item);
		$this->putShort($this->meta);
		$this->putByte($this->slot);
	}

}