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

namespace pocketmine\level\format\io;

use function chr;
use function extension_loaded;
use function ord;
use function str_repeat;

if(!extension_loaded('pocketmine_chunkutils')){
	class ChunkUtils{

		/**
		 * Re-orders a byte array (YZX -> XZY and vice versa)
		 *
		 * @param string $array length 4096
		 *
		 * @return string length 4096
		 */
		final public static function reorderByteArray(string $array) : string{
			$result = str_repeat("\x00", 4096);
			if($array !== $result){
				$i = 0;
				for($x = 0; $x < 16; ++$x){
					$zM = $x + 256;
					for($z = $x; $z < $zM; $z += 16){
						$yM = $z + 4096;
						for($y = $z; $y < $yM; $y += 256){
							$result{$i} = $array{$y};
							++$i;
						}
					}
				}
			}

			return $result;
		}

		/**
		 * Re-orders a nibble array (YZX -> XZY and vice versa)
		 *
		 * @param string $array length 2048
		 * @param string $commonValue length 1 common value to fill the default array with and to expect, may improve sort time
		 *
		 * @return string length 2048
		 */
		final public static function reorderNibbleArray(string $array, string $commonValue = "\x00") : string{
			$result = str_repeat($commonValue, 2048);

			if($array !== $result){
				$i = 0;
				for($x = 0; $x < 8; ++$x){
					for($z = 0; $z < 16; ++$z){
						$zx = (($z << 3) | $x);
						for($y = 0; $y < 8; ++$y){
							$j = (($y << 8) | $zx);
							$j80 = ($j | 0x80);
							if($array{$j} === $commonValue and $array{$j80} === $commonValue){
								//values are already filled
							}else{
								$i1 = ord($array{$j});
								$i2 = ord($array{$j80});
								$result{$i}        = chr(($i2 << 4) | ($i1 & 0x0f));
								$result{$i | 0x80} = chr(($i1 >> 4) | ($i2 & 0xf0));
							}
							$i++;
						}
					}
					$i += 128;
				}
			}

			return $result;
		}

		/**
		 * Converts pre-MCPE-1.0 biome color array to biome ID array.
		 *
		 * @param int[] $array of biome color values
		 *
		 * @return string
		 */
		public static function convertBiomeColors(array $array) : string{
			$result = str_repeat("\x00", 256);
			foreach($array as $i => $color){
				$result{$i} = chr(($color >> 24) & 0xff);
			}
			return $result;
		}

	}
}
