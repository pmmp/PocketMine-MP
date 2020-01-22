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
use function sqrt;

/**
 * Generates simplex-based noise.
 *
 * This is a modified version of the freely published version in the paper by
 * Stefan Gustavson at
 * http://staffwww.itn.liu.se/~stegu/simplexnoise/simplexnoise.pdf
 */
class Simplex extends Perlin{
	/** @var float */
	protected static $SQRT_3;
	/** @var float */
	protected static $SQRT_5;
	/** @var float */
	protected static $F2;
	/** @var float */
	protected static $G2;
	/** @var float */
	protected static $G22;
	/** @var float */
	protected static $F3;
	/** @var float */
	protected static $G3;
	/** @var float */
	protected static $F4;
	/** @var float */
	protected static $G4;
	/** @var float */
	protected static $G42;
	/** @var float */
	protected static $G43;
	/** @var float */
	protected static $G44;
	/** @var int[][] */
	protected static $grad4 = [[0, 1, 1, 1], [0, 1, 1, -1], [0, 1, -1, 1], [0, 1, -1, -1],
		[0, -1, 1, 1], [0, -1, 1, -1], [0, -1, -1, 1], [0, -1, -1, -1],
		[1, 0, 1, 1], [1, 0, 1, -1], [1, 0, -1, 1], [1, 0, -1, -1],
		[-1, 0, 1, 1], [-1, 0, 1, -1], [-1, 0, -1, 1], [-1, 0, -1, -1],
		[1, 1, 0, 1], [1, 1, 0, -1], [1, -1, 0, 1], [1, -1, 0, -1],
		[-1, 1, 0, 1], [-1, 1, 0, -1], [-1, -1, 0, 1], [-1, -1, 0, -1],
		[1, 1, 1, 0], [1, 1, -1, 0], [1, -1, 1, 0], [1, -1, -1, 0],
		[-1, 1, 1, 0], [-1, 1, -1, 0], [-1, -1, 1, 0], [-1, -1, -1, 0]];

	/** @var int[][] */
	protected static $simplex = [
		[0, 1, 2, 3], [0, 1, 3, 2], [0, 0, 0, 0], [0, 2, 3, 1], [0, 0, 0, 0], [0, 0, 0, 0], [0, 0, 0, 0], [1, 2, 3, 0],
		[0, 2, 1, 3], [0, 0, 0, 0], [0, 3, 1, 2], [0, 3, 2, 1], [0, 0, 0, 0], [0, 0, 0, 0], [0, 0, 0, 0], [1, 3, 2, 0],
		[0, 0, 0, 0], [0, 0, 0, 0], [0, 0, 0, 0], [0, 0, 0, 0], [0, 0, 0, 0], [0, 0, 0, 0], [0, 0, 0, 0], [0, 0, 0, 0],
		[1, 2, 0, 3], [0, 0, 0, 0], [1, 3, 0, 2], [0, 0, 0, 0], [0, 0, 0, 0], [0, 0, 0, 0], [2, 3, 0, 1], [2, 3, 1, 0],
		[1, 0, 2, 3], [1, 0, 3, 2], [0, 0, 0, 0], [0, 0, 0, 0], [0, 0, 0, 0], [2, 0, 3, 1], [0, 0, 0, 0], [2, 1, 3, 0],
		[0, 0, 0, 0], [0, 0, 0, 0], [0, 0, 0, 0], [0, 0, 0, 0], [0, 0, 0, 0], [0, 0, 0, 0], [0, 0, 0, 0], [0, 0, 0, 0],
		[2, 0, 1, 3], [0, 0, 0, 0], [0, 0, 0, 0], [0, 0, 0, 0], [3, 0, 1, 2], [3, 0, 2, 1], [0, 0, 0, 0], [3, 1, 2, 0],
		[2, 1, 0, 3], [0, 0, 0, 0], [0, 0, 0, 0], [0, 0, 0, 0], [3, 1, 0, 2], [0, 0, 0, 0], [3, 2, 0, 1], [3, 2, 1, 0]];

	/** @var float */
	protected $offsetW;

	/**
	 * @param int    $octaves
	 * @param float  $persistence
	 * @param float  $expansion
	 */
	public function __construct(Random $random, $octaves, $persistence, $expansion = 1){
		parent::__construct($random, $octaves, $persistence, $expansion);
		$this->offsetW = $random->nextFloat() * 256;
		self::$SQRT_3 = sqrt(3);
		self::$SQRT_5 = sqrt(5);
		self::$F2 = 0.5 * (self::$SQRT_3 - 1);
		self::$G2 = (3 - self::$SQRT_3) / 6;
		self::$G22 = self::$G2 * 2.0 - 1;
		self::$F3 = 1.0 / 3.0;
		self::$G3 = 1.0 / 6.0;
		self::$F4 = (self::$SQRT_5 - 1.0) / 4.0;
		self::$G4 = (5.0 - self::$SQRT_5) / 20.0;
		self::$G42 = self::$G4 * 2.0;
		self::$G43 = self::$G4 * 3.0;
		self::$G44 = self::$G4 * 4.0 - 1.0;
	}

