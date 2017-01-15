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

declare(strict_types = 1);

namespace pocketmine\level\format\io;

class ChunkUtils{

	/**
	 * Re-orders a byte array (YZX -> XZY and vice versa)
	 *
	 * @param string $array length 4096
	 *
	 * @return string length 4096
	 */
	public static final function reorderByteArray(string $array) : string{
		$result = str_repeat("\x00", 4096);
		$i = 0;
		for($x = 0; $x < 16; ++$x){
			for($z = 0; $z < 256; $z += 16){
				$zx = ($z + $x);
				for($y = 0; $y < 4096; $y += 256){
					$result{$i} = $array{$y + $zx};
					++$i;
				}
			}
		}
		return $result;
	}

	/**
	 * Re-orders a nibble array (YZX -> XZY and vice versa)
	 *
	 * @param string $array length 2048
	 *
	 * @return string length 2048
	 */
	public static final function reorderNibbleArray(string $array) : string{
		$result = str_repeat("\x00", 2048);
		$i = 0;
		for($x = 0; $x < 8; ++$x){
			for($z = 0; $z < 16; ++$z){
				$zx = (($z << 3) | $x);
				for($y = 0; $y < 8; ++$y){
					$j = (($y << 8) | $zx);
					$i1 = ord($array{$j});
					$i2 = ord($array{$j | 0x80});
					$result{$i}        = chr(($i2 << 4) | ($i1 & 0x0f));
					$result{$i | 0x80} = chr(($i1 >> 4) | ($i2 & 0xf0));
					$i++;
				}
			}
			$i += 128;
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