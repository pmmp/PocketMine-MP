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

namespace pocketmine\utils;


/**
 * Unsecure Random Number Generator, used for fast seeded values
 */
class Random{
	private $z, $w;

	/**
	 * @param int|bool $seed Integer to be used as seed. If false, generates a Random one
	 */
	public function __construct($seed = false){
		$this->setSeed($seed);
	}

	/**
	 * @param int|bool $seed Integer to be used as seed. If false, generates a Random one
	 */
	public function setSeed($seed = false){
		$seed = $seed !== false ? (int) $seed : Utils::readInt(Utils::getRandomBytes(4, false));
		$this->z = $seed ^ 0xdeadbeef;
		$this->w = $seed ^ 0xc0de1337;
	}

	/**
	 * Returns an 31-bit integer (not signed)
	 *
	 * @return int
	 */
	public function nextInt(){
		return Utils::readInt($this->nextBytes(4)) & 0x7FFFFFFF;
	}

	/**
	 * Returns a 32-bit integer (signed)
	 *
	 * @return int
	 */
	public function nextSignedInt(){
		return Utils::readInt($this->nextBytes(4));
	}

	/**
	 * Returns a float between 0.0 and 1.0 (inclusive)
	 *
	 * @return float
	 */
	public function nextFloat(){
		return $this->nextInt() / 0x7FFFFFFF;
	}

	/**
	 * Returns a float between -1.0 and 1.0 (inclusive)
	 *
	 * @return float
	 */
	public function nextSignedFloat(){
		return $this->nextSignedInt() / 0x7FFFFFFF;
	}

	/**
	 * Returns $byteCount random bytes
	 *
	 * @param $byteCount
	 *
	 * @return string
	 */
	public function nextBytes($byteCount){
		$bytes = "";
		while(strlen($bytes) < $byteCount){
			$this->z = 36969 * ($this->z & 65535) + ($this->z >> 16);
			$this->w = 18000 * ($this->w & 65535) + ($this->w >> 16);
			$bytes .= pack("N", ($this->z << 16) + $this->w);
		}

		return substr($bytes, 0, $byteCount);
	}

	/**
	 * Returns a random boolean
	 *
	 * @return bool
	 */
	public function nextBoolean(){
		return ($this->nextSignedInt() & 0x01) === 0;
	}

	/**
	 * Returns a random integer between $start and $end
	 *
	 * @param int $start default 0
	 * @param int $end   default PHP_INT_MAX
	 *
	 * @return int
	 */
	public function nextRange($start = 0, $end = PHP_INT_MAX){
		return $start + ($this->nextInt() % ($end + 1 - $start));
	}

}