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
use function strlen;
use function substr;

/**
 * @phpstan-extends Parameter<string>
 */
final class RawParameter extends Parameter{

	public function __construct(string $codeName, Translatable|string $printableName){
		parent::__construct(
			$codeName,
			$printableName,
			new NamedType(BuiltInType::STRING)
		);
	}

	public function consumesAllRemainingInputs() : bool{
		return true;
	}

	public function parse(string $buffer, int &$offset) : string{
		$value = substr($buffer, $offset);
		$offset += strlen($value);
		return $value;
	}
}
