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


class Java_Random{
	private $haveNextNextGaussian, $nextNextGaussian, $seed, $n1, $n2, $n3, $zero;

	public function __construct($seed = false){
		$this->n1 = new Math_BigInteger(0x5DEECE66D);
		$this->n2 = new Math_BigInteger(1);
		$this->n2 = $this->n2->bitwise_leftShift(48)->subtract($this->n2);
		$this->n3 = new Math_BigInteger(0xB);
		$this->zero = new Math_BigInteger(0);
		if($seed === false){
			$seed = microtime(true) * 1000000;
		}
		$this->setSeed($seed);
	}

	public function setSeed($seed){
		$seed = new Math_BigInteger($seed);
		$this->seed = $seed->bitwise_xor($this->n1)->bitwise_and($this->n2);
		$this->haveNextNextGaussian = false;
	}

	protected function next($bits){
		$bits = (int) $bits;
		$this->seed = $this->seed->multiply($this->n1)->add($this->n3)->bitwise_and($this->n2);
		return $this->_tripleRightShift($this->seed, (48 - $bits));
	}

	private function _tripleRightShift($number, $places){
		if($number->compare($this->zero) >= 0){
			return $number->bitwise_rightShift($places);
		}
		$n1 = new Math_BigInteger(2);
		return $number->bitwise_rightShift($places)->add($n1->bitwise_leftShift(~$places));
	}

	public function nextBytes($bytes){
		$bytes = (int) $bytes;
		$b = b"";
		$max = $bytes & ~0x3;
		for($i = 0; $i < $max; $i += 4){
			$b .= $this->next(32)->toBytes();
		}
		if($max < $bytes){
			$random = $this->next(32)->toBytes();
			for($j = $max; $j < $bytes; ++$j){
				$b .= $random{$j-$max};
			}
		}
		return $b;
	}

}