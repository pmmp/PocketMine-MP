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

use function getmypid;
use function preg_match;

trait EnumTrait{
	use RegistryTrait;

	/**
	 * Registers the given object as an enum member.
	 *
	 * @throws \InvalidArgumentException
	 */
	protected static function register(self $member) : void{
		self::_registryRegister($member->name(), $member);
	}

	protected static function registerAll(self ...$members) : void{
		foreach($members as $member){
			self::register($member);
		}
	}

	/**
	 * Returns all members of the enum.
	 * This is overridden to change the return typehint.
	 *
	 * @return self[]
	 */
	public static function getAll() : array{
		//phpstan doesn't support generic traits yet :(
		/** @var self[] $result */
		$result = self::_registryGetAll();
		return $result;
	}

	/**
	 * Returns the enum member matching the given name.
	 * This is overridden to change the return typehint.
	 *
	 * @throws \InvalidArgumentException if no member matches.
	 */
	public static function fromString(string $name) : self{
		//phpstan doesn't support generic traits yet :(
		/** @var self $result */
		$result = self::_registryFromString($name);
		return $result;
	}

	/** @var int|null */
	private static $nextId = null;

	/** @var string */
	private $enumName;
	/** @var int */
	private $runtimeId;

	/**
	 * @throws \InvalidArgumentException
	 */
	private function __construct(string $enumName){
		if(preg_match('/^\D[A-Za-z\d_]+$/u', $enumName, $matches) === 0){
			throw new \InvalidArgumentException("Invalid enum member name \"$enumName\", should only contain letters, numbers and underscores, and must not start with a number");
		}
		$this->enumName = $enumName;
		if(self::$nextId === null){
			self::$nextId = getmypid(); //this provides enough base entropy to prevent hardcoding
		}
		$this->runtimeId = self::$nextId++;
	}

	public function name() : string{
		return $this->enumName;
	}

	/**
	 * Returns a runtime-only identifier for this enum member. This will be different with each run, so don't try to
	 * hardcode it.
	 * This can be useful for switches or array indexing.
	 */
	public function id() : int{
		return $this->runtimeId;
	}

	/**
	 * Returns whether the two objects are equivalent.
	 */
	public function equals(self $other) : bool{
		return $this->enumName === $other->enumName;
	}
}
