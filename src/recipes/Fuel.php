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

namespace PocketMine\Recipes;

use PocketMine;

abstract class Fuel{
	public static $duration = array(
		Item\Item::COAL => 80,
		Item\Item::COAL_BLOCK => 800,
		Item\Item::TRUNK => 15,
		Item\Item::WOODEN_PLANKS => 15,
		Item\Item::SAPLING => 5,
		Item\Item::WOODEN_AXE => 10,
		Item\Item::WOODEN_PICKAXE => 10,
		Item\Item::WOODEN_SWORD => 10,
		Item\Item::WOODEN_SHOVEL => 10,
		Item\Item::WOODEN_HOE => 10,
		Item\Item::STICK => 5,
		Item\Item::FENCE => 15,
		Item\Item::FENCE_GATE => 15,
		Item\Item::WOODEN_STAIRS => 15,
		Item\Item::SPRUCE_WOOD_STAIRS => 15,
		Item\Item::BIRCH_WOOD_STAIRS => 15,
		Item\Item::JUNGLE_WOOD_STAIRS => 15,
		Item\Item::TRAPDOOR => 15,
		Item\Item::WORKBENCH => 15,
		Item\Item::BOOKSHELF => 15,
		Item\Item::CHEST => 15,
		Item\Item::BUCKET => 1000,

	);

}