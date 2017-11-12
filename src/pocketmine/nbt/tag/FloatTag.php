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

#include <rules/NBT.h>

class FloatTag extends NamedTag{

	/**
	 * FloatTag constructor.
	 *
	 * @param string $name
	 * @param float  $value
	 */
	public function __construct(string $name = "", float $value = 0.0){
		parent::__construct($name, $value);
	}

	public function getType() : int{
		return NBT::TAG_Float;
	}

	public function read(NBT $nbt, bool $network = false) : void{
		$this->value = $nbt->getFloat();
	}

	public function write(NBT $nbt, bool $network = false) : void{
		$nbt->putFloat($this->value);
	}

	/**
	 * @return float
	 */
	public function &getValue() : float{
		return parent::getValue();
	}

	public function setValue($value) : void{
		if(!is_float($value) and !is_int($value)){
			throw new \TypeError("FloatTag value must be of type float, " . gettype($value) . " given");
		}
		parent::setValue((float) $value);
	}
}