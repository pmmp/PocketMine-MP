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

namespace pocketmine\config;

use Iterator;

/**
 * A schema-sensitive configuration source
 */
interface ConfigSource{
	/**
	 * Returns the string value represented by this node,
	 * or null if type is unassignable.
	 */
	public function string() : ?string;

	/**
	 * Returns the int value represented by this node,
	 * or null if type is unassignable.
	 */
	public function int() : ?int;

	/**
	 * Returns the float value represented by this node,
	 * or null if type is unassignable.
	 */
	public function float() : ?float;

	/**
	 * Returns the boolean value represented by this node,
	 * or null if type is unassignable.
	 */
	public function bool() : ?bool;

	/**
	 * Returns the ConfigSource of the node under `$key` if this is a mapping,
	 * `null` otherwise or if `$key` does not exist.
	 */
	public function mapEntry(string $key) : ?ConfigSource;

	/**
	 * Returns an iterator of `string => ConfigSource` of the key-value pairs if this is a mapping,
	 * `null` otherwise.
	 * This may also return null if this type of source does not support traversal of all keys;
	 * returning null does not necessarily imply `mapEntry` cannot be used.
	 *
	 * The behaviour is undefined if the list is mutated during iteration.
	 *
	 * @return Iterator<string, ConfigSource>|null
	 */
	public function mapEntries() : ?Iterator;

	/**
	 * Returns the `ConfigSource` at index `$index` if this is a list,
	 * `null` otherwise or if this list has no more than `$index` elements.
	 */
	public function listElement(int $index) : ?ConfigSource;

	/**
	 * Returns an iterator of `ConfigSource` objects if this is a list,
	 * `null` otherwise.
	 *
	 * This should return an empty iterator if this is a list representing an empty array.
	 *
	 * The order of elements must be stable and consistent with `listElement`.
	 *
	 * The behaviour is undefined if the list is mutated during iteration.
	 *
	 * @return Iterator<ConfigSource>|null
	 */
	public function listElements() : ?Iterator;
}
