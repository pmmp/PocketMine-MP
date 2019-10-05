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

final class PetDiedEventData implements EventData{
	/** @var bool */
	public $unknownBool; // If true - PetDeathContext=2
	/** @var int */
	public $entityUniqueId;
	/** @var int */
	public $entityRuntimeId;
	/** @var int */
	public $petType;
	/** @var int */
	public $deathMethodType;

	public static function id() : int{
		return EventPacket::TYPE_MOB_KILLED;
	}

	public function read(NetworkBinaryStream $in) : void{
		$this->unknownBool = $in->getBool();
		$this->entityUniqueId = $in->getEntityUniqueId();
		$this->entityRuntimeId = $in->getEntityUniqueId(); // Nice
		$this->petType = $in->getVarInt();
		$this->deathMethodType = $in->getVarInt();
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putBool($this->unknownBool);
		$out->putEntityUniqueId($this->entityUniqueId);
		$out->putEntityUniqueId($this->entityRuntimeId);
		$out->putVarInt($this->petType);
		$out->putVarInt($this->deathMethodType);
	}

	public function equals(EventData $other) : bool{
		return $other instanceof $this and $other->unknownBool === $this->unknownBool and $other->entityUniqueId === $this->entityUniqueId and $other->entityRuntimeId === $this->entityRuntimeId and $other->petType === $this->petType and $other->deathMethodType === $this->deathMethodType;
	}
}
