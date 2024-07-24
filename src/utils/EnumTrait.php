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

/**
 * This trait allows a class to simulate a Java-style enum. Members are exposed as static methods and handled via
 * __callStatic().
 *
 * Classes using this trait need to include \@method tags in their class docblock for every enum member.
 * Alternatively, just put \@generate-registry-docblock in the docblock and run build/generate-registry-annotations.php
 *
 * @deprecated Use native PHP 8.1 enums instead. Use {@link LegacyEnumShimTrait} if you need to provide backwards
 * compatible EnumTrait-like API for migrated enums.
 */
trait EnumTrait{
	use RegistryTrait;
	use NotCloneable;
	use NotSerializable;

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
	 * @phpstan-return array<string, self>
	 */
	public static function getAll() : array{
		//phpstan doesn't support generic traits yet :(
		/** @var self[] $result */
		$result = self::_registryGetAll();
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
		self::verifyName($enumName);
		$this->enumName = $enumName;
		if(self::$nextId === null){
			self::$nextId = Process::pid(); //this provides enough base entropy to prevent hardcoding
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
