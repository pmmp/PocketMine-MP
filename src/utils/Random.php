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


//Unsecure, not used for "Real Randomness"
class Random{
	private $x, $y, $z, $w;
	public function __construct($seed = false){
		$this->setSeed($seed);
	}
	
	public function setSeed($seed = false){
		$seed = $seed !== false ? Utils::writeInt((int) $seed):Utils::getRandomBytes(4, false);
		$state = array();
		for($i = 0; $i < 256; ++$i){
			$state[] = $i;
		}
		for($i = $j = 0; $i < 256; ++$i){
			$j = ($j + ord($seed{$i & 0x03}) + $state[$i]) & 0xFF;
			$state[$i] ^= $state[$j];
			$state[$j] ^= $state[$i];
			$state[$i] ^= $state[$j];
		}
		$this->state = $state;
		$this->i = $this->j = 0;
	}
	
	public function nextInt(){
		return Utils::readInt($this->nextBytes(4)) & 0x7FFFFFFF;
	}
	
	public function nextFloat(){
		return $this->nextInt() / 0x7FFFFFFF;
	}
	
	public function nextBytes($byteCount){
		$bytes = "";
		for($i = 0; $i < $byteCount; ++$i){
			$this->i = ($this->i + 1) & 0xFF;
			$this->j = ($this->j + $this->state[$this->i]) & 0xFF;
			$this->state[$this->i] ^= $this->state[$this->j];
			$this->state[$this->j] ^= $this->state[$this->i];
			$this->state[$this->i] ^= $this->state[$this->j];
			$bytes .= chr($this->state[($this->state[$this->i] + $this->state[$this->j]) & 0xFF]);
		}
		return $bytes;
	}
	
	public function nextBoolean(){
		return ($this->nextBytes(1) & 0x01) == 0;
	}
	
	public function nextRange($start = 0, $end = PHP_INT_MAX){
		return $start + ($this->nextInt() % ($end + 1 - $start));
	}

}