	/**
	 * @param int[] $g
	 * @param float $x
	 * @param float $y
	 *
	 * @return float
	 */
	protected static function dot2D($g, $x, $y){
		return $g[0] * $x + $g[1] * $y;
	}

	/**
	 * @param int[] $g
	 * @param float $x
	 * @param float $y
	 * @param float $z
	 *
	 * @return float
	 */
	protected static function dot3D($g, $x, $y, $z){
		return $g[0] * $x + $g[1] * $y + $g[2] * $z;
	}

	/**
	 * @param int[] $g
	 * @param float $x
	 * @param float $y
	 * @param float $z
	 * @param float $w
	 *
	 * @return float
	 */
	protected static function dot4D($g, $x, $y, $z, $w){
		return $g[0] * $x + $g[1] * $y + $g[2] * $z + $g[3] * $w;
	}

	public function getNoise3D($x, $y, $z){
		$x += $this->offsetX;
		$y += $this->offsetY;
		$z += $this->offsetZ;

		// Skew the input space to determine which simplex cell we're in
		$s = ($x + $y + $z) * self::$F3; // Very nice and simple skew factor for 3D
		$i = (int) ($x + $s);
		$j = (int) ($y + $s);
		$k = (int) ($z + $s);
		$t = ($i + $j + $k) * self::$G3;
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
		$x1 = $x0 - $i1 + self::$G3; // Offsets for second corner in (x,y,z) coords
		$y1 = $y0 - $j1 + self::$G3;
		$z1 = $z0 - $k1 + self::$G3;
		$x2 = $x0 - $i2 + 2.0 * self::$G3; // Offsets for third corner in (x,y,z) coords
		$y2 = $y0 - $j2 + 2.0 * self::$G3;
		$z2 = $z0 - $k2 + 2.0 * self::$G3;
		$x3 = $x0 - 1.0 + 3.0 * self::$G3; // Offsets for last corner in (x,y,z) coords
		$y3 = $y0 - 1.0 + 3.0 * self::$G3;
		$z3 = $z0 - 1.0 + 3.0 * self::$G3;

		// Work out the hashed gradient indices of the four simplex corners
		$ii = $i & 255;
		$jj = $j & 255;
		$kk = $k & 255;

		$n = 0;

		// Calculate the contribution from the four corners
		$t0 = 0.6 - $x0 * $x0 - $y0 * $y0 - $z0 * $z0;
		if($t0 > 0){
			$gi0 = self::$grad3[$this->perm[$ii + $this->perm[$jj + $this->perm[$kk]]] % 12];
			$n += $t0 * $t0 * $t0 * $t0 * ($gi0[0] * $x0 + $gi0[1] * $y0 + $gi0[2] * $z0);
		}

		$t1 = 0.6 - $x1 * $x1 - $y1 * $y1 - $z1 * $z1;
		if($t1 > 0){
			$gi1 = self::$grad3[$this->perm[$ii + $i1 + $this->perm[$jj + $j1 + $this->perm[$kk + $k1]]] % 12];
			$n += $t1 * $t1 * $t1 * $t1 * ($gi1[0] * $x1 + $gi1[1] * $y1 + $gi1[2] * $z1);
		}

		$t2 = 0.6 - $x2 * $x2 - $y2 * $y2 - $z2 * $z2;
		if($t2 > 0){
			$gi2 = self::$grad3[$this->perm[$ii + $i2 + $this->perm[$jj + $j2 + $this->perm[$kk + $k2]]] % 12];
			$n += $t2 * $t2 * $t2 * $t2 * ($gi2[0] * $x2 + $gi2[1] * $y2 + $gi2[2] * $z2);
		}

		$t3 = 0.6 - $x3 * $x3 - $y3 * $y3 - $z3 * $z3;
		if($t3 > 0){
			$gi3 = self::$grad3[$this->perm[$ii + 1 + $this->perm[$jj + 1 + $this->perm[$kk + 1]]] % 12];
			$n += $t3 * $t3 * $t3 * $t3 * ($gi3[0] * $x3 + $gi3[1] * $y3 + $gi3[2] * $z3);
		}

		// Add contributions from each corner to get the noise value.
		// The result is scaled to stay just inside [-1,1]
		return 32.0 * $n;
	}

