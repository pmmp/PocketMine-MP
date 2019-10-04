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

final class CommandExecutedEvent implements EventData{
	/** @var int */
	public $successCount;
	/** @var int */
	public $errorCount;
	/** @var string */
	public $commandName;
	/** @var string */
	public $errorList;

	public static function id() : int{
		return EventPacket::TYPE_COMMANED_EXECUTED;
	}

	public function read(NetworkBinaryStream $in) : void{
		$this->successCount = $in->getVarInt();
		$this->errorCount = $in->getVarInt();
		$this->commandName = $in->getString();
		$this->errorList = $in->getString();
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putVarInt($this->successCount);
		$out->putVarInt($this->errorCount);
		$out->putString($this->commandName);
		$out->putString($this->errorList);
	}

	public function equals(EventData $other) : bool{
		return $other instanceof $this and $other->successCount === $this->successCount and $other->errorCount === $this->errorCount and $other->commandName === $this->commandName and $other->errorList === $this->errorList;
	}
}
