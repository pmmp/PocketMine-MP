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

use function array_map;
use function count;
use function mb_strtoupper;

trait RegistryTrait{
	/** @var object[] */
	private static $members = null;

	/**
	 * Adds the given object to the registry.
	 *
	 * @throws \InvalidArgumentException
	 */
	private static function _registryRegister(string $name, object $member) : void{
		$name = mb_strtoupper($name);
		if(isset(self::$members[$name])){
			throw new \InvalidArgumentException("\"$name\" is already reserved");
		}
		self::$members[mb_strtoupper($name)] = $member;
	}

	/**
	 * Inserts default entries into the registry.
	 *
	 * (This ought to be private, but traits suck too much for that.)
	 */
	abstract protected static function setup() : void;

	/**
	 * @internal Lazy-inits the enum if necessary.
	 *
	 * @throws \InvalidArgumentException
	 */
	protected static function checkInit() : void{
		if(self::$members === null){
			self::$members = [];
			self::setup();
		}
	}

	/**
	 * @throws \InvalidArgumentException
	 */
	private static function _registryFromString(string $name) : object{
		self::checkInit();
		$name = mb_strtoupper($name);
		if(!isset(self::$members[$name])){
			throw new \InvalidArgumentException("No such registry member: " . self::class . "::" . $name);
		}
		return self::preprocessMember(self::$members[$name]);
	}

	protected static function preprocessMember(object $member) : object{
		return $member;
	}

	/**
	 * @param string  $name
	 * @param mixed[] $arguments
	 * @phpstan-param list<mixed> $arguments
	 *
	 * @return object
	 */
	public static function __callStatic($name, $arguments){
		if(count($arguments) > 0){
			throw new \ArgumentCountError("Expected exactly 0 arguments, " . count($arguments) . " passed");
		}
		try{
			return self::_registryFromString($name);
		}catch(\InvalidArgumentException $e){
			throw new \Error($e->getMessage(), 0, $e);
		}
	}

	/**
	 * @return object[]
	 */
	private static function _registryGetAll() : array{
		self::checkInit();
		return array_map(function(object $o) : object{
			return self::preprocessMember($o);
		}, self::$members);
	}
}