	/**
	 * @param float $x
	 * @param float $y
	 *
	 * @return float
	 */
	public function getNoise2D($x, $y){
		$x += $this->offsetX;
		$y += $this->offsetY;

		// Skew the input space to determine which simplex cell we're in
		$s = ($x + $y) * self::$F2; // Hairy factor for 2D
		$i = (int) ($x + $s);
		$j = (int) ($y + $s);
		$t = ($i + $j) * self::$G2;
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

		$x1 = $x0 - $i1 + self::$G2; // Offsets for middle corner in (x,y) unskewed coords
		$y1 = $y0 - $j1 + self::$G2;
		$x2 = $x0 + self::$G22; // Offsets for last corner in (x,y) unskewed coords
		$y2 = $y0 + self::$G22;

		// Work out the hashed gradient indices of the three simplex corners
		$ii = $i & 255;
		$jj = $j & 255;

		$n = 0;

		// Calculate the contribution from the three corners
		$t0 = 0.5 - $x0 * $x0 - $y0 * $y0;
		if($t0 > 0){
			$gi0 = self::$grad3[$this->perm[$ii + $this->perm[$jj]] % 12];
			$n += $t0 * $t0 * $t0 * $t0 * ($gi0[0] * $x0 + $gi0[1] * $y0); // (x,y) of grad3 used for 2D gradient
		}

		$t1 = 0.5 - $x1 * $x1 - $y1 * $y1;
		if($t1 > 0){
			$gi1 = self::$grad3[$this->perm[$ii + $i1 + $this->perm[$jj + $j1]] % 12];
			$n += $t1 * $t1 * $t1 * $t1 * ($gi1[0] * $x1 + $gi1[1] * $y1);
		}

		$t2 = 0.5 - $x2 * $x2 - $y2 * $y2;
		if($t2 > 0){
			$gi2 = self::$grad3[$this->perm[$ii + 1 + $this->perm[$jj + 1]] % 12];
			$n += $t2 * $t2 * $t2 * $t2 * ($gi2[0] * $x2 + $gi2[1] * $y2);
		}

		// Add contributions from each corner to get the noise value.
		// The result is scaled to return values in the interval [-1,1].
		return 70.0 * $n;
	}

