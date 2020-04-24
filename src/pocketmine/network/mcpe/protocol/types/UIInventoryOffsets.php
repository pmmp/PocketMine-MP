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

namespace pocketmine\network\mcpe\protocol\types;

interface UIInventoryOffsets{

	public const OFFSET_CURSOR = 0; // 1 slots, 0 - 0
	public const OFFSET_ANVIL = 1; // 2 slots, 1 - 2
	public const OFFSET_STONECUTTER = 3; // 1 slots, 3 - 3
	public const OFFSET_V2_TRADE = 4; // 2 slots, 4 - 5
	public const OFFSET_TRADE = 6; // 2 slots, 6 - 7
	public const OFFSET_MATERIAL_REDUCER_INPUT = 8; // 1 slots, 8 - 8
	public const OFFSET_LOOM = 9; // 3 slots, 9 - 11
	public const OFFSET_CARTOGRAPHY_TABLE = 12; // 2 slots, 12 - 13
	public const OFFSET_ENCHANT = 14; // 2 slots, 14 - 15
	public const OFFSET_GRINDSTONE = 16; // 2 slots, 16 - 17
	public const OFFSET_COMPOUND_CREATER = 18; // 9 slots, 18 - 26
	public const OFFSET_BEACON = 27; // 1 slot, 27 - 27
	public const OFFSET_CRAFTING_SMALL = 28; // 4 slots, 28 - 31
	public const OFFSET_CRAFTING_BIG = 32; // 9 slots, 32 - 40
	public const OFFSET_MATERIAL_REDUCER_OUTPUT = 41; // 9 slots, 41 - 49
	public const OFFSET_CREATED_ITEM_OUTPUT = 50; // 1 slots, 50 -50

}
