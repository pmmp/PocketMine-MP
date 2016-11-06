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
 * Different noise generators for level generation
 */
namespace pocketmine\level\generator\noise;


abstract class Noise{
	protected $perm = [];
	protected $offsetX = 0;
	protected $offsetY = 0;
	protected $offsetZ = 0;
	protected $octaves = 8;
	protected $persistence;
	protected $expansion;

	public static function floor($x){
		return $x >= 0 ? (int) $x : (int) ($x - 1);
	}

	public static function fade($x){
		return $x * $x * $x * ($x * ($x * 6 - 15) + 10);
	}

	public static function lerp($x, $y, $z){
		return $y + $x * ($z - $y);
	}

	public static function linearLerp($x, $x1, $x2, $q0, $q1){
		return (($x2 - $x) / ($x2 - $x1)) * $q0 + (($x - $x1) / ($x2 - $x1)) * $q1;
	}

	public static function bilinearLerp($x, $y, $q00, $q01, $q10, $q11, $x1, $x2, $y1, $y2){
		$dx1 = (($x2 - $x) / ($x2 - $x1));
		$dx2 = (($x - $x1) / ($x2 - $x1));

		return (($y2 - $y) / ($y2 - $y1)) * (
			$dx1 * $q00 + $dx2 * $q10
		) + (($y - $y1) / ($y2 - $y1)) * (
			$dx1 * $q01 + $dx2 * $q11
		);
	}

	public static function trilinearLerp($x, $y, $z, $q000, $q001, $q010, $q011, $q100, $q101, $q110, $q111, $x1, $x2, $y1, $y2, $z1, $z2){
		$dx1 = (($x2 - $x) / ($x2 - $x1));
		$dx2 = (($x - $x1) / ($x2 - $x1));
		$dy1 = (($y2 - $y) / ($y2 - $y1));
		$dy2 = (($y - $y1) / ($y2 - $y1));

		return (($z2 - $z) / ($z2 - $z1)) * (
			$dy1 * (
				$dx1 * $q000 + $dx2 * $q100
			) + $dy2 * (
				$dx1 * $q001 + $dx2 * $q101
			)
		) + (($z - $z1) / ($z2 - $z1)) * (
			$dy1 * (
				$dx1 * $q010 + $dx2 * $q110
			) + $dy2 * (
				$dx1 * $q011 + $dx2 * $q111
			)
		);
	}

	public static function grad($hash, $x, $y, $z){
		$hash &= 15;
		$u = $hash < 8 ? $x : $y;
		$v = $hash < 4 ? $y : (($hash === 12 or $hash === 14) ? $x : $z);

		return (($hash & 1) === 0 ? $u : -$u) + (($hash & 2) === 0 ? $v : -$v);
	}

	abstract public function getNoise2D($x, $z);

	abstract public function getNoise3D($x, $y, $z);

	public function noise2D($x, $z, $normalized = false){
		$result = 0;
		$amp = 1;
		$freq = 1;
		$max = 0;

		$x *= $this->expansion;
		$z *= $this->expansion;

		for($i = 0; $i < $this->octaves; ++$i){
			$result += $this->getNoise2D($x * $freq, $z * $freq) * $amp;
			$max += $amp;
			$freq *= 2;
			$amp *= $this->persistence;
		}

		if($normalized === true){
			$result /= $max;
		}

		return $result;
	}

	public function noise3D($x, $y, $z, $normalized = false){
		$result = 0;
		$amp = 1;
		$freq = 1;
		$max = 0;

		$x *= $this->expansion;
		$y *= $this->expansion;
		$z *= $this->expansion;

		for($i = 0; $i < $this->octaves; ++$i){
			$result += $this->getNoise3D($x * $freq, $y * $freq, $z * $freq) * $amp;
			$max += $amp;
			$freq *= 2;
			$amp *= $this->persistence;
		}

		if($normalized === true){
			$result /= $max;
		}

		return $result;
	}

	public function setOffset($x, $y, $z){
		$this->offsetX = $x;
		$this->offsetY = $y;
		$this->offsetZ = $z;
	}
}