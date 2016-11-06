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

#ifndef COMPILE
use pocketmine\utils\Binary;

#endif

class AddEntityPacket extends DataPacket{
	const NETWORK_ID = Info::ADD_ENTITY_PACKET;

	public $eid;
	public $type;
	public $x;
	public $y;
	public $z;
	public $speedX;
	public $speedY;
	public $speedZ;
	public $yaw;
	public $pitch;
	public $modifiers;
	public $metadata = [];
	public $links = [];

	public function decode(){

	}

	public function encode(){
		$this->reset();
		$this->putEntityId($this->eid); //EntityUniqueID - TODO: verify this
		$this->putEntityId($this->eid);
		$this->putUnsignedVarInt($this->type);
		$this->putVector3f($this->x, $this->y, $this->z);
		$this->putVector3f($this->speedX, $this->speedY, $this->speedZ);
		$this->putLFloat($this->yaw * (256 / 360));
		$this->putLFloat($this->pitch * (256 / 360));
		$this->putUnsignedVarInt($this->modifiers); //attributes?
		$meta = Binary::writeMetadata($this->metadata);
		$this->put($meta);
		$this->putUnsignedVarInt(count($this->links));
		foreach($this->links as $link){
			$this->putEntityId($link[0]);
			$this->putEntityId($link[1]);
			$this->putByte($link[2]);
		}
	}

}
