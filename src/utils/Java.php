<?php

/**
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


class Java_String{
	private $value = "", $count = 0, $hash = 0;

	public function __construct($string = false){
		if($string !== false){
			$this->value = (string) $string;
			$this->count = strlen($this->value);
		}
	}

	public function __toString(){
		return $this->value;
	}

	public function lenght(){
		return $this->count;
	}

	public function isEmpty(){
		return $this->count === 0;
	}

	public function charAt($index){
		$index = (int) $index;
		if($index < 0 or $index >= $this->count){
			trigger_error("Undefined offset $index", E_USER_WARNING);
			return false;
		}
		return $this->value{$index};
	}

	public function hashCode(){
		$h = $this->hash;
		if($h === 0 and $this->count > 0){
			for($i = 0; $i < $this->count; ++$i){
				$h = (($h << 5) - $h) + ord($this->charAt($i));
				$h = $h & 0xFFFFFFFF;
				$this->hash = $h;
			}
			$this->hash = $h;
		}
		return $h;
	}
}