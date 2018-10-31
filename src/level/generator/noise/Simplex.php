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

namespace pocketmine\level\generator\noise;

use pocketmine\utils\Random;

/**
 * Generates simplex-based noise.
 *
 * This is a modified version of the freely published version in the paper by
 * Stefan Gustavson at
 * http://staffwww.itn.liu.se/~stegu/simplexnoise/simplexnoise.pdf
 */
class Simplex extends Noise{
	protected const grad3 = [
		[1, 1, 0], [-1, 1, 0], [1, -1, 0], [-1, -1, 0],
		[1, 0, 1], [-1, 0, 1], [1, 0, -1], [-1, 0, -1],
		[0, 1, 1], [0, -1, 1], [0, 1, -1], [0, -1, -1]
	];

	protected const F2 = 0.5 * (M_SQRT3 - 1);
	protected const G2 = (3 - M_SQRT3) / 6;
	protected const G22 = self::G2 * 2.0 - 1;
	protected const F3 = 1.0 / 3.0;
	protected const G3 = 1.0 / 6.0;

	/** @var float */
	protected $offsetX;
	/** @var float */
	protected $offsetZ;
	/** @var float */
	protected $offsetY;
	/** @var int[] */
	protected $perm = [];

	public function __construct(Random $random, int $octaves, float $persistence, float $expansion){
		parent::__construct($octaves, $persistence, $expansion);

		$this->offsetX = $random->nextFloat() * 256;
		$this->offsetY = $random->nextFloat() * 256;
		$this->offsetZ = $random->nextFloat() * 256;

		for($i = 0; $i < 512; ++$i){
			$this->perm[$i] = 0;
		}

		for($i = 0; $i < 256; ++$i){
			$this->perm[$i] = $random->nextBoundedInt(256);
		}

		for($i = 0; $i < 256; ++$i){
			$pos = $random->nextBoundedInt(256 - $i) + $i;
			$old = $this->perm[$i];

			$this->perm[$i] = $this->perm[$pos];
			$this->perm[$pos] = $old;
			$this->perm[$i + 256] = $this->perm[$i];
		}
	}

	public function getNoise3D($x, $y, $z){
		$x += $this->offsetX;
		$y += $this->offsetY;
		$z += $this->offsetZ;

		// Skew the input space to determine which simplex cell we're in
		$s = ($x + $y + $z) * self::F3; // Very nice and simple skew factor for 3D
		$i = (int) ($x + $s);
		$j = (int) ($y + $s);
		$k = (int) ($z + $s);
		$t = ($i + $j + $k) * self::G3;
		// Unskew the cell origin back to (x,y,z) space
		$x0 = $x - ($i - $t); // The x,y,z distances from the cell origin
		$y0 = $y - ($j - $t);
		$z0 = $z - ($k - $t);

		// For the 3D case, the simplex shape is a slightly irregular tetrahedron.

		// Determine which simplex we are in.
		if($x0 >= $y0){
			if($y0 >= $z0){
				$i1 = 1;
				$j1 = 0;
				$k1 = 0;
				$i2 = 1;
				$j2 = 1;
				$k2 = 0;
			} // X Y Z order
			elseif($x0 >= $z0){
				$i1 = 1;
				$j1 = 0;
				$k1 = 0;
				$i2 = 1;
				$j2 = 0;
				$k2 = 1;
			} // X Z Y order
			else{
				$i1 = 0;
				$j1 = 0;
				$k1 = 1;
				$i2 = 1;
				$j2 = 0;
				$k2 = 1;
			}
			// Z X Y order
		}else{ // x0<y0
			if($y0 < $z0){
				$i1 = 0;
				$j1 = 0;
				$k1 = 1;
				$i2 = 0;
				$j2 = 1;
				$k2 = 1;
			} // Z Y X order
			elseif($x0 < $z0){
				$i1 = 0;
				$j1 = 1;
				$k1 = 0;
				$i2 = 0;
				$j2 = 1;
				$k2 = 1;
			} // Y Z X order
			else{
				$i1 = 0;
				$j1 = 1;
				$k1 = 0;
				$i2 = 1;
				$j2 = 1;
				$k2 = 0;
			}
			// Y X Z order
		}

		// A step of (1,0,0) in (i,j,k) means a step of (1-c,-c,-c) in (x,y,z),
		// a step of (0,1,0) in (i,j,k) means a step of (-c,1-c,-c) in (x,y,z), and
		// a step of (0,0,1) in (i,j,k) means a step of (-c,-c,1-c) in (x,y,z), where
		// c = 1/6.
		$x1 = $x0 - $i1 + self::G3; // Offsets for second corner in (x,y,z) coords
		$y1 = $y0 - $j1 + self::G3;
		$z1 = $z0 - $k1 + self::G3;
		$x2 = $x0 - $i2 + 2.0 * self::G3; // Offsets for third corner in (x,y,z) coords
		$y2 = $y0 - $j2 + 2.0 * self::G3;
		$z2 = $z0 - $k2 + 2.0 * self::G3;
		$x3 = $x0 - 1.0 + 3.0 * self::G3; // Offsets for last corner in (x,y,z) coords
		$y3 = $y0 - 1.0 + 3.0 * self::G3;
		$z3 = $z0 - 1.0 + 3.0 * self::G3;

		// Work out the hashed gradient indices of the four simplex corners
		$ii = $i & 255;
		$jj = $j & 255;
		$kk = $k & 255;

		$n = 0;

		// Calculate the contribution from the four corners
		$t0 = 0.6 - $x0 * $x0 - $y0 * $y0 - $z0 * $z0;
		if($t0 > 0){
			$gi0 = self::grad3[$this->perm[$ii + $this->perm[$jj + $this->perm[$kk]]] % 12];
			$n += $t0 * $t0 * $t0 * $t0 * ($gi0[0] * $x0 + $gi0[1] * $y0 + $gi0[2] * $z0);
		}

		$t1 = 0.6 - $x1 * $x1 - $y1 * $y1 - $z1 * $z1;
		if($t1 > 0){
			$gi1 = self::grad3[$this->perm[$ii + $i1 + $this->perm[$jj + $j1 + $this->perm[$kk + $k1]]] % 12];
			$n += $t1 * $t1 * $t1 * $t1 * ($gi1[0] * $x1 + $gi1[1] * $y1 + $gi1[2] * $z1);
		}

		$t2 = 0.6 - $x2 * $x2 - $y2 * $y2 - $z2 * $z2;
		if($t2 > 0){
			$gi2 = self::grad3[$this->perm[$ii + $i2 + $this->perm[$jj + $j2 + $this->perm[$kk + $k2]]] % 12];
			$n += $t2 * $t2 * $t2 * $t2 * ($gi2[0] * $x2 + $gi2[1] * $y2 + $gi2[2] * $z2);
		}

		$t3 = 0.6 - $x3 * $x3 - $y3 * $y3 - $z3 * $z3;
		if($t3 > 0){
			$gi3 = self::grad3[$this->perm[$ii + 1 + $this->perm[$jj + 1 + $this->perm[$kk + 1]]] % 12];
			$n += $t3 * $t3 * $t3 * $t3 * ($gi3[0] * $x3 + $gi3[1] * $y3 + $gi3[2] * $z3);
		}

		// Add contributions from each corner to get the noise value.
		// The result is scaled to stay just inside [-1,1]
		return 32.0 * $n;
	}

