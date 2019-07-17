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

namespace pocketmine\block\utils;

use pocketmine\utils\EnumTrait;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever enum members are added, removed or changed.
 * @see EnumTrait::_generateMethodAnnotations()
 *
 * @method static self SKELETON()
 * @method static self WITHER_SKELETON()
 * @method static self ZOMBIE()
 * @method static self PLAYER()
 * @method static self CREEPER()
 * @method static self DRAGON()
 */
final class SkullType{
	use EnumTrait {
		register as Enum_register;
		__construct as Enum___construct;
	}

	/** @var SkullType[] */
	private static $numericIdMap = [];

	protected static function setup() : iterable{
		return [
			new SkullType("skeleton", "Skeleton Skull", 0),
			new SkullType("wither_skeleton", "Wither Skeleton Skull", 1),
			new SkullType("zombie", "Zombie Head", 2),
			new SkullType("player", "Player Head", 3),
			new SkullType("creeper", "Creeper Head", 4),
			new SkullType("dragon", "Dragon Head", 5)
		];
	}

	protected static function register(SkullType $type) : void{
		self::Enum_register($type);
		self::$numericIdMap[$type->getMagicNumber()] = $type;
	}

	/**
	 * @internal
	 * @param int $magicNumber
	 *
	 * @return SkullType
	 * @throws \InvalidArgumentException
	 */
	public static function fromMagicNumber(int $magicNumber) : SkullType{
		if(!isset(self::$numericIdMap[$magicNumber])){
			throw new \InvalidArgumentException("Unknown skull type magic number $magicNumber");
		}
		return self::$numericIdMap[$magicNumber];
	}

	/** @var string */
	private $displayName;
	/** @var int */
	private $magicNumber;

	private function __construct(string $enumName, string $displayName, int $magicNumber){
		$this->Enum___construct($enumName);
		$this->displayName = $displayName;
		$this->magicNumber = $magicNumber;
	}

	/**
	 * @return string
	 */
	public function getDisplayName() : string{
		return $this->displayName;
	}

	/**
	 * @return int
	 */
	public function getMagicNumber() : int{
		return $this->magicNumber;
	}
}
