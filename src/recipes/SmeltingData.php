<?php

/**
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


class SmeltingData{
	public static $product = array(
		COBBLESTONE => array(STONE, 0),
		SAND => array(GLASS, 0),
		TRUNK => array(COAL, 1), //Charcoal
		GOLD_ORE => array(GOLD_INGOT, 0),
		IRON_ORE => array(IRON_INGOT, 0),
		NETHERRACK => array(NETHER_BRICK, 0),
		RAW_PORKCHOP => array(COOKED_PORKCHOP, 0),
		CLAY => array(BRICK, 0),
		//RAW_FISH => array(COOKED_FISH, 0),
		CACTUS => array(DYE, 2),
		RED_MUSHROOM => array(DYE, 1),
		RAW_BEEF => array(STEAK, 0),
		RAW_CHICKEN => array(COOKED_CHICKEN, 0),
		RED_MUSHROOM => array(DYE, 1),
		POTATO => array(BAKED_POTATO, 0),
	);

}