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

/**
 * @phpstan-template TValue
 * @phpstan-template TRaw of int|string
 */
interface StateMap{

	/**
	 * @phpstan-return array<TRaw, TValue>
	 */
	public function getRawToValueMap() : array;

	/**
	 * @phpstan-param TValue $value
	 * @phpstan-return TRaw
	 */
	public function valueToRaw(mixed $value) : int|string;

	/**
	 * @phpstan-param TRaw $raw
	 * @phpstan-return TValue|null
	 */
	public function rawToValue(int|string $raw) : mixed;

	/**
	 * @phpstan-param TValue $value
	 */
	public function printableValue(mixed $value) : string;
}
