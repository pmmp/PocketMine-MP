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
	const COBBLESTONE = 4;
	const PLANKS = 5, WOODEN_PLANKS = 5;
	const SAPLING = 6;
	const BEDROCK = 7;
	const FLOWING_WATER = 8;
	const STILL_WATER = 9, WATER = 9;
	const FLOWING_LAVA = 10;
	const LAVA = 11, STILL_LAVA = 11;
	const SAND = 12;
	const GRAVEL = 13;
	const GOLD_ORE = 14;
	const IRON_ORE = 15;
	const COAL_ORE = 16;
	const LOG = 17, WOOD = 17;
	const LEAVES = 18;
	const SPONGE = 19;
	const GLASS = 20;
	const LAPIS_ORE = 21;
	const LAPIS_BLOCK = 22;
	const DISPENSER = 23;
	const SANDSTONE = 24;
	const NOTEBLOCK = 25, NOTE_BLOCK = 25;
	const BED_BLOCK = 26;
	const GOLDEN_RAIL = 27, POWERED_RAIL = 27;
	const DETECTOR_RAIL = 28;
	const STICKY_PISTON = 29;
	const COBWEB = 30, WEB = 30;
	const TALLGRASS = 31, TALL_GRASS = 31;
	const DEADBUSH = 32, DEAD_BUSH = 32;
	const PISTON = 33;
	const PISTONARMCOLLISION = 34, PISTON_ARM_COLLISION = 34;
	const WOOL = 35;

	const DANDELION = 37, YELLOW_FLOWER = 37;
	const POPPY = 38, RED_FLOWER = 38;
	const BROWN_MUSHROOM = 39;
	const RED_MUSHROOM = 40;
	const GOLD_BLOCK = 41;
	const IRON_BLOCK = 42;
	const DOUBLE_STONE_SLAB = 43;
	const STONE_SLAB = 44;
	const BRICK_BLOCK = 45;
	const TNT = 46;
	const BOOKSHELF = 47;
	const MOSSY_COBBLESTONE = 48, MOSS_STONE = 48;
	const OBSIDIAN = 49;
	const TORCH = 50;
	const FIRE = 51;
	const MOB_SPAWNER = 52, MONSTER_SPAWNER = 52;
	const OAK_STAIRS = 53, WOODEN_STAIRS = 53;
	const CHEST = 54;
	const REDSTONE_WIRE = 55;
	const DIAMOND_ORE = 56;
	const DIAMOND_BLOCK = 57;
	const CRAFTING_TABLE = 58, WORKBENCH = 58;
	const WHEAT_BLOCK = 59;
	const FARMLAND = 60;
	const FURNACE = 61;
	const BURNING_FURNACE = 62, LIT_FURNACE = 62;
	const SIGN_POST = 63, STANDING_SIGN = 63;
	const OAK_DOOR_BLOCK = 64, WOODEN_DOOR_BLOCK = 64;
	const LADDER = 65;
	const RAIL = 66;
	const COBBLESTONE_STAIRS = 67, STONE_STAIRS = 67;
	const WALL_SIGN = 68;
	const LEVER = 69;
	const STONE_PRESSURE_PLATE = 70;
	const IRON_DOOR_BLOCK = 71;
	const WOODEN_PRESSURE_PLATE = 72;
	const REDSTONE_ORE = 73;
	const GLOWING_REDSTONE_ORE = 74, LIT_REDSTONE_ORE = 74;
	const UNLIT_REDSTONE_TORCH = 75;
	const LIT_REDSTONE_TORCH = 76, REDSTONE_TORCH = 76;
	const STONE_BUTTON = 77;
	const SNOW_LAYER = 78;
	const ICE = 79;
	const SNOW = 80, SNOW_BLOCK = 80;
	const CACTUS = 81;
	const CLAY_BLOCK = 82;
	const REEDS_BLOCK = 83, SUGARCANE_BLOCK = 83;

	const FENCE = 85;
	const PUMPKIN = 86;
	const NETHERRACK = 87;
	const SOUL_SAND = 88;
	const GLOWSTONE = 89;
	const PORTAL = 90;
	const JACK_O_LANTERN = 91, LIT_PUMPKIN = 91;
	const CAKE_BLOCK = 92;
	const REPEATER_BLOCK = 93, UNPOWERED_REPEATER = 93;
	const POWERED_REPEATER = 94;
	const INVISIBLEBEDROCK = 95, INVISIBLE_BEDROCK = 95;
	const TRAPDOOR = 96, WOODEN_TRAPDOOR = 96;
	const MONSTER_EGG = 97;
	const STONEBRICK = 98, STONE_BRICK = 98, STONE_BRICKS = 98;
	const BROWN_MUSHROOM_BLOCK = 99;
	const RED_MUSHROOM_BLOCK = 100;
	const IRON_BARS = 101;
	const GLASS_PANE = 102;
	const MELON_BLOCK = 103;
	const PUMPKIN_STEM = 104;
	const MELON_STEM = 105;
	const VINE = 106, VINES = 106;
	const FENCE_GATE = 107, OAK_FENCE_GATE = 107;
	const BRICK_STAIRS = 108;
	const STONE_BRICK_STAIRS = 109;
	const MYCELIUM = 110;
	const LILY_PAD = 111, WATERLILY = 111, WATER_LILY = 111;
	const NETHER_BRICK = 112, NETHER_BRICK_BLOCK = 112;
	const NETHER_BRICK_FENCE = 113;
	const NETHER_BRICK_STAIRS = 114;
	const NETHER_WART_PLANT = 115;
	const ENCHANTING_TABLE = 116, ENCHANTMENT_TABLE = 116;
	const BREWING_STAND_BLOCK = 117;
	const CAULDRON_BLOCK = 118;
	const END_PORTAL = 119;
	const END_PORTAL_FRAME = 120;
	const END_STONE = 121;
	const DRAGON_EGG = 122;
	const REDSTONE_LAMP = 123;
	const LIT_REDSTONE_LAMP = 124;
	const DROPPER = 125;
	const ACTIVATOR_RAIL = 126;
	const COCOA = 127, COCOA_BLOCK = 127;
	const SANDSTONE_STAIRS = 128;
	const EMERALD_ORE = 129;
	const ENDER_CHEST = 130;
	const TRIPWIRE_HOOK = 131;
	const TRIPWIRE = 132, TRIP_WIRE = 132;
	const EMERALD_BLOCK = 133;
	const SPRUCE_STAIRS = 134;
	const BIRCH_STAIRS = 135;
	const JUNGLE_STAIRS = 136;
	const COMMAND_BLOCK = 137;
	const BEACON = 138;
	const COBBLESTONE_WALL = 139, STONE_WALL = 139;
	const FLOWER_POT_BLOCK = 140;
	const CARROTS = 141, CARROT_BLOCK = 141;
	const POTATOES = 142, POTATO_BLOCK = 142;
	const WOODEN_BUTTON = 143;
	const MOB_HEAD_BLOCK = 144, SKULL_BLOCK = 144;
	const ANVIL = 145;
	const TRAPPED_CHEST = 146;
	const LIGHT_WEIGHTED_PRESSURE_PLATE = 147;
	const HEAVY_WEIGHTED_PRESSURE_PLATE = 148;
	const COMPATATOR_BLOCK = 149, UNPOWERED_COMPARATOR = 149;
	const POWERED_COMPARATOR = 150;
	const DAYLIGHT_DETECTOR = 151, DAYLIGHT_SENSOR = 151;
	const REDSTONE_BLOCK = 152;
	const NETHER_QUARTZ_ORE = 153, QUARTZ_ORE = 153;
	const HOPPER_BLOCK = 154;
	const QUARTZ_BLOCK = 155;
	const QUARTZ_STAIRS = 156;
	const DOUBLE_WOODEN_SLAB = 157;
	const WOODEN_SLAB = 158;
	const STAINED_CLAY = 159, STAINED_HARDENED_CLAY = 159, TERRACOTTA = 159;
	const STAINED_GLASS_PANE = 160;
	const LEAVES2 = 161;
	const LOG2 = 162, WOOD2 = 162;
	const ACACIA_STAIRS = 163;
	const DARK_OAK_STAIRS = 164;
	const SLIME = 165, SLIME_BLOCK = 165;

	const IRON_TRAPDOOR = 167;
	const PRISMARINE = 168;
	const SEALANTERN = 169, SEA_LANTERN = 169;
	const HAY_BALE = 170, HAY_BLOCK = 170;
	const CARPET = 171;
	const HARDENED_CLAY = 172;
	const COAL_BLOCK = 173;
	const PACKED_ICE = 174;
	const DOUBLE_PLANT = 175;

	const DAYLIGHT_DETECTOR_INVERTED = 178, DAYLIGHT_SENSOR_INVERTED = 178;
	const RED_SANDSTONE = 179;
	const RED_SANDSTONE_STAIRS = 180;
	const DOUBLE_STONE_SLAB2 = 181;
	const STONE_SLAB2 = 182;
	const SPRUCE_FENCE_GATE = 183;
	const BIRCH_FENCE_GATE = 184;
	const JUNGLE_FENCE_GATE = 185;
	const DARK_OAK_FENCE_GATE = 186;
	const ACACIA_FENCE_GATE = 187;
	const REPEATING_COMMAND_BLOCK = 188;
	const CHAIN_COMMAND_BLOCK = 189;

	const SPRUCE_DOOR_BLOCK = 193;
	const BIRCH_DOOR_BLOCK = 194;
	const JUNGLE_DOOR_BLOCK = 195;
	const ACACIA_DOOR_BLOCK = 196;
	const DARK_OAK_DOOR_BLOCK = 197;
	const GRASS_PATH = 198;
	const FRAME_BLOCK = 199, ITEM_FRAME_BLOCK = 199;
	const CHORUS_FLOWER = 200;
	const PURPUR_BLOCK = 201;

	const PURPUR_STAIRS = 203;

	const END_BRICKS = 206;
	const FROSTED_ICE = 207;
	const END_ROD = 208;
	const END_GATEWAY = 209;

	const MAGMA = 213;
	const NETHER_WART_BLOCK = 214;
	const RED_NETHER_BRICK = 215;
	const BONE_BLOCK = 216;

	const SHULKER_BOX = 218;
	const PURPLE_GLAZED_TERRACOTTA = 219;
	const WHITE_GLAZED_TERRACOTTA = 220;
	const ORANGE_GLAZED_TERRACOTTA = 221;
	const MAGENTA_GLAZED_TERRACOTTA = 222;
	const LIGHT_BLUE_GLAZED_TERRACOTTA = 223;
	const YELLOW_GLAZED_TERRACOTTA = 224;
	const LIME_GLAZED_TERRACOTTA = 225;
	const PINK_GLAZED_TERRACOTTA = 226;
	const GRAY_GLAZED_TERRACOTTA = 227;
	const SILVER_GLAZED_TERRACOTTA = 228;
	const CYAN_GLAZED_TERRACOTTA = 229;

	const BLUE_GLAZED_TERRACOTTA = 231;
	const BROWN_GLAZED_TERRACOTTA = 232;
	const GREEN_GLAZED_TERRACOTTA = 233;
	const RED_GLAZED_TERRACOTTA = 234;
	const BLACK_GLAZED_TERRACOTTA = 235;
	const CONCRETE = 236;
	const CONCRETEPOWDER = 237, CONCRETE_POWDER = 237;

	const CHORUS_PLANT = 240;
	const STAINED_GLASS = 241;

	const PODZOL = 243;
	const BEETROOT_BLOCK = 244;
	const STONECUTTER = 245;
	const GLOWINGOBSIDIAN = 246, GLOWING_OBSIDIAN = 246;
	const NETHERREACTOR = 247, NETHER_REACTOR = 247;
	const INFO_UPDATE = 248;
	const INFO_UPDATE2 = 249;
	const MOVINGBLOCK = 250, MOVING_BLOCK = 250;
	const OBSERVER = 251;

	const RESERVED6 = 255;

}
