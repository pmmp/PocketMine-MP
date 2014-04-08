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
 * WARNING: This class is available on the PocketMine-MP Zephir project.
 * If this class is modified, remember to modify the PHP C extension.
 */
class Random{

	protected $x;
	protected $y;
	protected $z;

	/**
	 * @param int $seed Integer to be used as seed.
	 */
	public function __construct($seed = -1){
		if($seed == -1){
			$seed = time();
		}
		$this->setSeed($seed);
	}

	/**
	 * @param int $seed Integer to be used as seed.
	 */
	public function setSeed($seed){
		$seed = crc32($seed);
		$this->x = ($seed ^ 1076543210) & INT32_MASK;
		$this->z = ($seed ^ 0xdeadc0de) & INT32_MASK;
		$this->y = ($seed ^ 1122334455) & INT32_MASK;
	}

	/**
	 * Returns an 31-bit integer (not signed)
	 *
	 * @return int
	 */
	public function nextInt(){
		return $this->nextSignedInt() & 0x7fffffff;
	}

	/**
	 * Returns a 32-bit integer (signed)
	 *
	 * @return int
	 */
	public function nextSignedInt(){
		if(INT32_MASK === -1){ //32 bit, do hacky things to shift sign
			$this->x ^= $this->x << 16;
			$hasBit = $this->x < 1;
			$t = ($hasBit === true ? $this->x ^ ~0x7fffffff : $this->x) >> 5;
			$this->x ^= ($hasBit === true ? $t ^ ~0x7fffffff : $t);
			$this->x ^= $this->x << 1;
		}else{ //64 bit
			$this->x ^= ($this->x << 16) & INT32_MASK;
			$this->x ^= $this->x >> 5;
			$this->x ^= ($this->x << 1) & INT32_MASK;
		}

		$t = $this->x;
		$this->x = $this->y;
		$this->y = $this->z;
		$this->z = $t ^ $this->x ^ $this->y;

		$t = $this->z;

		if($t > 2147483647){
			$t -= 4294967296;
		}
		return (int) $t;
	}

	/**
	 * Returns a float between 0.0 and 1.0 (inclusive)
	 *
	 * @return float
	 */
	public function nextFloat(){
		return $this->nextInt() / 0x7fffffff;
	}

	/**
	 * Returns a float between -1.0 and 1.0 (inclusive)
	 *
	 * @return float
	 */
	public function nextSignedFloat(){
		return $this->nextSignedInt() / 0x7fffffff;
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