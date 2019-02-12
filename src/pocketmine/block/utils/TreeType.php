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

final class TreeType{

	/** @var TreeType */
	public static $OAK;
	/** @var TreeType */
	public static $SPRUCE;
	/** @var TreeType */
	public static $BIRCH;
	/** @var TreeType */
	public static $JUNGLE;
	/** @var TreeType */
	public static $ACACIA;
	/** @var TreeType */
	public static $DARK_OAK;

	/** @var TreeType[] */
	private static $numericIdMap = [];
	/** @var TreeType[] */
	private static $all = [];

	/**
	 * @internal
	 */
	public static function _init() : void{
		self::register(self::$OAK = new TreeType("Oak", 0));
		self::register(self::$SPRUCE = new TreeType("Spruce", 1));
		self::register(self::$BIRCH = new TreeType("Birch", 2));
		self::register(self::$JUNGLE = new TreeType("Jungle", 3));
		self::register(self::$ACACIA = new TreeType("Acacia", 4));
		self::register(self::$DARK_OAK = new TreeType("Dark Oak", 5));
	}

	private static function register(TreeType $type) : void{
		self::$numericIdMap[$type->getMagicNumber()] = $type;
		self::$all[] = $type;
	}

	/**
	 * @return TreeType[]
	 */
	public static function getAll() : array{
		return self::$all;
	}

	/**
	 * @internal
	 * @param int $magicNumber
	 *
	 * @return TreeType
	 * @throws \InvalidArgumentException
	 */
	public static function fromMagicNumber(int $magicNumber) : TreeType{
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
	 * @param string $displayName
	 * @param int    $magicNumber
	 */
	private function __construct(string $displayName, int $magicNumber){
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
TreeType::_init();
