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

namespace pocketmine\data\bedrock\block\convert\property;

use function spl_object_id;

/**
 * @phpstan-template TEnum of \UnitEnum
 * @phpstan-template TRaw of int|string
 * @phpstan-implements StateMap<TEnum, TRaw>
 */
class EnumFromRawStateMap implements StateMap{
	/**
	 * @var int[]
	 * @phpstan-var array<int, TRaw>
	 */
	private array $enumToValue = [];

	/**
	 * @var \UnitEnum[]
	 * @phpstan-var array<TRaw, TEnum>
	 */
	private array $valueToEnum = [];

	/**
	 * @phpstan-param class-string<TEnum> $class
	 * @phpstan-param \Closure(TEnum) : TRaw $mapper
	 * @phpstan-param ?\Closure(TEnum) : list<TRaw> $aliasMapper
	 */
	public function __construct(
		string $class,
		\Closure $mapper,
		?\Closure $aliasMapper = null
	){
		foreach($class::cases() as $case){
			$int = $mapper($case);
			$this->valueToEnum[$int] = $case;
			$this->enumToValue[spl_object_id($case)] = $int;

			if($aliasMapper !== null){
				$aliases = $aliasMapper($case);
				foreach($aliases as $alias){
					$this->valueToEnum[$alias] = $case;
				}
			}
		}
	}

	/**
	 * Workaround PHPStan too-specific literal type inference - if it ever gets fixed we can get rid of these functions
	 *
	 * @phpstan-template TEnum_ of \UnitEnum
	 * @phpstan-param class-string<TEnum_> $class
	 * @param \Closure(TEnum_) : string        $mapper
	 * @param ?\Closure(TEnum_) : list<string> $aliasMapper
	 *
	 * @phpstan-return EnumFromRawStateMap<TEnum_, string>
	 */
	public static function string(string $class, \Closure $mapper, ?\Closure $aliasMapper = null) : self{ return new self($class, $mapper, $aliasMapper); }

	/**
	 * Workaround PHPStan too-specific literal type inference - if it ever gets fixed we can get rid of these functions
	 *
	 * @phpstan-template TEnum_ of \UnitEnum
	 * @phpstan-param class-string<TEnum_> $class
	 * @param \Closure(TEnum_) : int        $mapper
	 * @param ?\Closure(TEnum_) : list<int> $aliasMapper
	 *
	 * @phpstan-return EnumFromRawStateMap<TEnum_, int>
	 */
	public static function int(string $class, \Closure $mapper, ?\Closure $aliasMapper = null) : self{ return new self($class, $mapper, $aliasMapper); }

	public function getRawToValueMap() : array{
		return $this->valueToEnum;
	}

	public function valueToRaw(mixed $value) : int|string{
		return $this->enumToValue[spl_object_id($value)];
	}

	public function rawToValue(int|string $raw) : ?\UnitEnum{
		return $this->valueToEnum[$raw] ?? null;
	}

	public function printableValue(mixed $value) : string{
		return $value::class . "::" . $value->name;
	}
}
