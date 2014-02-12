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
require_once("OctaveGenerator.php");
/***REM_END***/

class PerlinOctaveGenerator extends OctaveGenerator{
	public function __construct(Random $random, $octaves){
		$this->octaves = array();
		for($o = 0; $o < $octaves; ++$o){
			$this->octaves[$o] = new NoiseGeneratorPerlin($random);
		}
	}
	
	/*public function generateNoiseOctaves($x, $y, $z, $sizeX, $sizeY, $sizeZ, $fX, $fY, $fZ){
		$adouble = array_fill(0, $sizeX * $sizeY * $sizeZ, 0.0);
		
		$d3 = 1.0;
		
		foreach($this->octaves as $octave){
			$dX = $x * $d3 * $fX;
			$dY = $y * $d3 * $fY;
			$dZ = $x * $d3 * $fZ;
			
			$x1 = NoiseGenerator::floor($dX);
			$z1 = NoiseGenerator::floor($dZ);
			
			$dX -= $x1;
			$dZ -= $z1;
			
			$x1 %= 16777216;
			$z1 %= 16777216;
			//$x1 &= 0xFFFFFF;
			//$z1 &= 0xFFFFFF;
			
			$dX += $x1;
			$dZ += $z1;
			$octave->populateNoiseArray($adouble, $dX, $dY, $dZ, $sizeX, $sizeY, $sizeZ, $fX * $d3, $fY * $d3, $fZ * $d3, $d3);
			$d3 *= 0.5;
		}
		
		return $adouble;
	}*/
}