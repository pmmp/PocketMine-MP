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
use pocketmine\math\Vector3;
use pocketmine\network\protocol\AddPlayerPacket;
use pocketmine\network\protocol\RemovePlayerPacket;

class FloatingTextParticle extends Particle{
	//TODO: HACK!

	protected $text;
	protected $title;
	protected $entityId;
	protected $invisible = false;

	public function __construct(Vector3 $pos, $text, $title = ""){
		parent::__construct($pos->x, $pos->y, $pos->z);
		$this->text = $text;
		$this->title = $title;
	}

	public function setText($text){
		$this->text = $text;
	}

	public function setTitle($title){
		$this->title = $title;
	}
	
	public function isInvisible(){
		return $this->invisible;
	}
	
	public function setInvisible($value = true){
		$this->invisible = (bool) $value;
	}

	public function encode(){
		$p = [];

		if($this->entityId === null){
			$this->entityId = bcadd("1095216660480", mt_rand(0, 0x7fffffff)); //No conflict with other things
		}else{
			$pk0 = new RemovePlayerPacket();
			$pk0->eid = $this->entityId;
			$pk0->clientID = $this->entityId;

			$p[] = $pk0;
		}

		if(!$this->invisible){
			
			$pk = new AddPlayerPacket();
			$pk->eid = $this->entityId;
			$pk->username = $this->title . ($this->text !== "" ? "\n" . $this->text : "");
			$pk->clientID = $this->entityId;
			$pk->x = $this->x;
			$pk->y = $this->y - 2.5;
			$pk->z = $this->z;
			$pk->yaw = 0;
			$pk->pitch = 0;
			$pk->item = 0;
			$pk->meta = 0;
			$pk->slim = false;
			$pk->skin = str_repeat("\x00", 64 * 32 * 4);
			$pk->metadata = [
				Entity::DATA_FLAGS => [Entity::DATA_TYPE_BYTE, 1 << Entity::DATA_FLAG_INVISIBLE],
				Entity::DATA_AIR => [Entity::DATA_TYPE_SHORT, 300],
				Entity::DATA_SHOW_NAMETAG => [Entity::DATA_TYPE_BYTE, 1],
				Entity::DATA_NO_AI => [Entity::DATA_TYPE_BYTE, 1]
            ];

			$p[] = $pk;
		}
		
		return $p;
	}
}
