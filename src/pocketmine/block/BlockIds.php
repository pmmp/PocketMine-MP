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

namespace pocketmine\block;

interface BlockIds{

	const AIR = 0;
	const STONE = 1;
	const GRASS = 2;
	const DIRT = 3;
	const COBBLESTONE = 4, COBBLE = 4;
	const PLANK = 5, PLANKS = 5, WOODEN_PLANK = 5, WOODEN_PLANKS = 5;
	const SAPLING = 6, SAPLINGS = 6;
	const BEDROCK = 7;
	const WATER = 8;
	const STILL_WATER = 9;
	const LAVA = 10;
	const STILL_LAVA = 11;
	const SAND = 12;
	const GRAVEL = 13;
	const GOLD_ORE = 14;
	const IRON_ORE = 15;
	const COAL_ORE = 16;
	const LOG = 17, WOOD = 17, TRUNK = 17;
	const LEAVES = 18;
	const SPONGE = 19;
	const GLASS = 20;
	const LAPIS_ORE = 21;
	const LAPIS_BLOCK = 22;
	const DISPENSER = 23;
	const SANDSTONE = 24;
	const NOTE_BLOCK = 25, NOTEBLOCK = 25;
	const BED_BLOCK = 26;
	const POWERED_RAIL = 27;
	const DETECTOR_RAIL = 28;
	const STICKY_PISTON = 29;
	const COBWEB = 30;
	const TALL_GRASS = 31;
	const BUSH = 32, DEAD_BUSH = 32;
	const PISTON = 33;
	const PISTON_HEAD = 34;
	const WOOL = 35;

	const DANDELION = 37;
	const POPPY = 38, ROSE = 38, RED_FLOWER = 38;
	const BROWN_MUSHROOM = 39;
	const RED_MUSHROOM = 40;
	const GOLD_BLOCK = 41;
	const IRON_BLOCK = 42;
	const DOUBLE_SLAB = 43, DOUBLE_SLABS = 43;
	const SLAB = 44, SLABS = 44, STONE_SLAB = 44;
	const BRICKS = 45, BRICKS_BLOCK = 45;
	const TNT = 46;
	const BOOKSHELF = 47;
	const MOSS_STONE = 48, MOSSY_STONE = 48;
	const OBSIDIAN = 49;
	const TORCH = 50;
	const FIRE = 51;
	const MONSTER_SPAWNER = 52;
	const WOOD_STAIRS = 53, WOODEN_STAIRS = 53, OAK_WOOD_STAIRS = 53, OAK_WOODEN_STAIRS = 53;
	const CHEST = 54;
	const REDSTONE_WIRE = 55;
	const DIAMOND_ORE = 56;
	const DIAMOND_BLOCK = 57;
	const CRAFTING_TABLE = 58, WORKBENCH = 58;
	const WHEAT_BLOCK = 59;
	const FARMLAND = 60;
	const FURNACE = 61;
	const BURNING_FURNACE = 62, LIT_FURNACE = 62;
	const SIGN_POST = 63;
	const DOOR_BLOCK = 64, WOODEN_DOOR_BLOCK = 64, WOOD_DOOR_BLOCK = 64;
	const LADDER = 65;
	const RAIL = 66;
	const COBBLESTONE_STAIRS = 67, COBBLE_STAIRS = 67;
	const WALL_SIGN = 68;
	const LEVER = 69;
	const STONE_PRESSURE_PLATE = 70;
	const IRON_DOOR_BLOCK = 71;
	const WOODEN_PRESSURE_PLATE = 72;
	const REDSTONE_ORE = 73;
	const GLOWING_REDSTONE_ORE = 74, LIT_REDSTONE_ORE = 74;
	const UNLIT_REDSTONE_TORCH = 75;
	const REDSTONE_TORCH = 76, LIT_REDSTONE_TORCH = 76;
	const STONE_BUTTON = 77;
	const SNOW = 78, SNOW_LAYER = 78;
	const ICE = 79;
	const SNOW_BLOCK = 80;
	const CACTUS = 81;
	const CLAY_BLOCK = 82;
	const REEDS = 83, SUGARCANE_BLOCK = 83;

	const FENCE = 85;
	const PUMPKIN = 86;
	const NETHERRACK = 87;
	const SOUL_SAND = 88;
	const GLOWSTONE = 89, GLOWSTONE_BLOCK = 89;
	const PORTAL_BLOCK = 90, PORTAL = 90;
	const JACK_O_LANTERN = 91, LIT_PUMPKIN = 91;
	const CAKE_BLOCK = 92;
	const REPEATER_BLOCK = 93, UNPOWERED_REPEATER_BLOCK = 93;
	const POWERED_REPEATER_BLOCK = 94;
	const INVISIBLE_BEDROCK = 95;
	const TRAPDOOR = 96, WOODEN_TRAPDOOR = 96;
	const MONSTER_EGG_BLOCK = 97;
	const STONE_BRICKS = 98, STONE_BRICK = 98;
	const BROWN_MUSHROOM_BLOCK = 99;
	const RED_MUSHROOM_BLOCK = 100;
	const IRON_BARS = 101, IRON_BAR = 101;
	const GLASS_PANE = 102, GLASS_PANEL = 102;
	const MELON_BLOCK = 103;
	const PUMPKIN_STEM = 104;
	const MELON_STEM = 105;
	const VINES = 106, VINE = 106;
	const FENCE_GATE = 107, OAK_FENCE_GATE = 107;
	const BRICK_STAIRS = 108;
	const STONE_BRICK_STAIRS = 109;
	const MYCELIUM = 110;
	const LILY_PAD = 111, WATER_LILY = 111;
	const NETHER_BRICKS = 112, NETHER_BRICK_BLOCK = 112;
	const NETHER_BRICK_FENCE = 113;
	const NETHER_BRICK_STAIRS = 114, NETHER_BRICKS_STAIRS = 114;
	const NETHER_WART_BLOCK = 115;
	const ENCHANTING_TABLE = 116, ENCHANT_TABLE = 116, ENCHANTMENT_TABLE = 116;
	const BREWING_STAND_BLOCK = 117;
	const CAULDRON_BLOCK = 118;

