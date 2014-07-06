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


class SetEntityMotionPacket extends DataPacket{

	// eid, motX, motY, motZ
	/** @var array[] */
	public $entities = [];

	public function pid(){
		return Info::SET_ENTITY_MOTION_PACKET;
	}

	public function decode(){

	}

	public function encode(){
		$this->reset();
		$this->putInt(count($this->entities));
		foreach($this->entities as $d){
			$this->putInt($d[0]); //eid
			$this->putShort((int) ($d[1] * 8000)); //motX
			$this->putShort((int) ($d[2] * 8000)); //motY
			$this->putShort((int) ($d[3] * 8000)); //motZ
		}
	}

}