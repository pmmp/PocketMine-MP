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

namespace PocketMine\NBT;
const TAG_End = 0;
const TAG_Byte = 1;
const TAG_Short = 2;
const TAG_Int = 3;
const TAG_Long = 4;
const TAG_Float = 5;
const TAG_Double = 6;
const TAG_Byte_Array = 7;
const TAG_String = 8;
const TAG_Enum = 9;
const TAG_Compound = 10;
const TAG_Int_Array = 11;
use PocketMine;

abstract class NBTTag{

	protected $value;

	public function &getValue(){
		return $this->value;
	}

	public abstract function getType();

	public function setValue($value){
		$this->value = $value;
	}

	abstract public function write(NBT $nbt);

	abstract public function read(NBT $nbt);

	public final function __toString(){
		return $this->value;
	}
}