	const END_PORTAL_FRAME = 120, END_PORTAL = 120;
	const END_STONE = 121;

	const REDSTONE_LAMP = 123, INACTIVE_REDSTONE_LAMP = 123;
	const LIT_REDSTONE_LAMP = 124, ACTIVE_REDSTONE_LAMP = 124;
	const DROPPER = 125;
	const ACTIVATOR_RAIL = 126;
	const COCOA_BLOCK = 127, COCOA_PODS = 127;
	const SANDSTONE_STAIRS = 128;
	const EMERALD_ORE = 129;

	const TRIPWIRE_HOOK = 131;
	const TRIPWIRE = 132;
	const EMERALD_BLOCK = 133;
	const SPRUCE_WOOD_STAIRS = 134, SPRUCE_WOODEN_STAIRS = 134;
	const BIRCH_WOOD_STAIRS = 135, BIRCH_WOODEN_STAIRS = 135;
	const JUNGLE_WOOD_STAIRS = 136, JUNGLE_WOODEN_STAIRS = 136;

	const BEACON = 138;
	const COBBLESTONE_WALL = 139, COBBLE_WALL = 139, STONE_WALL = 139;
	const FLOWER_POT_BLOCK = 140;
	const CARROT_BLOCK = 141;
	const POTATO_BLOCK = 142;
	const WOODEN_BUTTON = 143;
	const MOB_HEAD_BLOCK = 144, SKULL_BLOCK = 144;
	const ANVIL = 145;
	const TRAPPED_CHEST = 146;
	const WEIGHTED_PRESSURE_PLATE_LIGHT = 147, LIGHT_WEIGHTED_PRESSURE_PLATE = 147, GOLD_PRESSURE_PLATE = 147;
	const WEIGHTED_PRESSURE_PLATE_HEAVY = 148, HEAVY_WEIGHTED_PRESSURE_PLATE = 148, IRON_PRESSURE_PLATE = 148;
	const COMPARATOR_BLOCK = 149, UNPOWERED_COMPARATOR_BLOCK = 149;
	const POWERED_COMPARATOR_BLOCK = 150;
	const DAYLIGHT_SENSOR = 151;
	const REDSTONE_BLOCK = 152;
	const NETHER_QUARTZ_ORE = 153;
	const HOPPER_BLOCK = 154;
	const QUARTZ_BLOCK = 155;
	const QUARTZ_STAIRS = 156;
	const DOUBLE_WOOD_SLAB = 157, DOUBLE_WOODEN_SLAB = 157, DOUBLE_WOOD_SLABS = 157, DOUBLE_WOODEN_SLABS = 157;
	const WOOD_SLAB = 158, WOODEN_SLAB = 158, WOOD_SLABS = 158, WOODEN_SLABS = 158;
	const STAINED_CLAY = 159, STAINED_HARDENED_CLAY = 159;

	const LEAVES2 = 161;
	const WOOD2 = 162, TRUNK2 = 162, LOG2 = 162;
	const ACACIA_WOOD_STAIRS = 163, ACACIA_WOODEN_STAIRS = 163;
	const DARK_OAK_WOOD_STAIRS = 164, DARK_OAK_WOODEN_STAIRS = 164;
	const SLIME_BLOCK = 165;

	const IRON_TRAPDOOR = 167;
	const PRISMARINE = 168;
	const SEA_LANTERN = 169;
	const HAY_BALE = 170;
	const CARPET = 171;
	const HARDENED_CLAY = 172;
	const COAL_BLOCK = 173;
	const PACKED_ICE = 174;
	const DOUBLE_PLANT = 175;

	const INVERTED_DAYLIGHT_SENSOR = 178, DAYLIGHT_SENSOR_INVERTED = 178;
	const RED_SANDSTONE = 179;
	const RED_SANDSTONE_STAIRS = 180;
	const DOUBLE_RED_SANDSTONE_SLAB = 181;
	const RED_SANDSTONE_SLAB = 182;
	const SPRUCE_FENCE_GATE = 183, FENCE_GATE_SPRUCE = 183;
	const BIRCH_FENCE_GATE = 184, FENCE_GATE_BIRCH = 184;
	const JUNGLE_FENCE_GATE = 185, FENCE_GATE_JUNGLE = 185;
	const DARK_OAK_FENCE_GATE = 186, FENCE_GATE_DARK_OAK = 186;
	const ACACIA_FENCE_GATE = 187, FENCE_GATE_ACACIA = 187;

	const SPRUCE_DOOR_BLOCK = 193;
	const BIRCH_DOOR_BLOCK = 194;
	const JUNGLE_DOOR_BLOCK = 195;
	const ACACIA_DOOR_BLOCK = 196;
	const DARK_OAK_DOOR_BLOCK = 197;
	const GRASS_PATH = 198;
	const ITEM_FRAME_BLOCK = 199;

	const PODZOL = 243;
	const BEETROOT_BLOCK = 244;
	const STONECUTTER = 245;
	const GLOWING_OBSIDIAN = 246;
	const NETHER_REACTOR = 247;
	const UPDATE_BLOCK = 248;
	const ATEUPD_BLOCK = 249;
	const BLOCK_MOVED_BY_PISTON = 250;
	const OBSERVER = 251;
	const INFO_RESERVED6 = 255;
}
