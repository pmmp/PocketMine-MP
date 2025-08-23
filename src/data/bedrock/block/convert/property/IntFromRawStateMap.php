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

use function array_flip;
use function is_array;

/**
 * @phpstan-template TRaw of int|string
 * @phpstan-implements StateMap<int, TRaw>
 */
class IntFromRawStateMap implements StateMap{

	/**
	 * @var int[]
	 * @phpstan-var array<TRaw, int>
	 */
	private array $deserializeMap;

	/**
	 * Constructs a bidirectional mapping, given a mapping of internal values -> serialized values, and an optional set
	 * of aliases per internal value (used for deserializing invalid serialized values).
	 *
	 * @param (int|string)[]                $serializeMap
	 * @param (int|int[])|(string|string[]) $deserializeAliases
	 *
	 * @phpstan-param array<int, TRaw>              $serializeMap
	 * @phpstan-param array<int, TRaw|list<TRaw>> $deserializeAliases
	 */
	public function __construct(
		private array $serializeMap,
		array $deserializeAliases = []
	){
		$this->deserializeMap = array_flip($this->serializeMap);
		foreach($deserializeAliases as $pmValue => $mcValues){
			if(!is_array($mcValues)){
				$this->deserializeMap[$mcValues] = $pmValue;
			}else{
				foreach($mcValues as $mcValue){
					$this->deserializeMap[$mcValue] = $pmValue;
				}
			}
		}
	}

	/**
	 * @param int[]       $serializeMap
	 * @param (int|int[]) $deserializeAliases
	 *
	 * @phpstan-param array<int, int>              $serializeMap
	 * @phpstan-param array<int, int|list<int>> $deserializeAliases
	 *
	 * @phpstan-return self<int>
	 */
	public static function int(array $serializeMap, array $deserializeAliases = []) : self{ return new self($serializeMap, $deserializeAliases); }

	/**
	 * @param string[]          $serializeMap
	 * @param (string|string[]) $deserializeAliases
	 *
	 * @phpstan-param array<int, string>              $serializeMap
	 * @phpstan-param array<int, string|list<string>> $deserializeAliases
	 *
	 * @phpstan-return self<string>
	 */
	public static function string(array $serializeMap, array $deserializeAliases = []) : self{ return new self($serializeMap, $deserializeAliases); }

	public function getRawToValueMap() : array{
		return $this->deserializeMap;
	}

	public function valueToRaw(mixed $value) : int|string{
		return $this->serializeMap[$value];
	}

	public function rawToValue(int|string $raw) : mixed{
		return $this->deserializeMap[$raw] ?? null;
	}

	public function printableValue(mixed $value) : string{
		return "$value";
	}
}
