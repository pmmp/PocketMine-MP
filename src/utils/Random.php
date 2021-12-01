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

namespace pocketmine\utils;

use function time;

/**
 * XorShift128Engine Random Number Noise, used for fast seeded values
 * Most of the code in this class was adapted from the XorShift128Engine in the php-random library.
 */
class Random{
	public const X = 123456789;
	public const Y = 362436069;
	public const Z = 521288629;
	public const W = 88675123;

	/** @var int */
	private $x;

	/** @var int */
	private $y;

	/** @var int */
	private $z;

	/** @var int */
	private $w;

	/** @var int */
	protected $seed;

	/**
	 * @param int $seed Integer to be used as seed.
	 */
	public function __construct(int $seed = -1){
		if($seed === -1){
			$seed = time();
		}

		$this->setSeed($seed);
	}

	/**
	 * @param int $seed Integer to be used as seed.
	 */
	public function setSeed(int $seed) : void{
		$this->seed = $seed;
		$this->x = self::X ^ $seed;
		$this->y = self::Y ^ ($seed << 17) | (($seed >> 15) & 0x7fffffff) & 0xffffffff;
		$this->z = self::Z ^ ($seed << 31) | (($seed >> 1) & 0x7fffffff) & 0xffffffff;
		$this->w = self::W ^ ($seed << 18) | (($seed >> 14) & 0x7fffffff) & 0xffffffff;
	}

	public function getSeed() : int{
		return $this->seed;
	}

	/**
	 * Returns an 31-bit integer (not signed)
	 */
	public function nextInt() : int{
		return $this->nextSignedInt() & 0x7fffffff;
	}

	/**
	 * Returns a 32-bit integer (signed)
	 */
	public function nextSignedInt() : int{
		$t = ($this->x ^ ($this->x << 11)) & 0xffffffff;

		$this->x = $this->y;
		$this->y = $this->z;
		$this->z = $this->w;
		$this->w = ($this->w ^ (($this->w >> 19) & 0x7fffffff) ^ ($t ^ (($t >> 8) & 0x7fffffff))) & 0xffffffff;

		return $this->w;
	}

	/**
	 * Returns a float between 0.0 and 1.0 (inclusive)
	 */
	public function nextFloat() : float{
		return $this->nextInt() / 0x7fffffff;
	}

	/**
	 * Returns a float between -1.0 and 1.0 (inclusive)
	 */
	public function nextSignedFloat() : float{
		return $this->nextSignedInt() / 0x7fffffff;
	}

	/**
	 * Returns a random boolean
	 */
	public function nextBoolean() : bool{
		return ($this->nextSignedInt() & 0x01) === 0;
	}

	/**
	 * Returns a random integer between $start and $end
	 *
	 * @param int $start default 0
	 * @param int $end default 0x7fffffff
	 */
	public function nextRange(int $start = 0, int $end = 0x7fffffff) : int{
		return $start + ($this->nextInt() % ($end + 1 - $start));
	}

	public function nextBoundedInt(int $bound) : int{
		return $this->nextInt() % $bound;
	}
}
