<?php

/*
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

namespace pocketmine\network\protocol;

#include <rules/DataPacket.h>


class MobArmorEquipmentPacket extends DataPacket{
	const NETWORK_ID = Info::MOB_ARMOR_EQUIPMENT_PACKET;

	public $eid;
	public $slots = [];

	public function decode(){
		$this->eid = $this->getEntityId();
		$this->slots[0] = $this->getSlot();
		$this->slots[1] = $this->getSlot();
		$this->slots[2] = $this->getSlot();
		$this->slots[3] = $this->getSlot();
	}

	public function encode(){
		$this->reset();
		$this->putEntityId($this->eid);
		$this->putSlot($this->slots[0]);
		$this->putSlot($this->slots[1]);
		$this->putSlot($this->slots[2]);
		$this->putSlot($this->slots[3]);
	}

}
