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


interface MutableConfigSource extends ConfigSource{
	/**
	 * Sets this node to a string.
	 *
	 * @return bool returns false if type is unassignable
	 */
	public function setString(string $value) : bool;

	/**
	 * Sets this node to a int.
	 *
	 * @return bool returns false if type is unassignable
	 */
	public function setInt(int $value) : bool;

	/**
	 * Sets this node to a float.
	 *
	 * @return bool returns false if type is unassignable
	 */
	public function setFloat(float $value) : bool;

	/**
	 * Sets this node to a boolean.
	 *
	 * @return bool returns false if type is unassignable
	 */
	public function setBool(bool $value) : bool;

	/**
	 * Allocates a new entry under `$key` if this is a mapping,
	 * or `null` if this is not a mapping or `$key` already exists.
	 *
	 * Writes to existing map entries should use `mapEntry` instead.
	 *
	 * The behaviour is undefined if a non-null returned value is not assigned into,
	 * or if it is read before assigned.
	 *
	 * Implementations may provide insert `$comment` as a comment if possible.
	 * Whether the comment is added does not affect the return value.
	 *
	 * @see ConfigSource::mapEntry()
	 */
	public function addMapEntry(string $key, ?string $comment = null) : ?MutableConfigSource;

	/**
	 * Sets the comment of a map entry.
	 *
	 * The behaviour is undefined if this is not a mapping or `$key` does not exist.
	 */
	public function setMapEntryComment(string $key, string $comment) : void;

	/**
	 * Removes the entry under `$key` if this is a mapping.
	 *
	 * Returns whether this entry existed,
	 * or `null` if this is not a mapping.
	 */
	public function removeMapEntry(string $key) : ?bool;

	/**
	 * Adds a value if this is a list.
	 *
	 * Returns a writer to the added entry if this is a list,
	 * `null` otherwise.
	 *
	 * The behaviour is undefined if a non-null returned value is not assigned into,
	 * or if it is read before assigned.
	 */
	public function addListElement() : ?MutableConfigSource;

	/**
	 * Removes the entry at index `$index` if this is a list.
	 *
	 * Returns whether this entry existed,
	 * or `null` if this is not a list.
	 */
	public function removeListElement(int $index) : ?bool;

	/**
	 * Flushes the previous writes to this config source.
	 */
	public function flush() : void;
}
