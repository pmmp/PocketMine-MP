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


define("PMF_LEVEL_DEFLATE_LEVEL", 6);

//Gamemodes
define("SURVIVAL", 0);
define("CREATIVE", 1);
define("ADVENTURE", 2);
define("VIEW", 3);
define("VIEWER", 3);


//Players
define("PLAYER_MAX_QUEUE", 1024);

define("PLAYER_SURVIVAL_SLOTS", 36);
define("PLAYER_CREATIVE_SLOTS", 112);


//Block Updates
define("BLOCK_UPDATE_NORMAL", 1);
define("BLOCK_UPDATE_RANDOM", 2);
define("BLOCK_UPDATE_SCHEDULED", 3);
define("BLOCK_UPDATE_WEAK", 4);
define("BLOCK_UPDATE_TOUCH", 5);


//Entities
define("ENTITY_PLAYER", 1);

define("ENTITY_MOB", 2);
	define("MOB_CHICKEN", 10);
	define("MOB_COW", 11);
	define("MOB_PIG", 12);
	define("MOB_SHEEP", 13);

	define("MOB_ZOMBIE", 32);
	define("MOB_CREEPER", 33);
	define("MOB_SKELETON", 34);
	define("MOB_SPIDER", 35);
	define("MOB_PIGMAN", 36);

define("ENTITY_OBJECT", 3);
	define("OBJECT_PRIMEDTNT", 65);
	define("OBJECT_ARROW", 80);
	define("OBJECT_PAINTING", 83);

define("ENTITY_ITEM", 4);

define("ENTITY_FALLING", 5);
	define("FALLING_SAND", 66);


//TileEntities
define("TILE_SIGN", "Sign");
define("TILE_CHEST", "Chest");
	define("CHEST_SLOTS", 27);
define("TILE_FURNACE", "Furnace");
	define("FURNACE_SLOTS", 3);