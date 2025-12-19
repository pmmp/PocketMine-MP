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

use DaveRandom\CallbackValidator\Type\BuiltInType;
use DaveRandom\CallbackValidator\Type\NamedType;
use pocketmine\lang\Translatable;
use function preg_match;
use function strlen;

/**
 * @phpstan-extends Parameter<float>
 */
final class FloatRangeParameter extends Parameter{

	public function __construct(
		string $codeName,
		Translatable|string $printableName,
		private float $min,
		private float $max
	){
		parent::__construct($codeName, $printableName, new NamedType(BuiltInType::FLOAT));
	}

	public function parse(string $buffer, int &$offset) : float{
		if(preg_match('/\G(-?\d+\.?\d*)/', $buffer, $matches, offset: $offset) > 0){
			$offset += strlen($matches[0]);
			$value = (float) $matches[0];
			if($value < $this->min || $value > $this->max){
				//TODO: we should probably use localised messages for this, but they probably won't be seen by the user
				//anyway since we'll try all the overloads before giving up
				throw new ParameterParseException("Value must be in the range $this->min ... $this->max");
			}

			return $value;
		}

		throw new ParameterParseException("Expected a float in the range $this->min ... $this->max");
	}
}
