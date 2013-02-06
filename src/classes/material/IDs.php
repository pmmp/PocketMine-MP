<?php

/*

           -
         /   \
      /         \
   /   PocketMine  \
/          MP         \
|\     @shoghicp     /|
|.   \           /   .|
| ..     \   /     .. |
|    ..    |    ..    |
|       .. | ..       |
\          |          /
   \       |       /
      \    |    /
         \ | /

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Lesser General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.


*/

define("AIR", 0);
define("STONE", 1);
define("GRASS", 2);
define("DIRT", 3);
define("COBBLESTONE", 4);
define("COBBLE", 4);
define("PLANK", 5);
define("PLANKS", 5);
define("WOODEN_PLANK", 5);
define("SAPLING", 6);
define("SAPLINGS", 6);
define("BEDROCK", 7);
define("WATER", 8);
define("STILL_WATER", 9);
define("LAVA", 10);
define("STILL_LAVA", 11);
define("SAND", 12);
define("GRAVEL", 13);
define("GOLD_ORE", 14);
define("IRON_ORE", 15);
define("COAL_ORE", 16);
define("WOOD", 17);
define("TRUNK", 17);
define("LEAVES", 18);
define("LEAVE", 18);

define("GLASS", 20);
define("LAPIS_ORE", 21);
define("LAPIS_BLOCK", 22);

define("SANDSTONE", 24);

define("BED_BLOCK", 26);


define("COBWEB", 30);
define("TALL_GRASS", 31);
define("BUSH", 32);
define("DEAD_BUSH", 32);
define("WOOL", 35);
define("DANDELION", 37);
define("ROSE", 38);
define("CYAN_FLOWER", 38);
define("BROWN_MUSHROOM", 39);
define("RED_MUSHROOM", 40);
define("GOLD_BLOCK", 41);
define("IRON_BLOCK", 42);
define("DOUBLE_SLAB", 43);
define("DOUBLE_SLABS", 43);
define("SLAB", 44);
define("SLABS", 44);
define("BRICKS", 45);
define("BRICKS_BLOCK", 45);
define("TNT", 46);
define("BOOKSHELF", 47);
define("MOSS_STONE", 48);
define("MOSSY_STONE", 48);
define("OBSIDIAN", 49);
define("TORCH", 50);
define("FIRE", 51);

define("WOOD_STAIRS", 53);
define("WOODEN_STAIRS", 53);
define("CHEST", 54);

define("DIAMOND_ORE", 56);
define("DIAMOND_BLOCK", 57);
define("CRAFTING_TABLE", 58);
define("WORKBENCH", 58);
define("WHEAT_BLOCK", 59);
define("FARMLAND", 60);
define("FURNACE", 61);
define("BURNING_FURNACE", 62);
define("LIT_FURNACE", 62);
define("SIGN_POST", 63);
define("DOOR_BLOCK", 64);
define("WOODEN_DOOR_BLOCK", 64);
define("WOOD_DOOR_BLOCK", 64);
define("LADDER", 65);

define("COBBLE_STAIRS", 67);
define("COBBLESTONE_STAIRS", 67);
define("WALL_SIGN", 68);

define("IRON_DOOR_BLOCK", 71);

define("REDSTONE_ORE", 73);
define("GLOWING_REDSTONE_ORE", 74);
define("LIT_REDSTONE_ORE", 74);

define("SNOW", 78);
define("SNOW_LAYER", 78);
define("ICE", 79);
define("SNOW_BLOCK", 80);
define("CACTUS", 81);
define("CLAY_BLOCK", 82);
define("SUGARCANE_BLOCK", 83);

define("FENCE", 85);

define("NETHERRACK", 87);
define("SOUL_SAND", 88);
define("GLOWSTONE_BLOCK", 89);

define("TRAPDOOR", 96);

define("STONE_BRICKS", 98);
define("STONE_BRICK", 98);

define("GLASS_PANE", 102);
define("GLASS_PANEL", 102);
define("MELON_BLOCK", 103);

define("MELON_STEM", 105);

define("FENCE_GATE", 107);
define("BRICK_STAIRS", 108);
define("STONE_BRICK_STAIRS", 109);

define("NETHER_BRICKS", 112);

define("NETHER_BRICKS_STAIRS", 114);

define("SANDSTONE_STAIRS", 128);

