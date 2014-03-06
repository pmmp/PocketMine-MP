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
		Item\COAL => 80,
		Item\COAL_BLOCK => 800,
		Item\TRUNK => 15,
		Item\WOODEN_PLANKS => 15,
		Item\SAPLING => 5,
		Item\WOODEN_AXE => 10,
		Item\WOODEN_PICKAXE => 10,
		Item\WOODEN_SWORD => 10,
		Item\WOODEN_SHOVEL => 10,
		Item\WOODEN_HOE => 10,
		Item\STICK => 5,
		Item\FENCE => 15,
		Item\FENCE_GATE => 15,
		Item\WOODEN_STAIRS => 15,
		Item\SPRUCE_WOOD_STAIRS => 15,
		Item\BIRCH_WOOD_STAIRS => 15,
		Item\JUNGLE_WOOD_STAIRS => 15,
		Item\TRAPDOOR => 15,
		Item\WORKBENCH => 15,
		Item\BOOKSHELF => 15,
		Item\CHEST => 15,
		Item\BUCKET => 1000,

	);

}