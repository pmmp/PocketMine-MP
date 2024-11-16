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

namespace pocketmine\utils;

use function count;
use function mb_strtoupper;
use function spl_object_id;

/**
 * Offers a shim to make a native enum behave similarly to a legacy {@link EnumTrait} enum.
 * Used to provide backwards compatibility for enums that have been migrated to native PHP 8.1 enums.
 *
 * @deprecated
 */
trait LegacyEnumShimTrait{

	/**
	 * @param mixed[] $arguments
	 */
	public static function __callStatic(string $name, array $arguments) : self{
		if(count($arguments) > 0){
			throw new \ArgumentCountError("Expected exactly 0 arguments, " . count($arguments) . " passed");
		}
		return self::getAll()[mb_strtoupper($name)];
	}

	/**
	 * Returns a list of all cases, indexed by name.
	 *
	 * @return self[]
	 * @phpstan-return array<string, self>
	 */
	public static function getAll() : array{
		/** @var array<string, self>|null $result */
		static $result = null;
		if($result === null){
			$result = [];
			foreach(self::cases() as $case){
				$result[mb_strtoupper($case->name)] = $case;
			}
		}
		return $result;
	}

	/**
	 * Shim for {@link \UnitEnum::name}.
	 *
	 * @deprecated Use the native enum's name property instead.
	 */
	public function name() : string{
		return $this->name;
	}

	/**
	 * Alias of spl_object_id($this).
	 *
	 * @deprecated
	 */
	public function id() : int{
		return spl_object_id($this);
	}

	/**
	 * Returns whether the two objects are equivalent.
	 *
	 * @deprecated Native enums can be safely compared with ===.
	 */
	public function equals(self $other) : bool{
		return $this === $other;
	}
}
