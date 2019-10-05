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

namespace pocketmine\network\mcpe\protocol\types\event;

use pocketmine\network\mcpe\protocol\EventPacket;
use pocketmine\network\mcpe\serializer\NetworkBinaryStream;

final class AgentCommandEventData implements EventData{
	/** @var int */
	public $varint1;
	/** @var int */
	public $varint2;
	/** @var string */
	public $string1;
	/** @var string */
	public $string2;
	/** @var string */
	public $string3;

	public function id() : int{
		return EventPacket::TYPE_AGENT_COMMAND;
	}

	public function read(NetworkBinaryStream $in) : void{
		$this->varint1 = $in->getVarInt(); // Unknown, 0 - 3
		$this->varint2 = $in->getVarInt(); // Unknown, v9 != -1
		$this->string1 = $in->getString(); // Unknown, Json
		$this->string2 = $in->getString(); // Unknown, Json
		$this->string3 = $in->getString(); // Unknown, Json, maybe named "Result". Contains "commandName"
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putVarInt($this->varint1);
		$out->putVarInt($this->varint2);
		$out->putString($this->string1);
		$out->putString($this->string2);
		$out->putString($this->string3);
	}
}
