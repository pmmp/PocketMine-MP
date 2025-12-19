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
use function preg_quote;
use function strlen;

/**
 * @phpstan-extends Parameter<int>
 */
final class IntRangeParameter extends Parameter{

	public function __construct(
		string $codeName,
		Translatable|string $printableName,
		private int $min,
		private int $max,
		private string $suffix = ""
	){
		parent::__construct($codeName, $printableName, new NamedType(BuiltInType::INT));
	}

	public function parse(string $buffer, int &$offset) : int{
		if(preg_match('/\G-?\d+' . preg_quote($this->suffix, '/') . '/', $buffer, $matches, offset: $offset) > 0){
			$offset += strlen($matches[0]);
			$int = (int) $matches[0];
			if($int < $this->min || $int > $this->max){
				//TODO: we should probably use localised messages for this, but they probably won't be seen by the user
				//anyway since we'll try all the overloads before giving up
				throw new ParameterParseException("Value must be in the range $this->min ... $this->max");
			}

			return $int;
		}

		throw new ParameterParseException("Expected an integer in the range $this->min ... $this->max");
	}

	public function getSuffix() : string{
		return $this->suffix;
	}
}
