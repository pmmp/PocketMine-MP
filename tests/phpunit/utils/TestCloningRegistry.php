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
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see RegistryTrait::_generateMethodAnnotations()
 *
 * @method static \stdClass TEST1()
 * @method static \stdClass TEST2()
 * @method static \stdClass TEST3()
 */
final class TestCloningRegistry{
	use CloningRegistryTrait;

	/**
	 * @return \stdClass[]
	 */
	public static function getAll() : array{
		/** @var \stdClass[] $result */
		$result = self::_registryGetAll();
		return $result;
	}

	public static function fromString(string $s) : \stdClass{
		/** @var \stdClass $result */
		$result = self::_registryFromString($s);
		return $result;
	}

	protected static function setup() : void{
		self::_registryRegister("test1", new \stdClass());
		self::_registryRegister("test2", new \stdClass());
		self::_registryRegister("test3", new \stdClass());
	}
}
