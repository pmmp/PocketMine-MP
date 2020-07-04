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

namespace pocketmine\network\mcpe\protocol\types\inventory;

final class UIInventorySlotOffset{

	private function __construct(){
		//NOOP
	}

	public const CURSOR = 0;
	public const ANVIL = [
		1 => 0,
		2 => 1,
	];
	public const STONE_CUTTER_INPUT = 3;
	public const TRADE2_INGREDIENT = [
		4 => 0,
		5 => 1,
	];
	public const TRADE_INGREDIENT = [
		6 => 0,
		7 => 1,
	];
	public const MATERIAL_REDUCER_INPUT = 8;
	public const LOOM = [
		9 => 0,
		10 => 1,
		11 => 2,
	];
	public const CARTOGRAPHY_TABLE = [
		12 => 0,
		13 => 1,
	];
	public const ENCHANTING_TABLE = [
		14 => 0,
		15 => 1,
	];
	public const GRINDSTONE = [
		16 => 0,
		17 => 1,
	];
	public const COMPOUND_CREATOR_INPUT = [
		18 => 0,
		19 => 1,
		20 => 2,
		21 => 3,
		22 => 4,
		23 => 5,
		24 => 6,
		25 => 7,
		26 => 8,
	];
	public const BEACON_PAYMENT = 27;
	public const CRAFTING2X2_INPUT = [
		28 => 0,
		29 => 1,
		30 => 2,
		31 => 3,
	];
	public const CRAFTING3X3_INPUT = [
		32 => 0,
		33 => 1,
		34 => 2,
		35 => 3,
		36 => 4,
		37 => 5,
		38 => 6,
		39 => 7,
		40 => 8,
	];
	public const MATERIAL_REDUCER_OUTPUT = [
		41 => 0,
		42 => 1,
		43 => 2,
		44 => 3,
		45 => 4,
		46 => 5,
		47 => 6,
		48 => 7,
		49 => 8,
	];
	public const CREATED_ITEM_OUTPUT = 50;
}
