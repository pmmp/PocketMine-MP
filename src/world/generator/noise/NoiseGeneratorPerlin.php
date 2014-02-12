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

/***REM_START***/
require_once("NoiseGenerator.php");
/***REM_END***/

class NoiseGeneratorPerlin extends NoiseGenerator{
	public static $grad3 = [
		[1, 1, 0], [-1, 1, 0], [1, -1, 0], [-1, -1, 0],
		[1, 0, 1], [-1, 0, 1], [1, 0, -1], [-1, 0, -1],
		[0, 1, 1], [0, -1, 1], [0, 1, -1], [0, -1, -1]
	];
	
	
	public function __construct(Random $random, $octaves){
		$this->octaves = $octaves;
		$this->offsetX = $random->nextFloat() * 256;
		$this->offsetY = $random->nextFloat() * 256;
		$this->offsetZ = $random->nextFloat() * 256;

		for($i = 0; $i < 512; ++$i){
			$this->perm[$i] = 0;
		}
		
		for($i = 0; $i < 256; ++$i){
			$this->perm[$i] = $random->nextRange(0, 255);
		}

		for($i = 0; $i < 256; ++$i){
			$pos = $random->nextRange(0, 255 - $i) + $i;
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
		
		$floorX = self::floor($x);
		$floorY = self::floor($y);
		$floorZ = self::floor($z);
		
		$X = $floorX & 0xFF;
		$Y = $floorY & 0xFF;
		$Z = $floorZ & 0xFF;
		
		$x -= $floorX;
		$y -= $floorY;
		$z -= $floorZ;
		
		//Fade curves
		$fX = self::fade($x);
		$fY = self::fade($y);
		$fZ = self::fade($z);
		
		//Cube corners
		$A = $this->perm[$X] + $Y;
		$AA = $this->perm[$A] + $Z;
		$AB = $this->perm[$A + 1] + $Z;
		$B = $this->perm[$X + 1] + $Y;
		$BA = $this->perm[$B] + $Z;
		$BB = $this->perm[$B + 1] + $Z;
		
		return self::lerp($fZ, self::lerp($fY, self::lerp($fX, self::grad($this->perm[$AA], $x, $y, $z),
			self::grad($this->perm[$BA], $x - 1, $y, $z)),
			self::lerp($fX, self::grad($this->perm[$AB], $x, $y - 1, $z),
			self::grad($this->perm[$BB], $x - 1, $y - 1, $z))),
			self::lerp($fY, self::lerp($fX, self::grad($this->perm[$AA + 1], $x, $y, $z - 1),
			self::grad($this->perm[$BA + 1], $x - 1, $y, $z - 1)),
			self::lerp($fX, self::grad($this->perm[$AB + 1], $x, $y - 1, $z - 1),
			self::grad($this->perm[$BB + 1], $x - 1, $y - 1, $z - 1))));
	}
	
	public function getNoise2D($x, $y){
		return $this->getNoise3D($x, $y, 0);
	}
}