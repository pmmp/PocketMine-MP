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
 * TODO: it would be nice not to duplicate this code from EnumFromStringStateMap, but this will do for now
 * @phpstan-template TEnum of \UnitEnum
 */
class EnumFromIntStateMap{
	/**
	 * @var int[]
	 * @phpstan-var array<int, int>
	 */
	private array $enumToValue = [];

	/**
	 * @var \UnitEnum[]
	 * @phpstan-var array<int, TEnum>
	 */
	private array $valueToEnum = [];

	/**
	 * @phpstan-param class-string<TEnum> $class
	 * @phpstan-param \Closure(TEnum) : int $mapper
	 * @phpstan-param ?\Closure(TEnum) : list<int> $aliasMapper
	 */
	public function __construct(
		private string $class,
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
	 * @phpstan-param TEnum $enum
	 */
	public function enumToValue(\UnitEnum $enum) : int{
		return $this->enumToValue[spl_object_id($enum)];
	}

	/**
	 * @phpstan-return TEnum|null
	 */
	public function valueToEnum(int $int) : ?\UnitEnum{
		return $this->valueToEnum[$int] ?? throw new BlockStateDeserializeException("No $this->class enum mapping for value $int");
	}

	/**
	 * @return \UnitEnum[]
	 * @phpstan-return array<int, TEnum>
	 */
	public function getValueToEnum() : array{
		return $this->valueToEnum;
	}
}
