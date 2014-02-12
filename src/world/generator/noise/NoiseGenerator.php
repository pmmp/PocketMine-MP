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


abstract class NoiseGenerator{
	protected $perm = array();
	protected $offsetX;
	protected $offsetY;
	protected $offsetZ;
	
	public static function floor($x){
		return $x >= 0 ? (int) $x : (int) $x - 1;
	}
	
	public static function fade($x){
		return $x * $x * $x * ($x * ($x * 6 - 15) + 10);
	}
	
	public static function lerp($x, $y, $z){
		return $y + $x * ($z - $y);
	}
	
	public static function grad($hash, $x, $y, $z){
		$hash &= 15;
		$u = $hash < 8 ? $x : $y;
		$v = $hash < 4 ? $y : (($hash === 12 or $hash === 14) ? $x : $z);
		return (($hash & 1) === 0 ? $u : -$u) + (($hash & 2) === 0 ? $v : -$v);
	}
	
	abstract public function noise($x, $y, $z);
	
	public function noise3D($x, $y, $z, $octaves, $frequency, $amplitude, $normalized = false){	
		$result = 0;
		$amp = 1;
		$freq = 1;
		$max = 0;
		
		for($i = 0; $i < $octaves; ++$i){
			$result += $this->noise($x * $freq, $y * $freq, $z * $freq) * $amp;
			$max += $amp;
			$freq *= $frequency;
			$amp *= $amplitude;
		}
		if($normalized === true){
			$result /= $max;
		}
			
		return $result;
}