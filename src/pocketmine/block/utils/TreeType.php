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
 * @method static self OAK()
 * @method static self SPRUCE()
 * @method static self BIRCH()
 * @method static self JUNGLE()
 * @method static self ACACIA()
 * @method static self DARK_OAK()
 */
final class TreeType{
	use EnumTrait {
		register as Enum_register;
		__construct as Enum___construct;
	}

	/** @var TreeType[] */
	private static $numericIdMap = [];

	protected static function setup() : iterable{
		return [
			new TreeType("oak", "Oak", 0),
			new TreeType("spruce", "Spruce", 1),
			new TreeType("birch", "Birch", 2),
			new TreeType("jungle", "Jungle", 3),
			new TreeType("acacia", "Acacia", 4),
			new TreeType("dark_oak", "Dark Oak", 5)
		];
	}

	protected static function register(TreeType $type) : void{
		self::Enum_register($type);
		self::$numericIdMap[$type->getMagicNumber()] = $type;
	}

	/**
	 * @internal
	 *
	 * @param int $magicNumber
	 *
	 * @return TreeType
	 * @throws \InvalidArgumentException
	 */
	public static function fromMagicNumber(int $magicNumber) : TreeType{
		self::checkInit();
		if(!isset(self::$numericIdMap[$magicNumber])){
			throw new \InvalidArgumentException("Unknown tree type magic number $magicNumber");
		}
		return self::$numericIdMap[$magicNumber];
	}

	/** @var string */
	private $displayName;
	/** @var int */
	private $magicNumber;

	/**
	 * @param string $enumName
	 * @param string $displayName
	 * @param int    $magicNumber
	 */
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
