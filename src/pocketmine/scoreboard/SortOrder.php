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

namespace pocketmine\scoreboard;

use pocketmine\utils\EnumTrait;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever enum members are added, removed or changed.
 * @see EnumTrait::_generateMethodAnnotations()
 *
 * @method static self ASCENDING()
 * @method static self DESCENDING()
 */
final class SortOrder{
	use EnumTrait {
		register as Enum_register;
		__construct as Enum___construct;
	}

	/** @var self[] */
	private static $numericIdMap = [];

	protected static function setup() : iterable{
		return [
			new self("ascending", 0),
			new self("descending", 1)
		];
	}

	protected static function register(self $member) : void{
		self::Enum_register($member);
		self::$numericIdMap[$member->getMagicNumber()] = $member;
	}

	/**
	 * @internal
	 *
	 * @param int $magicNumber
	 *
	 * @return self
	 * @throws \InvalidArgumentException
	 */
	public static function fromMagicNumber(int $magicNumber) : self{
		self::checkInit();
		if(!isset(self::$numericIdMap[$magicNumber])){
			throw new \InvalidArgumentException("Unknown sort order magic number $magicNumber");
		}
		return self::$numericIdMap[$magicNumber];
	}

	/** @var int */
	private $magicNumber;

	/**
	 * @param string $name
	 * @param int    $magicNumber
	 */
	private function __construct(string $name, int $magicNumber){
		$this->Enum___construct($name);
		$this->magicNumber = $magicNumber;
	}

	/**
	 * @return int
	 */
	public function getMagicNumber() : int{
		return $this->magicNumber;
	}
}
