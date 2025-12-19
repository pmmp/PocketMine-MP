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

namespace pocketmine\command\overload;

use function max;
use function min;
use function preg_match;
use function strlen;

final class RelativeFloat{

	public function __construct(
		private float $value,
		private bool $relative
	){}

	public function getValue() : float{ return $this->value; }

	public function isRelative() : bool{ return $this->relative; }

	public function resolve(float $base, float $min, float $max) : float{
		//TODO: this should probably bail on out of bounds values
		return min($max, max($min, $this->relative ? $base + $this->value : $this->value));
	}

	/**
	 * @throws ParameterParseException
	 */
	public static function parse(string $buffer, int &$offset) : self{
		if(preg_match('/\G(~)?(-?\d+\.?\d*)?/', $buffer, $matches, offset: $offset) > 0){
			$relativeRaw = $matches[1] ?? "";
			$valueRaw = $matches[2] ?? "";
			if($valueRaw !== "" || $relativeRaw !== ""){
				$offset += strlen($matches[0]);
				$relative = $relativeRaw === "~";
				$value = (float) $valueRaw;
				return new RelativeFloat($value, $relative);
			}
		}

		throw new ParameterParseException("Expected a float, possibly preceded by a ~ symbol");
	}
}
