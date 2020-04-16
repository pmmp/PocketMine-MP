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
	public $result;
	/** @var int */
	public $resultNumber = -1;
	/** @var string */
	public $commandName;
	/** @var string */
	public $resultKey;
	/** @var string */
	public $resultString;

	public function id() : int{
		return EventPacket::TYPE_AGENT_COMMAND;
	}

	public function read(NetworkBinaryStream $in) : void{
		$this->result = $in->getVarInt();
		$this->resultNumber = $in->getVarInt();
		$this->commandName = $in->getString();
		$this->resultKey = $in->getString();
		$this->resultString = $in->getString();
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putVarInt($this->result);
		$out->putVarInt($this->resultNumber);
		$out->putString($this->commandName);
		$out->putString($this->resultKey);
		$out->putString($this->resultString);
	}
}
