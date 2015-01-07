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
 *
 * WARNING: This class is available on the PocketMine-MP Zephir project.
 * If this class is modified, remember to modify the PHP C extension.
 */
namespace pocketmine\level\generator\noise;


abstract class Noise{
	protected $perm = [];
	protected $offsetX = 0;
	protected $offsetY = 0;
	protected $offsetZ = 0;
	protected $octaves = 8;
	protected $frequency;
	protected $lacunarity;
	protected $amplitude;

	public static function floor($x){
		return $x >= 0 ? (int) $x : (int) ($x - 1);
	}

	public static function fade($x){
		return $x ** 3 * ($x * ($x * 6 - 15) + 10);
	}

	public static function lerp($x, $y, $z){
		return $y + $x * ($z - $y);
	}

	public static function linearLerp($x, $x1, $x2, $q0, $q1){
		return (($x2 - $x) / ($x2 - $x1)) * $q0 + (($x - $x1) / ($x2 - $x1)) * $q1;
	}

	public static function bilinearLerp($x, $y, $q00, $q01, $q10, $q11, $x1, $x2, $y1, $y2){
		$q0 = self::linearLerp($x, $x1, $x2, $q00, $q10);
		$q1 = self::linearLerp($x, $x1, $x2, $q01, $q11);
		return self::linearLerp($y, $y1, $y2, $q0, $q1);
	}

	public static function trilinearLerp($x, $y, $z, $q000, $q001, $q010, $q011, $q100, $q101, $q110, $q111, $x1, $x2, $y1, $y2, $z1, $z2) {
		$q00 = self::linearLerp($x, $x1, $x2, $q000, $q100);
		$q01 = self::linearLerp($x, $x1, $x2, $q010, $q110);
		$q10 = self::linearLerp($x, $x1, $x2, $q001, $q101);
		$q11 = self::linearLerp($x, $x1, $x2, $q011, $q111);
		$q0 = self::linearLerp($y, $y1, $y2, $q00, $q10);
		$q1 = self::linearLerp($y, $y1, $y2, $q01, $q11);
		return self::linearLerp($z, $z1, $z2, $q0, $q1);
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
		$laq = 1;
		$max = 0;

		$x *= $this->frequency;
		$z *= $this->frequency;

		for($i = 0; $i < $this->octaves; ++$i){
			$result += $this->getNoise2D($x * $laq, $z * $laq) * $amp;
			$max += $amp;
			$laq *= $this->lacunarity;
			$amp *= $this->amplitude;
		}
		if($normalized === true){
			$result /= $max;
		}

		return $result;
	}

	public function noise3D($x, $y, $z, $normalized = false){
		$result = 0;
		$amp = 1;
		$laq = 1;
		$max = 0;

		$x *= $this->frequency;
		$y *= $this->frequency;
		$z *= $this->frequency;

		for($i = 0; $i < $this->octaves; ++$i){
			$result += $this->getNoise3D($x * $laq, $y * $laq, $z * $laq) * $amp;
			$max += $amp;
			$laq *= $this->lacunarity;
			$amp *= $this->amplitude;
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