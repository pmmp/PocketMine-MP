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
	private $permutations = array();
	public $xCoord, $yCoord, $zCoord;
	
	public function __construct($random = false){
		if(!($random instanceof Random)){
			$random = new Random();
		}
		$this->xCoord = $random->nextFloat() * 256;
		$this->yCoord = $random->nextFloat() * 256;
		$this->zCoord = $random->nextFloat() * 256;

		for($i = 0; $i < 512; ++$i){
			$this->permutations[$i] = 0;
		}
		for($i = 0; $i < 256; ++$i){
			$this->permutations[$i] = $i;
		}
		
		for($i = 0; $i < 256; ++$i){
			$j = $random->nextRange(0, 256 - $i) + $i;
			$k = $this->permutations[$i];
			$this->permutations[$i] = $this->permutations[$j];
			$this->permutations[$j] = $k;
			$this->permutations[$i + 256] = $this->permutations[$i];
		}
		
	}
	
	public final function curve($par1, $par2, $par3){
		return $par2 + $par1 * ($par3 - $par2);
	}
	
	public function grad2D($int, $par1, $par2){
		$i = $int & 0x0F;
		$d1 = (1 - (($i & 0x08) >> 3)) * $par1;
		$d2 = ($i === 12 or $i === 14) ? $par1:($i < 4 ? 0:$par2);
		
		return (($i & 0x01) === 0 ? $d1:-$d1) + (($i & 0x02) === 0 ? $d2:-$d2);
	}
	
	public function grad3D($int, $par1, $par2, $par3){
		$i = $int & 0x0F;
		$d1 = $i < 8 ? $par1 : $par2;
		$d2 = ($i === 12 or $i === 14) ? $par1:($i < 4 ? $par2:$par3);
		
		return (($i & 0x01) === 0 ? $d1:-$d1) + (($i & 0x02) === 0 ? $d2:-$d2);
	}
	
	public function populateNoiseArray(&$floats, $par1, $par2, $par3, $int1, $int2, $int3, $par4, $par5, $par6, $par7){
		if($int2 === 1){
			$n = 0;
			$d3 = 1 / $par7;
			for($i1 = 0; $i1 < $int1; ++$i1){
				$d4 = $par1 + $i1 * $par4 + $this->xCoord;
				$i2 = (int) $d4;
				if($d4 < $i2){
					--$i2;
				}
				$i3 = $i2 & 0xFF;
				$d4 -= $i2;
				$d5 = $d4 * $d4 * $d4 * ($d4 * ($d4 * 6 - 15) + 10);
				
				for($i4 = 0; $i4 < $int3; ++$i4){
					$d6 = $par3 + $i4 * $par6 + $this->zCoord;
					$i5 = (int) $d6;
					if($d6 < $i5){
						--$i5;
					}
					$i6 = $i5 & 0xFF;
					$d6 -= $i5;
					$d7 = $d6 * $d6 * $d6 * ($d6 * ($d6 * 6 - 15) + 10);
					
					$i = $this->permutations[$i3];
					$j = $this->permutations[$i] + $i6;
					$k = $this->permutations[$i3 + 1];
					$m = $this->permutations[$k] + $i6;
					$d1 = $this->curve($d5, $this->grad2D($this->permutations[$j], $d4, $d6), $this->grad3D($this->permutations[$m], $d4 - 1, 0, $d6));
					$d2 = $this->curve($d5, $this->grad3D($this->permutations[$j + 1], $d4, 0, $d6 - 1), $this->grad3D($this->permutations[$m + 1], $d4 - 1, 0, $d6 - 1));
					
					$d8 = $this->curve($d7, $d1, $d2);
					$floats[$n++] += $d8 * $d3;
				}
			}
			return;
		}

		$d9 = 1 / $par7;
		$m = -1;
		$n = 0;
		$i = 0;
		
		for($i4 = 0; $i4 < $int1; ++$i4){
			$d6 = $par1 + $i4 * $par4 + $this->xCoord;
			$i5 = (int) $d6;
			if($d6 < $i5){
				--$i5;
			}
			$i6 = $i5 & 0xFF;
			$d6 -= $i5;
			$d7 = $d6 * $d6 * $d6 * ($d6 * ($d6 * 6 - 15) + 10);
			
			for($i12 = 0; $i12 < $int3; ++$i12){
				$d12 = $par3 + $i12 * $par6 + $this->zCoord;
				$i13 = (int) $d12;
				if($d12 < $i13){
					--$i13;
				}
				$i14 = $i13 & 0xFF;
				$d12 -= $i13;
				$d13 = $d12 * $d12 * $d12 * ($d12 * ($d12 * 6 - 15) + 10);
				
				for($i15 = 0; $i15 < $int2; ++$i15){
					$d14 = $par2 + $i15 * $par5 + $this->yCoord;
					$i16 = (int) $d14;
					if($d14 < $i16){
						--$i16;
					}
					$d14 -= $i16;
					$d15 = $d14 * $d14 * $d14 * ($d14 * ($d14 * 6 - 15) + 10);

					if($i15 === 0 or $i17 !== $m){
						$m = $i17;
						$i7 = $this->permutations[$i6] + $i17;
						$i8 = $this->permutations[$i7] + $i14;
						$i9 = $this->permutations[$i7 + 1] + $i14;
						$i10 = $this->permutations[$i6 + 1] + $i17;
						$n = $this->permutations[$i10] + $i14;
						$i11 = $this->permutations[$i10 + 1] + $i14;
						$d10 = $this->curve($d7, $this->grad3D($this->permutations[$i8], $d6, $d14, $d12), $this->grad3D($this->permutations[$n], $d6 - 1, $d14, $d12));
						$d4 = $this->curve($d7, $this->grad3D($this->permutations[$i9], $d6, $d14 - 1, $d12), $this->grad3D($this->permutations[$i11], $d6 - 1, $d14 - 1, $d12));
						$d11 = $this->curve($d7, $this->grad3D($this->permutations[$i8 + 1], $d6, $d14, $d12 - 1), $this->grad3D($this->permutations[$n + 1], $d6 - 1, $d14, $d12 - 1));
						$d5 = $this->curve($d7, $this->grad3D($this->permutations[$i9 + 1], $d6, $d14 - 1, $d12 - 1), $this->grad3D($this->permutations[$i11 + 1], $d6 - 1, $d14 - 1, $d12 - 1));
					}
					
					$d16 = $this->curve($d15, $d10, $d4);
					$d17 = $this->curve($d15, $d11, $d5);
					$d18 = $this->curve($d13, $d16, $d17);
					$floats[$i++] += $d18 * $d9;
				}
			}
		}
	}
}