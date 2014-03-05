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

namespace PocketMine\Utils;
use PocketMine;

//Unsecure, not used for "Real Randomness"
class Random{
	private $z, $w;
	public function __construct($seed = false){
		$this->setSeed($seed);
	}
	
	public function setSeed($seed = false){
		$seed = $seed !== false ? (int) $seed:Utils::readInt(Utils::getRandomBytes(4, false));
		$this->z = $seed ^ 0xdeadbeef;
		$this->w = $seed ^ 0xc0de1337;
	}
	
	public function nextInt(){
		return Utils::readInt($this->nextBytes(4)) & 0x7FFFFFFF;
	}
	
	public function nextSignedInt(){
		return Utils::readInt($this->nextBytes(4));
	}
	
	public function nextFloat(){
		return $this->nextInt() / 0x7FFFFFFF;
	}
	
	public function nextSignedFloat(){
		return $this->nextSignedInt() / 0x7FFFFFFF;
	}
	
	public function nextBytes($byteCount){
		$bytes = "";
		while(strlen($bytes) < $byteCount){
			$this->z = 36969 * ($this->z & 65535) + ($this->z >> 16);
			$this->w = 18000 * ($this->w & 65535) + ($this->w >> 16);
			$bytes .= pack("N", ($this->z << 16) + $this->w);
		}
		return substr($bytes, 0, $byteCount);
	}
	
	public function nextBoolean(){
		return ($this->nextSignedInt() & 0x01) === 0;
	}
	
	public function nextRange($start = 0, $end = PHP_INT_MAX){
		return $start + ($this->nextInt() % ($end + 1 - $start));
	}

}