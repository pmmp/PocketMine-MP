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
use function define;
use function defined;
use function ord;
use function str_repeat;
use function strlen;

if(!defined(__NAMESPACE__ . '\ZERO_NIBBLE_ARRAY')){
	define(__NAMESPACE__ . '\ZERO_NIBBLE_ARRAY', str_repeat("\x00", 2048));
}
if(!defined(__NAMESPACE__ . '\FIFTEEN_NIBBLE_ARRAY')){
	define(__NAMESPACE__ . '\FIFTEEN_NIBBLE_ARRAY', str_repeat("\xff", 2048));
}

final class LightArray{

	private const ZERO = ZERO_NIBBLE_ARRAY;
	private const FIFTEEN = FIFTEEN_NIBBLE_ARRAY;

	/** @var string */
	private $data;

	public function __construct(string $payload){
		if(($len = strlen($payload)) !== 2048){
			throw new \InvalidArgumentException("Payload size must be 2048 bytes, but got $len bytes");
		}

		$this->data = $payload;
		$this->collectGarbage();
	}

	public static function fill(int $level) : self{
		if($level === 0){
			return new self(self::ZERO);
		}
		if($level === 15){
			return new self(self::FIFTEEN);
		}
		return new self(str_repeat(chr(($level & 0x0f) | ($level << 4)), 2048));
	}

	public function get(int $x, int $y, int $z) : int{
		return (ord($this->data[($x << 7) | ($z << 3) | ($y >> 1)]) >> (($y & 1) << 2)) & 0xf;
	}

	public function set(int $x, int $y, int $z, int $level) : void{
		$i = ($x << 7) | ($z << 3) | ($y >> 1);

		$shift = ($y & 1) << 2;
		$byte = ord($this->data[$i]);
		$this->data[$i] = chr(($byte & ~(0xf << $shift)) | (($level & 0xf) << $shift));
	}

	public function collectGarbage() : void{
		/*
		 * This strange looking code is designed to exploit PHP's copy-on-write behaviour. Assigning will copy a
		 * reference to the const instead of duplicating the whole string. The string will only be duplicated when
		 * modified, which is perfect for this purpose.
		 */
		if($this->data === self::ZERO){
			$this->data = self::ZERO;
		}elseif($this->data === self::FIFTEEN){
			$this->data = self::FIFTEEN;
		}
	}

	public function getData() : string{
		return $this->data;
	}

	public function __wakeup(){
		//const refs aren't preserved when unserializing
		$this->collectGarbage();
	}
}
