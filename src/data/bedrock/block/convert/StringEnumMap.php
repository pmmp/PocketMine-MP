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

namespace pocketmine\data\bedrock\block\convert;

use pocketmine\data\bedrock\block\BlockStateDeserializeException;
use function spl_object_id;

/**
 * @phpstan-template TEnum of \UnitEnum
 */
class StringEnumMap{
	/**
	 * @var string[]
	 * @phpstan-var array<int, string>
	 */
	private array $enumToValue = [];

	/**
	 * @var \UnitEnum[]
	 * @phpstan-var array<string, TEnum>
	 */
	private array $valueToEnum = [];

	/**
	 * @phpstan-param class-string<TEnum> $class
	 * @phpstan-param \Closure(TEnum) : string $mapper
	 */
	public function __construct(
		private string $class,
		\Closure $mapper
	){
		foreach($class::cases() as $case){
			$string = $mapper($case);
			$this->valueToEnum[$string] = $case;
			$this->enumToValue[spl_object_id($case)] = $string;
		}
	}

	/**
	 * @phpstan-param TEnum $enum
	 */
	public function enumToValue(\UnitEnum $enum) : string{
		return $this->enumToValue[spl_object_id($enum)];
	}

	/**
	 * @phpstan-return TEnum|null
	 */
	public function valueToEnum(string $string) : ?\UnitEnum{
		return $this->valueToEnum[$string] ?? throw new BlockStateDeserializeException("No $this->class enum mapping for \"$string\"");
	}

	/**
	 * @return \UnitEnum[]
	 * @phpstan-return array<string, TEnum>
	 */
	public function getValueToEnum() : array{
		return $this->valueToEnum;
	}
}
