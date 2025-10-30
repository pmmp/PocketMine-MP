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

/**
 * Different noise generators for world generation
 */
namespace pocketmine\world\generator\noise;

use function array_fill;
use function assert;

abstract class Noise{

	/**
	 * @param float $x
	 * @param float $x1
	 * @param float $x2
	 * @param float $q0
	 * @param float $q1
	 *
	 * @return float
	 */
	public static function linearLerp($x, $x1, $x2, $q0, $q1){
		return (($x2 - $x) / ($x2 - $x1)) * $q0 + (($x - $x1) / ($x2 - $x1)) * $q1;
	}

	/**
	 * @param float $x
	 * @param float $y
	 * @param float $q00
	 * @param float $q01
	 * @param float $q10
	 * @param float $q11
	 * @param float $x1
	 * @param float $x2
	 * @param float $y1
	 * @param float $y2
	 *
	 * @return float
	 */
	public static function bilinearLerp($x, $y, $q00, $q01, $q10, $q11, $x1, $x2, $y1, $y2){
		$dx1 = (($x2 - $x) / ($x2 - $x1));
		$dx2 = (($x - $x1) / ($x2 - $x1));

		return (($y2 - $y) / ($y2 - $y1)) * (
			$dx1 * $q00 + $dx2 * $q10
		) + (($y - $y1) / ($y2 - $y1)) * (
			$dx1 * $q01 + $dx2 * $q11
		);
	}

	/**
	 * @param float $x
	 * @param float $y
	 * @param float $z
	 * @param float $q000
	 * @param float $q001
	 * @param float $q010
	 * @param float $q011
	 * @param float $q100
	 * @param float $q101
	 * @param float $q110
	 * @param float $q111
	 * @param float $x1
	 * @param float $x2
	 * @param float $y1
	 * @param float $y2
	 * @param float $z1
	 * @param float $z2
	 *
	 * @return float
	 */
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

	public function __construct(
		protected int $octaves,
		protected float $persistence,
		protected float $expansion
	){}

	/**
	 * @param float $x
	 * @param float $z
	 *
	 * @return float
	 */
	abstract public function getNoise2D($x, $z);

	/**
	 * @param float $x
	 * @param float $y
	 * @param float $z
	 *
	 * @return float
	 */
	abstract public function getNoise3D($x, $y, $z);

	/**
	 * @param float $x
	 * @param float $z
	 * @param bool  $normalized
	 *
	 * @return float
	 */
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

	/**
	 * @param float $x
	 * @param float $y
	 * @param float $z
	 * @param bool  $normalized
	 *
	 * @return float
	 */
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

	/**
	 * @return \SplFixedArray|float[]
	 * @phpstan-return \SplFixedArray<float>
	 */
	public function getFastNoise1D(int $xSize, int $samplingRate, int $x, int $y, int $z) : \SplFixedArray{
		if($samplingRate === 0){
			throw new \InvalidArgumentException("samplingRate cannot be 0");
		}
		if($xSize % $samplingRate !== 0){
			throw new \InvalidArgumentException("xSize % samplingRate must return 0");
		}

		/** @phpstan-var \SplFixedArray<float> $noiseArray */
		$noiseArray = new \SplFixedArray($xSize + 1);

		for($xx = 0; $xx <= $xSize; $xx += $samplingRate){
			$noiseArray[$xx] = $this->noise3D($xx + $x, $y, $z);
		}

		for($xx = 0; $xx < $xSize; ++$xx){
			if($xx % $samplingRate !== 0){
				$nx = (int) ($xx / $samplingRate) * $samplingRate;
				$noiseArray[$xx] = self::linearLerp(
					x: $xx,
					x1: $nx,
					x2: $nx + $samplingRate,
					q0: $noiseArray[$nx],
					q1: $noiseArray[$nx + $samplingRate]
				);
			}
		}

		return $noiseArray;
	}

	/**
	 * @return \SplFixedArray|float[][]
	 * @phpstan-return \SplFixedArray<\SplFixedArray<float>>
	 */
	public function getFastNoise2D(int $xSize, int $zSize, int $samplingRate, int $x, int $y, int $z) : \SplFixedArray{
		assert($samplingRate !== 0, new \InvalidArgumentException("samplingRate cannot be 0"));

		assert($xSize % $samplingRate === 0, new \InvalidArgumentException("xSize % samplingRate must return 0"));
		assert($zSize % $samplingRate === 0, new \InvalidArgumentException("zSize % samplingRate must return 0"));

		/** @phpstan-var \SplFixedArray<\SplFixedArray<float>> $noiseArray */
		$noiseArray = new \SplFixedArray($xSize + 1);

		for($xx = 0; $xx <= $xSize; $xx += $samplingRate){
			$noiseArray[$xx] = new \SplFixedArray($zSize + 1);
			for($zz = 0; $zz <= $zSize; $zz += $samplingRate){
				$noiseArray[$xx][$zz] = $this->noise3D($x + $xx, $y, $z + $zz);
			}
		}

		for($xx = 0; $xx < $xSize; ++$xx){
			if($xx % $samplingRate !== 0){
				$noiseArray[$xx] = new \SplFixedArray($zSize + 1);
			}

			for($zz = 0; $zz < $zSize; ++$zz){
				if($xx % $samplingRate !== 0 || $zz % $samplingRate !== 0){
					$nx = (int) ($xx / $samplingRate) * $samplingRate;
					$nz = (int) ($zz / $samplingRate) * $samplingRate;
					$noiseArray[$xx][$zz] = Noise::bilinearLerp(
						x: $xx,
						y: $zz,
						q00: $noiseArray[$nx][$nz],
						q01: $noiseArray[$nx][$nz + $samplingRate],
						q10: $noiseArray[$nx + $samplingRate][$nz],
						q11: $noiseArray[$nx + $samplingRate][$nz + $samplingRate],
						x1: $nx,
						x2: $nx + $samplingRate,
						y1: $nz,
						y2: $nz + $samplingRate
					);
				}
			}
		}

		return $noiseArray;
	}