define("QUARTZ_BLOCK", 155);
define("QUARTZ_STAIRS", 156);

define("STONECUTTER", 245);
define("GLOWING_OBSIDIAN", 246);
define("NETHER_REACTOR", 247);

// ---- Items ----
define("IRON_SHOVEL", 256);
define("IRON_PICKAXE", 257);
define("IRON_AXE", 258);
define("FLINT_STEEL", 259);
define("APPLE", 260);//Implemented
define("BOW", 261);
define("ARROW", 262);
define("COAL", 263);//Implemented
define("DIAMOND", 264);//Implemented
define("IRON_INGOT", 265);
define("GOLD_INGOT", 266);
define("IRON_SWORD", 267);
define("WOODEN_SWORD", 268);
define("WOODEN_SHOVEL", 269);
define("WOODEN_PICKAXE", 270);
define("WOODEN_AXE", 271);
define("STONE_SWORD", 272);
define("STONE_SHOVEL", 273);
define("STONE_PICKAXE", 274);
define("STONE_AXE", 275);
define("DIAMOND_SWORD", 276);
define("DIAMOND_SHOVEL", 277);
define("DIAMOND_PICKAXE", 278);
define("DIAMOND_AXE", 279);
define("STICK", 280);//Implemented
define("BOWL", 281);//Implemented
define("MUSHROOM_STEW", 282);
define("GOLD_SWORD", 283);
define("GOLD_SHOVEL", 284);
define("GOLD_PICKAXE", 285);
define("GOLD_AXE", 286);
define("STRING", 287);
define("FEATHER", 288);//Implemented
define("GUNPOWDER", 289);
define("WOODEN_HOE", 290);
define("STONE_HOE", 291);
define("IRON_HOE", 292);
define("DIAMOND_HOE", 293);
define("GOLD_HOE", 294);
define("SEEDS", 295);
define("WHEAT_SEEDS", 295);
define("WHEAT", 296);
define("BREAD", 297);
define("LEATHER_CAP", 298);
define("LEATHER_TUNIC", 299);
define("LEATHER_PANTS", 300);
define("LEATHER_BOOTS", 301);
define("CHAIN_HELMET", 302);
define("CHAIN_CHESTPLATE", 303);
define("CHAIN_LEGGINS", 304);
define("CHAIN_BOOTS", 305);
define("IRON_HELMET", 306);
define("IRON_CHESTPLATE", 307);
define("IRON_LEGGINS", 308);
define("IRON_BOOTS", 309);
define("DIAMOND_HELMET", 310);
define("DIAMOND_CHESTPLATE", 311);
define("DIAMOND_LEGGINS", 312);
define("DIAMOND_BOOTS", 313);
define("GOLD_HELMET", 314);
define("GOLD_CHESTPLATE", 315);
define("GOLD_LEGGINS", 316);
define("GOLD_BOOTS", 317);
define("FLINT", 318);
define("RAW_PORKCHOP", 319);
define("COOKED_PORKCHOP", 320);
define("PAINTING", 321);
define("GOLDEN_APPLE", 322);
define("SIGN", 323);
define("WOODEN_DOOR", 324);

define("IRON_DOOR", 330);

define("SNOWBALL", 332);

define("LEATHER", 334);

define("BRICK", 336);
define("CLAY", 337);
define("SUGARCANE", 338);
define("SUGAR_CANE", 338);
define("SUGAR_CANES", 338);
define("PAPER", 339);
define("SLIMEBALL", 341);

define("EGG", 344);
define("COMPASS", 345);

define("GLOWSTONE_DUST", 348);
define("RAW_FISH", 349);
define("COOKED_FISH", 350);
define("DYE", 351);
define("BONE", 352);
define("SUGAR", 353);

define("BED", 355);


define("COOKIE", 357);


define("SHEARS", 359);
define("MELON", 360);
define("MELON_SLICE", 360);

define("MELON_SEEDS", 362);
define("RAW_BEEF", 363);
define("STEAK", 364);
define("COOKED_BEEF", 364);

define("RAW_CHICKEN", 365);
define("COOKED_CHICKEN", 366);

define("NETHER_BRICK", 405);
define("QUARTZ", 406);
define("NETHER_QUARTZ", 406);

define("CAMERA", 456);

/*

define("", );
*/