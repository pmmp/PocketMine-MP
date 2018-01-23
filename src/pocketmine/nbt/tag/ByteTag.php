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

namespace pocketmine\nbt\tag;

use pocketmine\nbt\NBT;
use pocketmine\nbt\NBTStream;

#include <rules/NBT.h>

class ByteTag extends NamedTag{

	/**
	 * ByteTag constructor.
	 *
	 * @param string $name
	 * @param int    $value
	 */
	public function __construct(string $name = "", int $value = 0){
		parent::__construct($name, $value);
	}

	public function getType() : int{
		return NBT::TAG_Byte;
	}

	public function read(NBTStream $nbt) : void{
		$this->value = $nbt->getSignedByte();
	}

	public function write(NBTStream $nbt) : void{
		$nbt->putByte($this->value);
	}

	/**
	 * @return int
	 */
	public function &getValue() : int{
		return parent::getValue();
	}

	/**
	 * @param int $value
	 *
	 * @throws \TypeError
	 */
	public function setValue($value) : void{
		if(!is_int($value)){
			throw new \TypeError("ByteTag value must be of type int, " . gettype($value) . " given");
		}elseif($value < -(2 ** 7) or $value > ((2 ** 7) - 1)){
			throw new \InvalidArgumentException("Value $value is too large!");
		}
		parent::setValue($value);
	}
}
