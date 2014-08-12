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

use pocketmine\utils\Binary;

class AddMobPacket extends DataPacket{
	public $eid;
	public $type;
	public $x;
	public $y;
	public $z;
	public $pitch;
	public $yaw;
	public $metadata;

	public function pid(){
		return Info::ADD_MOB_PACKET;
	}

	public function decode(){

	}

	public function encode(){
		$this->reset();
		$this->putInt($this->eid);
		$this->putInt($this->type);
		$this->putInt($this->x);
		$this->putInt($this->y);
		$this->putInt($this->z);
		$this->putByte(floor($this->yaw * (256 / 360)));
		$this->putByte(floor($this->pitch * (256 / 360)));
		$this->put(Binary::writeMetadata($this->metadata));
	}

}
