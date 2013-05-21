<?php

/*

           -
         /   \
      /         \
   /   PocketMine  \
/          MP         \
|\     @shoghicp     /|
|.   \           /   .|
| ..     \   /     .. |
|    ..    |    ..    |
|       .. | ..       |
\          |          /
   \       |       /
      \    |    /
         \ | /

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Lesser General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.


*/


//Unsecure, not used for "Real Randomness"
class Random{
	private $random;
	public function __construct($seed = false){
		$this->random = new twister(0);
		$this->setSeed($seed);
	}
	
	public function setSeed($seed = false){
		$this->random->init_with_integer($seed !== false ? (int) $seed:Utils::readInt(Utils::getRandomBytes(4, false)));
	}
	
	public function nextInt(){
		return $this->random->int32();
	}
	
	public function nextFloat(){
		return $this->random->real_closed();
	}
	
	public function nextBytes($byteCount){
		$bytes = "";
		for($i = 0; $i < $byteCount; ++$i){
			$bytes .= chr($this->random->rangeint(0, 0xFF));
		}
		return $bytes;
	}
	
	public function nextBoolean(){
		return $this->random->rangeint(0, 1) === 1;
	}
	
	public function nextRange($start = 0, $end = PHP_INT_MAX){
		return $this->random->rangeint($start, $end);
	}

}