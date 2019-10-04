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

final class MobBornEvent implements EventData{
	/** @var int */
	public $babyType;
	/** @var int */
	public $babyVariant;
	/** @var int */
	public $babyColor;

	public static function id() : int{
		return EventPacket::TYPE_MOB_BORN;
	}

	public function read(NetworkBinaryStream $in) : void{
		$this->babyType = $in->getVarInt();
		$this->babyVariant = $in->getVarInt();
		$this->babyColor = $in->getByte();
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putVarInt($this->babyType);
		$out->putVarInt($this->babyVariant);
		$out->putByte($this->babyColor);
	}

	public function equals(EventData $other) : bool{
		return $other instanceof $this and $other->babyType === $this->babyType and $other->babyVariant === $this->babyVariant and $other->babyColor === $this->babyColor;
	}
}
