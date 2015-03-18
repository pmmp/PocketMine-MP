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

namespace pocketmine\level\particle;

use pocketmine\entity\Entity;
use pocketmine\entity\Wolf;
use pocketmine\math\Vector3;
use pocketmine\network\protocol\AddMobPacket;
use pocketmine\network\protocol\EntityEventPacket;
use pocketmine\network\protocol\RemoveEntityPacket;

class HeartParticle extends Particle{
	//TODO: HACK!
	//TODO: needs more testing!

	public function __construct(Vector3 $pos){
		parent::__construct($pos->x, $pos->y, $pos->z);
	}
	
	public function encode(){
		$entityId = bcadd("1095216660480", mt_rand(0, 0x7fffffff)); //No conflict with other things
		$pk = new AddMobPacket();
		$pk->eid = $entityId;
		$pk->type = Wolf::NETWORK_ID;
		$pk->x = $this->x;
		$pk->y = $this->y - 1;
		$pk->z = $this->z;
		$pk->pitch = 0;
		$pk->yaw = 0;
		$pk->metadata = [
			Entity::DATA_FLAGS => [Entity::DATA_TYPE_BYTE, 1 << Entity::DATA_FLAG_INVISIBLE],
			Entity::DATA_AIR => [Entity::DATA_TYPE_SHORT, 300]
		];

		$pk2 = new EntityEventPacket();
		$pk2->eid = $entityId;
		$pk2->event = 7;

		$pk3 = new RemoveEntityPacket();
		$pk3->eid = $entityId;
		
		return [$pk, $pk2, $pk3];
	}
}
