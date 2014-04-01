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

namespace pocketmine\recipes;

use pocketmine\item\Item;

class Smelt{
	public static $product = array(
		Item::COBBLESTONE => array(Item::STONE, 0),
		Item::SAND => array(Item::GLASS, 0),
		Item::TRUNK => array(Item::COAL, 1), //Charcoal
		Item::GOLD_ORE => array(Item::GOLD_INGOT, 0),
		Item::IRON_ORE => array(Item::IRON_INGOT, 0),
		Item::NETHERRACK => array(Item::NETHER_BRICK, 0),
		Item::RAW_PORKCHOP => array(Item::COOKED_PORKCHOP, 0),
		Item::CLAY => array(Item::BRICK, 0),
		//Item::RAW_FISH => array(Item::COOKED_FISH, 0),
		Item::CACTUS => array(Item::DYE, 2),
		Item::RED_MUSHROOM => array(Item::DYE, 1),
		Item::RAW_BEEF => array(Item::STEAK, 0),
		Item::RAW_CHICKEN => array(Item::COOKED_CHICKEN, 0),
		Item::RED_MUSHROOM => array(Item::DYE, 1),
		Item::POTATO => array(Item::BAKED_POTATO, 0),
	);
}