	/**
	 * Computes and returns the 4D simplex noise for the given coordinates in
	 * 4D space
	 *
	 * @param float $x X coordinate
	 * @param float $y Y coordinate
	 * @param float $z Z coordinate
	 * @param float $w W coordinate
	 *
	 * @return float Noise at given location, from range -1 to 1
	 */
	/*public function getNoise4D($x, $y, $z, $w){
		x += offsetX;
		y += offsetY;
		z += offsetZ;
		w += offsetW;

		n0, n1, n2, n3, n4; // Noise contributions from the five corners

		// Skew the (x,y,z,w) space to determine which cell of 24 simplices we're in
		s = (x + y + z + w) * self::$F4; // Factor for 4D skewing
		i = floor(x + s);
		j = floor(y + s);
		k = floor(z + s);
		l = floor(w + s);

		t = (i + j + k + l) * self::$G4; // Factor for 4D unskewing
		X0 = i - t; // Unskew the cell origin back to (x,y,z,w) space
		Y0 = j - t;
		Z0 = k - t;
		W0 = l - t;
		x0 = x - X0; // The x,y,z,w distances from the cell origin
		y0 = y - Y0;
		z0 = z - Z0;
		w0 = w - W0;

		// For the 4D case, the simplex is a 4D shape I won't even try to describe.
		// To find out which of the 24 possible simplices we're in, we need to
		// determine the magnitude ordering of x0, y0, z0 and w0.
		// The method below is a good way of finding the ordering of x,y,z,w and
		// then find the correct traversal order for the simplex we’re in.
		// First, six pair-wise comparisons are performed between each possible pair
		// of the four coordinates, and the results are used to add up binary bits
		// for an integer index.
		c1 = (x0 > y0) ? 32 : 0;
		c2 = (x0 > z0) ? 16 : 0;
		c3 = (y0 > z0) ? 8 : 0;
		c4 = (x0 > w0) ? 4 : 0;
		c5 = (y0 > w0) ? 2 : 0;
		c6 = (z0 > w0) ? 1 : 0;
		c = c1 + c2 + c3 + c4 + c5 + c6;
		i1, j1, k1, l1; // The integer offsets for the second simplex corner
		i2, j2, k2, l2; // The integer offsets for the third simplex corner
		i3, j3, k3, l3; // The integer offsets for the fourth simplex corner

		// simplex[c] is a 4-vector with the numbers 0, 1, 2 and 3 in some order.
		// Many values of c will never occur, since e.g. x>y>z>w makes x<z, y<w and x<w
		// impossible. Only the 24 indices which have non-zero entries make any sense.
		// We use a thresholding to set the coordinates in turn from the largest magnitude.

		// The number 3 in the "simplex" array is at the position of the largest coordinate.
		i1 = simplex[c][0] >= 3 ? 1 : 0;
		j1 = simplex[c][1] >= 3 ? 1 : 0;
		k1 = simplex[c][2] >= 3 ? 1 : 0;
		l1 = simplex[c][3] >= 3 ? 1 : 0;

		// The number 2 in the "simplex" array is at the second largest coordinate.
		i2 = simplex[c][0] >= 2 ? 1 : 0;
		j2 = simplex[c][1] >= 2 ? 1 : 0;
		k2 = simplex[c][2] >= 2 ? 1 : 0;
		l2 = simplex[c][3] >= 2 ? 1 : 0;

		// The number 1 in the "simplex" array is at the second smallest coordinate.
		i3 = simplex[c][0] >= 1 ? 1 : 0;
		j3 = simplex[c][1] >= 1 ? 1 : 0;
		k3 = simplex[c][2] >= 1 ? 1 : 0;
		l3 = simplex[c][3] >= 1 ? 1 : 0;

		// The fifth corner has all coordinate offsets = 1, so no need to look that up.

		x1 = x0 - i1 + self::$G4; // Offsets for second corner in (x,y,z,w) coords
		y1 = y0 - j1 + self::$G4;
		z1 = z0 - k1 + self::$G4;
		w1 = w0 - l1 + self::$G4;

		x2 = x0 - i2 + self::$G42; // Offsets for third corner in (x,y,z,w) coords
		y2 = y0 - j2 + self::$G42;
		z2 = z0 - k2 + self::$G42;
		w2 = w0 - l2 + self::$G42;

		x3 = x0 - i3 + self::$G43; // Offsets for fourth corner in (x,y,z,w) coords
		y3 = y0 - j3 + self::$G43;
		z3 = z0 - k3 + self::$G43;
		w3 = w0 - l3 + self::$G43;

		x4 = x0 + self::$G44; // Offsets for last corner in (x,y,z,w) coords
		y4 = y0 + self::$G44;
		z4 = z0 + self::$G44;
		w4 = w0 + self::$G44;

		// Work out the hashed gradient indices of the five simplex corners
		ii = i & 255;
		jj = j & 255;
		kk = k & 255;
		ll = l & 255;

		gi0 = $this->perm[ii + $this->perm[jj + $this->perm[kk + $this->perm[ll]]]] % 32;
		gi1 = $this->perm[ii + i1 + $this->perm[jj + j1 + $this->perm[kk + k1 + $this->perm[ll + l1]]]] % 32;
		gi2 = $this->perm[ii + i2 + $this->perm[jj + j2 + $this->perm[kk + k2 + $this->perm[ll + l2]]]] % 32;
		gi3 = $this->perm[ii + i3 + $this->perm[jj + j3 + $this->perm[kk + k3 + $this->perm[ll + l3]]]] % 32;
		gi4 = $this->perm[ii + 1 + $this->perm[jj + 1 + $this->perm[kk + 1 + $this->perm[ll + 1]]]] % 32;

		// Calculate the contribution from the five corners
		t0 = 0.6 - x0 * x0 - y0 * y0 - z0 * z0 - w0 * w0;
		if(t0 < 0){
			n0 = 0.0;
		}else{
			t0 *= t0;
			n0 = t0 * t0 * dot(grad4[gi0], x0, y0, z0, w0);
		}

		t1 = 0.6 - x1 * x1 - y1 * y1 - z1 * z1 - w1 * w1;
		if(t1 < 0){
			n1 = 0.0;
		}else{
			t1 *= t1;
			n1 = t1 * t1 * dot(grad4[gi1], x1, y1, z1, w1);
		}

		t2 = 0.6 - x2 * x2 - y2 * y2 - z2 * z2 - w2 * w2;
		if(t2 < 0){
			n2 = 0.0;
		}else{
			t2 *= t2;
			n2 = t2 * t2 * dot(grad4[gi2], x2, y2, z2, w2);
		}

		t3 = 0.6 - x3 * x3 - y3 * y3 - z3 * z3 - w3 * w3;
		if(t3 < 0){
			n3 = 0.0;
		}else{
			t3 *= t3;
			n3 = t3 * t3 * dot(grad4[gi3], x3, y3, z3, w3);
		}

		t4 = 0.6 - x4 * x4 - y4 * y4 - z4 * z4 - w4 * w4;
		if(t4 < 0){
			n4 = 0.0;
		}else{
			t4 *= t4;
			n4 = t4 * t4 * dot(grad4[gi4], x4, y4, z4, w4);
		}

		// Sum up and scale the result to cover the range [-1,1]
		return 27.0 * (n0 + n1 + n2 + n3 + n4);
	}*/
}
