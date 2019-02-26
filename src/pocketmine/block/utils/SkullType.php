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

final class SkullType{

	/** @var SkullType */
	private static $SKELETON;
	/** @var SkullType */
	private static $WITHER_SKELETON;
	/** @var SkullType */
	private static $ZOMBIE;
	/** @var SkullType */
	private static $HUMAN;
	/** @var SkullType */
	private static $CREEPER;
	/** @var SkullType */
	private static $DRAGON;

	/* auto-generated code */

	public static function SKELETON() : SkullType{
		return self::$SKELETON;
	}

	public static function WITHER_SKELETON() : SkullType{
		return self::$WITHER_SKELETON;
	}

	public static function ZOMBIE() : SkullType{
		return self::$ZOMBIE;
	}

	public static function HUMAN() : SkullType{
		return self::$HUMAN;
	}

	public static function CREEPER() : SkullType{
		return self::$CREEPER;
	}

	public static function DRAGON() : SkullType{
		return self::$DRAGON;
	}

	/** @var SkullType[] */
	private static $all = [];
	/** @var SkullType[] */
	private static $numericIdMap = [];

	public static function _init() : void{
		self::register(self::$SKELETON = new SkullType("Skeleton Skull", 0));
		self::register(self::$WITHER_SKELETON = new SkullType("Wither Skeleton Skull", 1));
		self::register(self::$ZOMBIE = new SkullType("Zombie Head", 2));
		self::register(self::$HUMAN = new SkullType("Player Head", 3));
		self::register(self::$CREEPER = new SkullType("Creeper Head", 4));
		self::register(self::$DRAGON = new SkullType("Dragon Head", 5));
	}

	private static function register(SkullType $type) : void{
		self::$numericIdMap[$type->getMagicNumber()] = $type;
		self::$all[] = $type;
	}

	/**
	 * @return SkullType[]
	 */
	public static function getAll() : array{
		return self::$all;
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

	public function __construct(string $displayName, int $magicNumber){
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
SkullType::_init();
