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

namespace pocketmine\network\mcpe\protocol\serializer;

use pocketmine\nbt\BaseNbtSerializer;
use function count;
use function strlen;

class NetworkNbtSerializer extends BaseNbtSerializer{

	public function readShort() : int{
		return $this->buffer->getLShort();
	}

	public function readSignedShort() : int{
		return $this->buffer->getSignedLShort();
	}

	public function writeShort(int $v) : void{
		$this->buffer->putLShort($v);
	}

	public function readInt() : int{
		return $this->buffer->getVarInt();
	}

	public function writeInt(int $v) : void{
		$this->buffer->putVarInt($v);
	}

	public function readLong() : int{
		return $this->buffer->getVarLong();
	}

	public function writeLong(int $v) : void{
		$this->buffer->putVarLong($v);
	}

	public function readString() : string{
		return $this->buffer->get(self::checkReadStringLength($this->buffer->getUnsignedVarInt()));
	}

	public function writeString(string $v) : void{
		$this->buffer->putUnsignedVarInt(self::checkWriteStringLength(strlen($v)));
		$this->buffer->put($v);
	}

	public function readFloat() : float{
		return $this->buffer->getLFloat();
	}

	public function writeFloat(float $v) : void{
		$this->buffer->putLFloat($v);
	}

	public function readDouble() : float{
		return $this->buffer->getLDouble();
	}

	public function writeDouble(float $v) : void{
		$this->buffer->putLDouble($v);
	}

	public function readIntArray() : array{
		$len = $this->readInt(); //varint
		$ret = [];
		for($i = 0; $i < $len; ++$i){
			$ret[] = $this->readInt(); //varint
		}

		return $ret;
	}

	public function writeIntArray(array $array) : void{
		$this->writeInt(count($array)); //varint
		foreach($array as $v){
			$this->writeInt($v); //varint
		}
	}
}
