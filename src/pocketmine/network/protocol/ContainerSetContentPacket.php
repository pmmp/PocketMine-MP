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


class ContainerSetContentPacket extends DataPacket{
	const NETWORK_ID = Info::CONTAINER_SET_CONTENT_PACKET;

	const SPECIAL_INVENTORY = 0;
	const SPECIAL_ARMOR = 0x78;
	const SPECIAL_CREATIVE = 0x79;
	const SPECIAL_HOTBAR = 0x7a;

	public $windowid;
	public $slots = [];
	public $hotbar = [];

	public function clean(){
		$this->slots = [];
		$this->hotbar = [];
		return parent::clean();
	}

	public function decode(){
		$this->windowid = $this->getByte();
		$count = $this->getUnsignedVarInt();
		for($s = 0; $s < $count and !$this->feof(); ++$s){
			$this->slots[$s] = $this->getSlot();
		}
		if($this->windowid === self::SPECIAL_INVENTORY){
			$count = $this->getUnsignedVarInt();
			for($s = 0; $s < $count and !$this->feof(); ++$s){
				$this->hotbar[$s] = $this->getVarInt();
			}
		}
	}

	public function encode(){
		$this->reset();
		$this->putByte($this->windowid);
		$this->putUnsignedVarInt(count($this->slots));
		foreach($this->slots as $slot){
			$this->putSlot($slot);
		}
		if($this->windowid === self::SPECIAL_INVENTORY and count($this->hotbar) > 0){
			$this->putUnsignedVarInt(count($this->hotbar));
			foreach($this->hotbar as $slot){
				$this->putVarInt($slot);
			}
		}else{
			$this->putUnsignedVarInt(0);
		}
	}

}