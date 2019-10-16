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

final class InteractionType{

	private function __construct(){
		//NOOP
	}

	public const TYPE_BLOCK_USE = 0; // Used in BellBlock/BlastFurnace
	public const TYPE_BREWING_STAND_BLOCK_USE = 1;
	public const TYPE_CARTOGRAPHY_TABLE_BLOCK_USE = 2;
	public const TYPE_GRINDSTONE_BLOCK_USE = 3;
	public const TYPE_LOOM_BLOCK_USE = 4;
	public const TYPE_SMOKER_BLOCK_USE = 5;
	public const TYPE_STONECUTTER_BLOCK_USE = 6;
	public const TYPE_BARREL_BLOCK_USE = 7;

	public const TYPE_CAMPFIRE_COOK_FOOD = 9;
	public const TYPE_CAMPFIRE_DOUSE_FIRE = 10; // Use block with Shovel
	public const TYPE_CAMPFIRE_LIGHT_FIRE = 11; // Use block with FireCharge

	public const TYPE_CAULDRON_FILL_LIQUID = 13; // From GlassBattle/Bucket
	public const TYPE_CAULDRON_TAKE_LIQUID = 14; // Lava/Water
	public const TYPE_CAULDRON_ADD_DYE = 15;
	public const TYPE_CAULDRON_DYE_ARMOR = 16;
	public const TYPE_CAULDRON_CLEAN_BANNER_OR_ARMOR = 17;
	public const TYPE_CAULDRON_POTION_ARROW = 18;
	public const TYPE_COMPOSTER_ADD_ITEM = 19;
	public const TYPE_COMPOSTER_EMIT_BONE_MEAL = 20; // ???
	public const TYPE_LECTERN_ADD_BOOK = 21;
	public const TYPE_LECTERN_USE = 22;

}
