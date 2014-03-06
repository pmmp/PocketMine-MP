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

class Smelt{
	public static $product = array(
		Item\Item::COBBLESTONE => array(Item\Item::STONE, 0),
		Item\Item::SAND => array(Item\Item::GLASS, 0),
		Item\Item::TRUNK => array(Item\Item::COAL, 1), //Charcoal
		Item\Item::GOLD_ORE => array(Item\Item::GOLD_INGOT, 0),
		Item\Item::IRON_ORE => array(Item\Item::IRON_INGOT, 0),
		Item\Item::NETHERRACK => array(Item\Item::NETHER_BRICK, 0),
		Item\Item::RAW_PORKCHOP => array(Item\Item::COOKED_PORKCHOP, 0),
		Item\Item::CLAY => array(Item\Item::BRICK, 0),
		//Item\Item::RAW_FISH => array(Item\Item::COOKED_FISH, 0),
		Item\Item::CACTUS => array(Item\Item::DYE, 2),
		Item\Item::RED_MUSHROOM => array(Item\Item::DYE, 1),
		Item\Item::RAW_BEEF => array(Item\Item::STEAK, 0),
		Item\Item::RAW_CHICKEN => array(Item\Item::COOKED_CHICKEN, 0),
		Item\Item::RED_MUSHROOM => array(Item\Item::DYE, 1),
		Item\Item::POTATO => array(Item\Item::BAKED_POTATO, 0),
	);
}