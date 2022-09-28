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

namespace pocketmine\world\format;

use function chr;
use function ord;
use function str_repeat;
use function strlen;

final class BiomeArray{
	private string $payload;

	/**
	 * @param string $payload ZZZZXXXX key bits
	 */
	public function __construct(string $payload){
		if(strlen($payload) !== 256){
			throw new \InvalidArgumentException("Biome array is expected to be exactly 256 bytes");
		}
		$this->payload = $payload;
	}

	public static function fill(int $biomeId) : self{
		return new BiomeArray(str_repeat(chr($biomeId), 256));
	}

	private static function idx(int $x, int $z) : int{
		if($x < 0 || $x >= 16 || $z < 0 || $z >= 16){
			throw new \InvalidArgumentException("x and z must be in the range 0-15");
		}
		return ($z << 4) | $x;
	}

	public function get(int $x, int $z) : int{
		return ord($this->payload[self::idx($x, $z)]);
	}

	public function set(int $x, int $z, int $biomeId) : void{
		if($biomeId < 0 || $biomeId >= 256){
			throw new \InvalidArgumentException("Biome ID must be in the range 0-255");
		}
		$this->payload[self::idx($x, $z)] = chr($biomeId);
	}

	/**
	 * @return string ZZZZXXXX key bits
	 */
	public function getData() : string{
		return $this->payload;
	}
}
