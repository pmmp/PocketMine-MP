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

namespace pocketmine\nbt;

#ifndef COMPILE
use pocketmine\utils\Binary;
#endif

#include <rules/NBT.h>

class BigEndianNBTStream extends NBTStream{

	public function getShort() : int{
		return Binary::readShort($this->get(2));
	}

	public function getSignedShort() : int{
		return Binary::readSignedShort($this->get(2));
	}

	public function putShort(int $v) : void{
		$this->buffer .= Binary::writeShort($v);
	}

	public function getInt() : int{
		return Binary::readInt($this->get(4));
	}

	public function putInt(int $v) : void{
		$this->buffer .= Binary::writeInt($v);
	}

	public function getLong() : int{
		return Binary::readLong($this->get(8));
	}

	public function putLong(int $v) : void{
		$this->buffer .= Binary::writeLong($v);
	}

	public function getFloat() : float{
		return Binary::readFloat($this->get(4));
	}

	public function putFloat(float $v) : void{
		$this->buffer .= Binary::writeFloat($v);
	}

	public function getDouble() : float{
		return Binary::readDouble($this->get(8));
	}

	public function putDouble(float $v) : void{
		$this->buffer .= Binary::writeDouble($v);
	}

	public function getIntArray() : array{
		$len = $this->getInt();
		return array_values(unpack("N*", $this->get($len * 4)));
	}

	public function putIntArray(array $array) : void{
		$this->putInt(count($array));
		$this->put(pack("N*", ...$array));
	}
}
