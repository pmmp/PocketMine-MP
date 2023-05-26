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
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see build/generate-registry-annotations.php
 * @generate-registry-docblock
 *
 * @method static MobHeadType CREEPER()
 * @method static MobHeadType DRAGON()
 * @method static MobHeadType PLAYER()
 * @method static MobHeadType SKELETON()
 * @method static MobHeadType WITHER_SKELETON()
 * @method static MobHeadType ZOMBIE()
 */
final class MobHeadType{
	use EnumTrait {
		register as Enum_register;
		__construct as Enum___construct;
	}

	/** @var MobHeadType[] */
	private static array $numericIdMap = [];

	protected static function setup() : void{
		self::registerAll(
			new MobHeadType("skeleton", "Skeleton Skull", 0),
			new MobHeadType("wither_skeleton", "Wither Skeleton Skull", 1),
			new MobHeadType("zombie", "Zombie Head", 2),
			new MobHeadType("player", "Player Head", 3),
			new MobHeadType("creeper", "Creeper Head", 4),
			new MobHeadType("dragon", "Dragon Head", 5)
		);
	}

	protected static function register(MobHeadType $type) : void{
		self::Enum_register($type);
		self::$numericIdMap[$type->getMagicNumber()] = $type;
	}

	/**
	 * @internal
	 *
	 * @throws \InvalidArgumentException
	 */
	public static function fromMagicNumber(int $magicNumber) : MobHeadType{
		if(!isset(self::$numericIdMap[$magicNumber])){
			throw new \InvalidArgumentException("Unknown skull type magic number $magicNumber");
		}
		return self::$numericIdMap[$magicNumber];
	}

	private function __construct(
		string $enumName,
		private string $displayName,
		private int $magicNumber
	){
		$this->Enum___construct($enumName);
	}

	public function getDisplayName() : string{
		return $this->displayName;
	}

	public function getMagicNumber() : int{
		return $this->magicNumber;
	}
}
