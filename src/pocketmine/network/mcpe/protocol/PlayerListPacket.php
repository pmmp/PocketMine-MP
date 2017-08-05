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

declare(strict_types=1);

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>


use pocketmine\network\mcpe\NetworkSession;

class PlayerListPacket extends DataPacket{
	const NETWORK_ID = ProtocolInfo::PLAYER_LIST_PACKET;

	const TYPE_ADD = 0;
	const TYPE_REMOVE = 1;

	//REMOVE: UUID, ADD: UUID, entity id, name, skinId, skin, geometric model, geometry data
	/** @var array[] */
	public $entries = [];
	public $type;

	public function clean(){
		$this->entries = [];
		return parent::clean();
	}

	protected function decodePayload(){
		$this->type = $this->getByte();
		$count = $this->getUnsignedVarInt();
		for($i = 0; $i < $count; ++$i){
			if($this->type === self::TYPE_ADD){
				$this->entries[$i][0] = $this->getUUID();
				$this->entries[$i][1] = $this->getEntityUniqueId();
				$this->entries[$i][2] = $this->getString(); //name
				$this->entries[$i][3] = $this->getString(); //skin id
				$this->entries[$i][4] = $this->getString(); //skin data
				$this->entries[$i][5] = $this->getString(); //geometric model
				$this->entries[$i][6] = $this->getString(); //geometry data (json)
				$this->entries[$i][7] = $this->getString(); //???
			}else{
				$this->entries[$i][0] = $this->getUUID();
			}
		}
	}

	protected function encodePayload(){
		$this->putByte($this->type);
		$this->putUnsignedVarInt(count($this->entries));
		foreach($this->entries as $d){
			if($this->type === self::TYPE_ADD){
				$this->putUUID($d[0]);
				$this->putEntityUniqueId($d[1]);
				$this->putString($d[2]); //name
				$this->putString($d[3]); //skin id
				$this->putString($d[4]); //skin data
				$this->putString($d[5] ?? ""); //geometric model
				$this->putString($d[6] ?? ""); //geometry data (json)
				$this->putString($d[7] ?? ""); //???
			}else{
				$this->putUUID($d[0]);
			}
		}
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handlePlayerList($this);
	}

}
