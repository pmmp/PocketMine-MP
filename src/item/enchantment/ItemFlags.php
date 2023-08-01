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

namespace pocketmine\item\enchantment;

final class ItemFlags{

	private function __construct(){
		//NOOP
	}

	public const NONE = 0x0;
	public const ALL = 0xffffff;
	public const BREAKABLE = self::ALL & ~self::COMPASS & ~self::RECOVERY_COMPASS & ~self::HEAD;
	public const ARMOR = self::HELMET | self::CHESTPLATE | self::LEGGINGS | self::BOOTS;
	public const HELMET = 0x1;
	public const CHESTPLATE = 0x2;
	public const LEGGINGS = 0x4;
	public const BOOTS = 0x8;
	public const SWORD = 0x10;
	public const TRIDENT = 0x20;
	public const BOW = 0x40;
	public const CROSSBOW = 0x80;
	public const SHIELD = 0x100;
	public const SHEARS = 0x200;
	public const FLINT_AND_STEEL = 0x400;
	public const DIG = self::AXE | self::PICKAXE | self::SHOVEL | self::HOE;
	public const AXE = 0x800;
	public const PICKAXE = 0x1000;
	public const SHOVEL = 0x2000;
	public const HOE = 0x4000;
	public const FISHING_ROD = 0x8000;
	public const SMTH_ON_STICK = 0x10000;
	public const ELYTRA = 0x20000;
	public const BRUSH = 0x40000;
	public const COMPASS = 0x80000;
	public const RECOVERY_COMPASS = 0x100000;
	public const HEAD = 0x200000;
}
