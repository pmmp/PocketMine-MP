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


class UpdateTradePacket extends DataPacket{
	const NETWORK_ID = Info::UPDATE_TRADE_PACKET;

	//TODO: find fields
	public $byte1;
	public $byte2;
	public $varint1;
	public $varint2;
	public $bool1;
	public $eid1;
	public $eid2;
	public $string;
	public $nbt;

	public function decode(){
		$this->byte1 = $this->getByte();
		$this->byte2 = $this->getByte();
		$this->varint1 = $this->getVarInt();
		$this->varint2 = $this->getVarInt();
		$this->bool1 = $this->getBool();
		$this->eid1 = $this->getEntityId();
		$this->eid2 = $this->getEntityId();
		$this->string = $this->getString();
		$this->nbt = $this->get(true);
	}

	public function encode(){
		$this->reset();
		$this->putByte($this->byte1);
		$this->putByte($this->byte2);
		$this->putVarInt($this->varint1);
		$this->putVarInt($this->varint2);
		$this->putBool($this->bool1);
		$this->putEntityId($this->eid1); //UniqueID
		$this->putEntityId($this->eid2); //UniqueID
		$this->putString($this->string);
		$this->put($this->nbt);
	}
}