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
	 * @param self $member
	 *
	 * @throws \InvalidArgumentException
	 */
	protected static function register(self $member) : void{
		self::_registryRegister($member->name(), $member);
	}

	/**
	 * Returns an array of enum members to be registered.
	 *
	 * (This ought to be private, but traits suck too much for that.)
	 *
	 * @return self[]|iterable
	 */
	abstract protected static function setup() : iterable;

	/**
	 * @internal Lazy-inits the enum if necessary.
	 *
	 * @throws \InvalidArgumentException
	 */
	protected static function checkInit() : void{
		if(self::$members === null){
			self::$members = [];
			foreach(self::setup() as $item){
				self::register($item);
			}
		}
	}

	/**
	 * Returns all members of the enum.
	 * This is overridden to change the return typehint.
	 *
	 * @return self[]
	 */
	public static function getAll() : array{
		return self::_registryGetAll();
	}

	/**
	 * Returns the enum member matching the given name.
	 * This is overridden to change the return typehint.
	 *
	 * @param string $name
	 *
	 * @return self
	 * @throws \InvalidArgumentException if no member matches.
	 */
	public static function fromString(string $name) : self{
		return self::_registryFromString($name);
	}

	/** @var int|null */
	private static $nextId = null;

	/** @var string */
	private $enumName;
	/** @var int */
	private $runtimeId;

	/**
	 * @param string $enumName
	 * @throws \InvalidArgumentException
	 */
	private function __construct(string $enumName){
		static $pattern = '/^\D[A-Za-z\d_]+$/u';
		if(preg_match($pattern, $enumName, $matches) === 0){
			throw new \InvalidArgumentException("Invalid enum member name \"$enumName\", should only contain letters, numbers and underscores, and must not start with a number");
		}
		$this->enumName = $enumName;
		if(self::$nextId === null){
			self::$nextId = getmypid(); //this provides enough base entropy to prevent hardcoding
		}
		$this->runtimeId = self::$nextId++;
	}

	/**
	 * @return string
	 */
	public function name() : string{
		return $this->enumName;
	}

	/**
	 * Returns a runtime-only identifier for this enum member. This will be different with each run, so don't try to
	 * hardcode it.
	 * This can be useful for switches or array indexing.
	 *
	 * @return int
	 */
	public function id() : int{
		return $this->runtimeId;
	}

	/**
	 * Returns whether the two objects are equivalent.
	 *
	 * @param self $other
	 *
	 * @return bool
	 */
	public function equals(self $other) : bool{
		return $this->enumName === $other->enumName;
	}
}
