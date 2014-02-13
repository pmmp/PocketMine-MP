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


abstract class OctaveGenerator{
	protected $octaves;
	protected $xScale = 1;
	protected $yScale = 1;
	protected $zScale = 1;
	
	public function __construct(array $octaves){
		$this->octaves = $octaves;
	}
	
	public function setScale($scale){
		$this->setXScale($scale);
		$this->setYScale($scale);
		$this->setZScale($scale);
	}
	
	public function getXScale(){
		return $this->xScale;
	}
	
	public function setXScale($scale){
		$this->xScale = $scale;
	}
	
	public function getYScale(){
		return $this->yScale;
	}
	
	public function setYScale($scale){
		$this->yScale = $scale;
	}
	
	public function getZScale(){
		return $this->zScale;
	}
	
	public function setZScale($scale){
		$this->zScale = $scale;
	}
	
	public function getOctaves(){
		$array = array();
		foreach($this->octaves as $index => $value){
			$array[$index] = clone $value;
		}
		return $array;
	}
	
	//1D-noise
	public function noise1D($x, $frequency, $amplitude, $normalized = false){
		return $this->noise3D($x, 0, 0, $frequency, $amplitude, $normalized);
	}
	
	//2D-noise
	public function noise2D($x, $y, $frequency, $amplitude, $normalized = false){
		return $this->noise3D($x, $y, 0, $frequency, $amplitude, $normalized);
	}
	
	//3D-noise
	public function noise3D($x, $y, $z, $frequency, $amplitude, $normalized = false){
		$result = 0;
		$amp = 1;
		$freq = 1;
		$max = 0;
		
		$x *= $this->xScale;
		$y *= $this->yScale;
		$z *= $this->zScale;
		
		foreach($this->octaves as $noiseGenerator){
			$result += $octave->noise($x * $freq, $y * $freq, $z * $freq) * $amp;
			$max += $amp;
			$freq *= $frequency;
			$amp *= $amplitude;
		}
		if($normalized === true){
			$result /= $max;
		}
			
		return $result;
	}
	
	/*public function generateNoiseOctaves($x, $y, $z, $frequency, $amplitude){
		
	}*/
}