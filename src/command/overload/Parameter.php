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

use DaveRandom\CallbackValidator\Type\BaseType;
use pocketmine\lang\Translatable;

/**
 * @phpstan-template TValue
 */
abstract class Parameter{

	public function __construct(
		private string $codeName,
		private Translatable|string $printableName,
		private BaseType $codeType,
	){}

	public function getCodeName() : string{
		return $this->codeName;
	}

	public function getPrintableName() : Translatable|string{
		return $this->printableName;
	}

	public function getCodeType() : BaseType{
		return $this->codeType;
	}

	/**
	 * Returns whether this command will consume all remaining inputs.
	 */
	public function consumesAllRemainingInputs() : bool{
		return false;
	}

	/**
	 * The given string will be stripped of whitespace at the start
	 *
	 * @phpstan-return TValue
	 * @throws ParameterParseException
	 */
	abstract public function parse(string $buffer, int &$offset) : mixed;

	public function getSuffix() : string{
		return "";
	}
}
