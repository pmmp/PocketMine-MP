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

	//TODO: this should probably move to protocol

	public const NONE = 0x0;
	public const ALL = 0xffff;
	public const ARMOR = self::HEAD | self::TORSO | self::LEGS | self::FEET;
	public const HEAD = 0x1;
	public const TORSO = 0x2;
	public const LEGS = 0x4;
	public const FEET = 0x8;
	public const SWORD = 0x10;
	public const BOW = 0x20;
	public const TOOL = self::HOE | self::SHEARS | self::FLINT_AND_STEEL;
	public const HOE = 0x40;
	public const SHEARS = 0x80;
	public const FLINT_AND_STEEL = 0x100;
	public const DIG = self::AXE | self::PICKAXE | self::SHOVEL;
	public const AXE = 0x200;
	public const PICKAXE = 0x400;
	public const SHOVEL = 0x800;
	public const FISHING_ROD = 0x1000;
	public const CARROT_STICK = 0x2000;
	public const ELYTRA = 0x4000;
	public const TRIDENT = 0x8000;
}