	public function getNoise2D($x, $y){
		$x += $this->offsetX;
		$y += $this->offsetY;

		// Skew the input space to determine which simplex cell we're in
		$s = ($x + $y) * self::F2; // Hairy factor for 2D
		$i = (int) ($x + $s);
		$j = (int) ($y + $s);
		$t = ($i + $j) * self::G2;
		// Unskew the cell origin back to (x,y) space
		$x0 = $x - ($i - $t); // The x,y distances from the cell origin
		$y0 = $y - ($j - $t);

		// For the 2D case, the simplex shape is an equilateral triangle.

		// Determine which simplex we are in.
		if($x0 > $y0){
			$i1 = 1;
			$j1 = 0;
		} // lower triangle, XY order: (0,0)->(1,0)->(1,1)
		else{
			$i1 = 0;
			$j1 = 1;
		}
		// upper triangle, YX order: (0,0)->(0,1)->(1,1)

		// A step of (1,0) in (i,j) means a step of (1-c,-c) in (x,y), and
		// a step of (0,1) in (i,j) means a step of (-c,1-c) in (x,y), where
		// c = (3-sqrt(3))/6

		$x1 = $x0 - $i1 + self::G2; // Offsets for middle corner in (x,y) unskewed coords
		$y1 = $y0 - $j1 + self::G2;
		$x2 = $x0 + self::G22; // Offsets for last corner in (x,y) unskewed coords
		$y2 = $y0 + self::G22;

		// Work out the hashed gradient indices of the three simplex corners
		$ii = $i & 255;
		$jj = $j & 255;

		$n = 0;

		// Calculate the contribution from the three corners
		$t0 = 0.5 - $x0 * $x0 - $y0 * $y0;
		if($t0 > 0){
			$gi0 = self::grad3[$this->perm[$ii + $this->perm[$jj]] % 12];
			$n += $t0 * $t0 * $t0 * $t0 * ($gi0[0] * $x0 + $gi0[1] * $y0); // (x,y) of grad3 used for 2D gradient
		}

		$t1 = 0.5 - $x1 * $x1 - $y1 * $y1;
		if($t1 > 0){
			$gi1 = self::grad3[$this->perm[$ii + $i1 + $this->perm[$jj + $j1]] % 12];
			$n += $t1 * $t1 * $t1 * $t1 * ($gi1[0] * $x1 + $gi1[1] * $y1);
		}

		$t2 = 0.5 - $x2 * $x2 - $y2 * $y2;
		if($t2 > 0){
			$gi2 = self::grad3[$this->perm[$ii + 1 + $this->perm[$jj + 1]] % 12];
			$n += $t2 * $t2 * $t2 * $t2 * ($gi2[0] * $x2 + $gi2[1] * $y2);
		}

		// Add contributions from each corner to get the noise value.
		// The result is scaled to return values in the interval [-1,1].
		return 70.0 * $n;
	}
}
