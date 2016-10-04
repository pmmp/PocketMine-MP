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

/**
 * All the NBT Tags
 */
namespace pocketmine\nbt\tag;

use pocketmine\nbt\NBT;

abstract class Tag extends \stdClass{

	protected $value;

	public function &getValue(){
		return $this->value;
	}

	public abstract function getType();

	public function setValue($value){
		$this->value = $value;
	}

	abstract public function write(NBT $nbt, bool $network = false);

	abstract public function read(NBT $nbt, bool $network = false);

	public function __toString(){
		return (string) $this->value;
	}
}
