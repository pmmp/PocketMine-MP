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

use DaveRandom\CallbackValidator\ParameterInfo;
use DaveRandom\CallbackValidator\Prototype;
use DaveRandom\CallbackValidator\ReturnInfo;
use DaveRandom\CallbackValidator\Type\BuiltInType;
use DaveRandom\CallbackValidator\Type\NamedType;
use pocketmine\command\utils\CommandStringHelper;
use pocketmine\lang\Translatable;
use pocketmine\utils\Utils;

/**
 * Looks up a string and converts it to some type of value.
 * The associated argument type will be inferred from the return type of the provided mapper function.
 *
 * @phpstan-template TValue
 * @phpstan-extends Parameter<TValue>
 */
final class MappedParameter extends Parameter{

	/**
	 * @phpstan-param \Closure(string): TValue $mapper
	 */
	public function __construct(
		string $codeName,
		Translatable|string $printableName,
		private \Closure $mapper
	){
		$givenPrototype = Prototype::fromClosure($this->mapper);
		$type = $givenPrototype->getReturnInfo()->type;
		if($type === null){
			throw new \InvalidArgumentException("Mapper callback must have a return type set");
		}
		$expectedPrototype = new Prototype(
			new ReturnInfo($type, byReference: false),
			new ParameterInfo("value", new NamedType(BuiltInType::STRING), byReference: false, isOptional: false, isVariadic: false)
		);
		Utils::validateCallableSignature($expectedPrototype, $givenPrototype);

		parent::__construct(
			$codeName,
			$printableName,
			$type
		);
	}

	public function parse(string $buffer, int &$offset) : mixed{
		$lookupKey = CommandStringHelper::parseQuoteAwareSingle($buffer, $offset) ?? throw new ParameterParseException("Unable to parse an argument from the buffer");

		return ($this->mapper)($lookupKey);
	}
}