	/**
	 * @return float[][][]
	 */
	public function getFastNoise3D(int $xSize, int $ySize, int $zSize, int $xSamplingRate, int $ySamplingRate, int $zSamplingRate, int $x, int $y, int $z) : array{

		assert($xSamplingRate !== 0, new \InvalidArgumentException("xSamplingRate cannot be 0"));
		assert($zSamplingRate !== 0, new \InvalidArgumentException("zSamplingRate cannot be 0"));
		assert($ySamplingRate !== 0, new \InvalidArgumentException("ySamplingRate cannot be 0"));

		assert($xSize % $xSamplingRate === 0, new \InvalidArgumentException("xSize % xSamplingRate must return 0"));
		assert($zSize % $zSamplingRate === 0, new \InvalidArgumentException("zSize % zSamplingRate must return 0"));
		assert($ySize % $ySamplingRate === 0, new \InvalidArgumentException("ySize % ySamplingRate must return 0"));

		$noiseArray = array_fill(0, $xSize + 1, array_fill(0, $zSize + 1, array_fill(0, $ySize + 1, 0)));

		for($xx = 0; $xx <= $xSize; $xx += $xSamplingRate){
			for($zz = 0; $zz <= $zSize; $zz += $zSamplingRate){
				for($yy = 0; $yy <= $ySize; $yy += $ySamplingRate){
					$noiseArray[$xx][$zz][$yy] = $this->noise3D($x + $xx, $y + $yy, $z + $zz, true);
				}
			}
		}

		/**
		 * The following code is equivalent to calling trilinearLerp inside 3 nested for loops. However, for bulk data
		 * processing, this is substantially faster, since many of the operations can be done significantly fewer times
		 * if the data is processed in an optimal order.
		 *
		 * TODO: Maybe we should consider extracting this into its own function, since trilinearLerp() is comically slow
		 *
		 * @see Noise::trilinearLerp()
		 */
		$xLerpStep = 1 / $xSamplingRate;
		$yLerpStep = 1 / $ySamplingRate;
		$zLerpStep = 1 / $zSamplingRate;

		for($leftX = 0; $leftX < $xSize; $leftX += $xSamplingRate){
			$rightX = $leftX + $xSamplingRate;

			for($leftZ = 0; $leftZ < $zSize; $leftZ += $zSamplingRate){
				$rightZ = $leftZ + $zSamplingRate;

				for($leftY = 0; $leftY < $ySize; $leftY += $ySamplingRate){
					$rightY = $leftY + $ySamplingRate;

					//Fetch the corner samples first - this avoids multidimensional array lookups in the inner loops,
					//which are slow
					$c000 = $noiseArray[$leftX][$leftZ][$leftY];
					$c100 = $noiseArray[$rightX][$leftZ][$leftY];
					$c001 = $noiseArray[$leftX][$leftZ][$rightY];
					$c101 = $noiseArray[$rightX][$leftZ][$rightY];
					$c010 = $noiseArray[$leftX][$rightZ][$leftY];
					$c110 = $noiseArray[$rightX][$rightZ][$leftY];
					$c011 = $noiseArray[$leftX][$rightZ][$rightY];
					$c111 = $noiseArray[$rightX][$rightZ][$rightY];

					//Now, lerp all the cells enclosed by the corner samples
					for($xStep = 0; $xStep < $xSamplingRate; $xStep++){
						$xx = $leftX + $xStep;
						$dx2 = $xStep * $xLerpStep;
						$dx1 = 1 - $dx2;

						//Lerp along the x axis first
						$x00 = ($c000 * $dx1) + ($c100 * $dx2);
						$x01 = ($c001 * $dx1) + ($c101 * $dx2);
						$x10 = ($c010 * $dx1) + ($c110 * $dx2);
						$x11 = ($c011 * $dx1) + ($c111 * $dx2);

						for($zStep = 0; $zStep < $zSamplingRate; $zStep++){
							$zz = $leftZ + $zStep;
							$dz2 = $zStep * $zLerpStep;
							$dz1 = 1 - $dz2;

							//Then, lerp the x lerped axis values along the z axis
							$z0 = $x00 * $dz1 + $x10 * $dz2;
							$z1 = $x01 * $dz1 + $x11 * $dz2;

							//Skip first row if these are both zero
							$yStart = $xStep === 0 && $zStep === 0 ? 1 : 0;
							for($yStep = $yStart; $yStep < $ySamplingRate; $yStep++){
								$yy = $leftY + $yStep;
								$dy2 = $yStep * $yLerpStep;
								$dy1 = 1 - $dy2;

								//Finally, lerp the x/z lerped values along the y axis
								$noiseArray[$xx][$zz][$yy] = $dy1 * $z0 + $dy2 * $z1;
							}
						}
					}
				}
			}
		}

		return $noiseArray;
	}
}
