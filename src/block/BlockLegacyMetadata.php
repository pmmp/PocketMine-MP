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

/**
 * Constants for legacy metadata for various blocks.
 */
final class BlockLegacyMetadata{

	private function __construct(){
		//NOOP
	}

	public const ANVIL_NORMAL = 0;
	public const ANVIL_SLIGHTLY_DAMAGED = 4;
	public const ANVIL_VERY_DAMAGED = 8;

	public const BAMBOO_FLAG_THICK = 0x01;
	public const BAMBOO_FLAG_READY = 0x08;

	public const BAMBOO_LEAF_SIZE_SHIFT = 1;
	public const BAMBOO_LEAF_SIZE_MASK = 0x03;

	public const BARREL_FLAG_OPEN = 0x08;

	public const BED_FLAG_HEAD = 0x08;
	public const BED_FLAG_OCCUPIED = 0x04;

	public const BEDROCK_FLAG_INFINIBURN = 0x01;

	public const BREWING_STAND_FLAG_EAST = 0x01;
	public const BREWING_STAND_FLAG_SOUTHWEST = 0x02;
	public const BREWING_STAND_FLAG_NORTHWEST = 0x04;

	public const BUTTON_FLAG_POWERED = 0x08;

	public const CHEMISTRY_COMPOUND_CREATOR = 0;
	public const CHEMISTRY_MATERIAL_REDUCER = 4;
	public const CHEMISTRY_ELEMENT_CONSTRUCTOR = 8;
	public const CHEMISTRY_LAB_TABLE = 12;

	public const COLORED_TORCH_BP_BLUE = 0;
	public const COLORED_TORCH_BP_PURPLE = 8;
	public const COLORED_TORCH_RG_RED = 0;
	public const COLORED_TORCH_RG_GREEN = 8;

	public const CORAL_BLOCK_FLAG_DEAD = 0x8;

	public const CORAL_FAN_EAST_WEST = 0;
	public const CORAL_FAN_NORTH_SOUTH = 1;

	public const CORAL_FAN_HANG_FLAG_DEAD = 0x2;

	public const CORAL_FAN_HANG_TUBE = 0;
	public const CORAL_FAN_HANG_BRAIN = 1;
	public const CORAL_FAN_HANG2_BUBBLE = 0;
	public const CORAL_FAN_HANG2_FIRE = 1;
	public const CORAL_FAN_HANG3_HORN = 0;

	public const CORAL_VARIANT_TUBE = 0;
	public const CORAL_VARIANT_BRAIN = 1;
	public const CORAL_VARIANT_BUBBLE = 2;
	public const CORAL_VARIANT_FIRE = 3;
	public const CORAL_VARIANT_HORN = 4;

	public const DIRT_NORMAL = 0;
	public const DIRT_COARSE = 1;

	public const DOOR_FLAG_TOP = 0x08;
	public const DOOR_BOTTOM_FLAG_OPEN = 0x04;
	public const DOOR_TOP_FLAG_RIGHT = 0x01;
	public const DOOR_TOP_FLAG_POWERED = 0x02;

	public const DOUBLE_PLANT_SUNFLOWER = 0;
	public const DOUBLE_PLANT_LILAC = 1;
	public const DOUBLE_PLANT_TALLGRASS = 2;
	public const DOUBLE_PLANT_LARGE_FERN = 3;
	public const DOUBLE_PLANT_ROSE_BUSH = 4;
	public const DOUBLE_PLANT_PEONY = 5;

	public const DOUBLE_PLANT_FLAG_TOP = 0x08;

	public const END_PORTAL_FRAME_FLAG_EYE = 0x04;

	public const FENCE_GATE_FLAG_OPEN = 0x04;
	public const FENCE_GATE_FLAG_IN_WALL = 0x08;

	public const FLOWER_POPPY = 0;
	public const FLOWER_BLUE_ORCHID = 1;
	public const FLOWER_ALLIUM = 2;
	public const FLOWER_AZURE_BLUET = 3;
	public const FLOWER_RED_TULIP = 4;
	public const FLOWER_ORANGE_TULIP = 5;
	public const FLOWER_WHITE_TULIP = 6;
	public const FLOWER_PINK_TULIP = 7;
	public const FLOWER_OXEYE_DAISY = 8;
	public const FLOWER_CORNFLOWER = 9;
	public const FLOWER_LILY_OF_THE_VALLEY = 10;

	public const FLOWER_POT_FLAG_OCCUPIED = 0x01;

	public const HOPPER_FLAG_POWERED = 0x08;

	public const INFESTED_STONE = 0;
	public const INFESTED_COBBLESTONE = 1;
	public const INFESTED_STONE_BRICK = 2;
	public const INFESTED_STONE_BRICK_MOSSY = 3;
	public const INFESTED_STONE_BRICK_CRACKED = 4;
	public const INFESTED_STONE_BRICK_CHISELED = 5;

	public const ITEM_FRAME_FLAG_HAS_MAP = 0x04;

	public const LANTERN_FLAG_HANGING = 0x01;

	public const LEAVES_FLAG_NO_DECAY = 0x04;
	public const LEAVES_FLAG_CHECK_DECAY = 0x08;

	public const LEVER_FLAG_POWERED = 0x08;

	public const LIQUID_FLAG_FALLING = 0x08;

	public const MUSHROOM_BLOCK_ALL_PORES = 0;
	public const MUSHROOM_BLOCK_CAP_NORTHWEST_CORNER = 1;
	public const MUSHROOM_BLOCK_CAP_NORTH_SIDE = 2;
	public const MUSHROOM_BLOCK_CAP_NORTHEAST_CORNER = 3;
	public const MUSHROOM_BLOCK_CAP_WEST_SIDE = 4;
	public const MUSHROOM_BLOCK_CAP_TOP_ONLY = 5;
	public const MUSHROOM_BLOCK_CAP_EAST_SIDE = 6;
	public const MUSHROOM_BLOCK_CAP_SOUTHWEST_CORNER = 7;
	public const MUSHROOM_BLOCK_CAP_SOUTH_SIDE = 8;
	public const MUSHROOM_BLOCK_CAP_SOUTHEAST_CORNER = 9;
	public const MUSHROOM_BLOCK_STEM = 10;
	//11, 12 and 13 appear the same as 0
	public const MUSHROOM_BLOCK_ALL_CAP = 14;
	public const MUSHROOM_BLOCK_ALL_STEM = 15;

	public const NETHER_PORTAL_AXIS_X = 1;
	public const NETHER_PORTAL_AXIS_Z = 2;

	public const NETHER_REACTOR_INACTIVE = 0;
	public const NETHER_REACTOR_ACTIVE = 1;
	public const NETHER_REACTOR_USED = 2;

	public const PRESSURE_PLATE_FLAG_POWERED = 0x01;

	public const PRISMARINE_NORMAL = 0;
	public const PRISMARINE_DARK = 1;
	public const PRISMARINE_BRICKS = 2;

	public const PURPUR_NORMAL = 0;
	public const PURPUR_PILLAR = 2;

	public const QUARTZ_NORMAL = 0;
	public const QUARTZ_CHISELED = 1;
	public const QUARTZ_PILLAR = 2;
	public const QUARTZ_SMOOTH = 3;

	public const RAIL_STRAIGHT_NORTH_SOUTH = 0;
	public const RAIL_STRAIGHT_EAST_WEST = 1;
	public const RAIL_ASCENDING_EAST = 2;
	public const RAIL_ASCENDING_WEST = 3;
	public const RAIL_ASCENDING_NORTH = 4;
	public const RAIL_ASCENDING_SOUTH = 5;
	public const RAIL_CURVE_SOUTHEAST = 6;
	public const RAIL_CURVE_SOUTHWEST = 7;
	public const RAIL_CURVE_NORTHWEST = 8;
	public const RAIL_CURVE_NORTHEAST = 9;

	public const REDSTONE_COMPARATOR_FLAG_SUBTRACT = 0x04;
	public const REDSTONE_COMPARATOR_FLAG_POWERED = 0x08;

	public const REDSTONE_RAIL_FLAG_POWERED = 0x08;

	public const SANDSTONE_NORMAL = 0;
	public const SANDSTONE_CHISELED = 1;
	public const SANDSTONE_CUT = 2;
	public const SANDSTONE_SMOOTH = 3;

	public const SAPLING_FLAG_READY = 0x08;

	public const SEA_PICKLE_FLAG_NOT_UNDERWATER = 0x04;

	public const SLAB_FLAG_UPPER = 0x08;

	public const SPONGE_FLAG_WET = 0x01;

	public const STAIR_FLAG_UPSIDE_DOWN = 0x04;

	public const STONE_NORMAL = 0;
	public const STONE_GRANITE = 1;
	public const STONE_POLISHED_GRANITE = 2;
	public const STONE_DIORITE = 3;
	public const STONE_POLISHED_DIORITE = 4;
	public const STONE_ANDESITE = 5;
	public const STONE_POLISHED_ANDESITE = 6;

	public const STONE_BRICK_NORMAL = 0;
	public const STONE_BRICK_MOSSY = 1;
	public const STONE_BRICK_CRACKED = 2;
	public const STONE_BRICK_CHISELED = 3;

	public const STONE_SLAB_SMOOTH_STONE = 0;
	public const STONE_SLAB_SANDSTONE = 1;
	public const STONE_SLAB_FAKE_WOODEN = 2;
	public const STONE_SLAB_COBBLESTONE = 3;
	public const STONE_SLAB_BRICK = 4;
	public const STONE_SLAB_STONE_BRICK = 5;
	public const STONE_SLAB_QUARTZ = 6;
	public const STONE_SLAB_NETHER_BRICK = 7;
	public const STONE_SLAB2_RED_SANDSTONE = 0;
	public const STONE_SLAB2_PURPUR = 1;
	public const STONE_SLAB2_PRISMARINE = 2;
	public const STONE_SLAB2_DARK_PRISMARINE = 3;
	public const STONE_SLAB2_PRISMARINE_BRICKS = 4;
	public const STONE_SLAB2_MOSSY_COBBLESTONE = 5;
	public const STONE_SLAB2_SMOOTH_SANDSTONE = 6;
	public const STONE_SLAB2_RED_NETHER_BRICK = 7;
	public const STONE_SLAB3_END_STONE_BRICK = 0;
	public const STONE_SLAB3_SMOOTH_RED_SANDSTONE = 1;
	public const STONE_SLAB3_POLISHED_ANDESITE = 2;
	public const STONE_SLAB3_ANDESITE = 3;
	public const STONE_SLAB3_DIORITE = 4;
	public const STONE_SLAB3_POLISHED_DIORITE = 5;
	public const STONE_SLAB3_GRANITE = 6;
	public const STONE_SLAB3_POLISHED_GRANITE = 7;
	public const STONE_SLAB4_MOSSY_STONE_BRICK = 0;
	public const STONE_SLAB4_SMOOTH_QUARTZ = 1;
	public const STONE_SLAB4_STONE = 2;
	public const STONE_SLAB4_CUT_SANDSTONE = 3;
	public const STONE_SLAB4_CUT_RED_SANDSTONE = 4;

	public const TALLGRASS_NORMAL = 1;
	public const TALLGRASS_FERN = 2;

	public const TNT_FLAG_UNSTABLE = 0x01;
	public const TNT_FLAG_UNDERWATER = 0x02;

	public const TRAPDOOR_FLAG_UPPER = 0x04;
	public const TRAPDOOR_FLAG_OPEN = 0x08;

	public const TRIPWIRE_FLAG_TRIGGERED = 0x01;
	public const TRIPWIRE_FLAG_SUSPENDED = 0x02;
	public const TRIPWIRE_FLAG_CONNECTED = 0x04;
	public const TRIPWIRE_FLAG_DISARMED = 0x08;

	public const TRIPWIRE_HOOK_FLAG_CONNECTED = 0x04;
	public const TRIPWIRE_HOOK_FLAG_POWERED = 0x08;

	public const VINE_FLAG_SOUTH = 0x01;
	public const VINE_FLAG_WEST = 0x02;
	public const VINE_FLAG_NORTH = 0x04;
	public const VINE_FLAG_EAST = 0x08;

	public const WALL_COBBLESTONE = 0;
	public const WALL_MOSSY_COBBLESTONE = 1;
	public const WALL_GRANITE = 2;
	public const WALL_DIORITE = 3;
	public const WALL_ANDESITE = 4;
	public const WALL_SANDSTONE = 5;
	public const WALL_BRICK = 6;
	public const WALL_STONE_BRICK = 7;
	public const WALL_MOSSY_STONE_BRICK = 8;
	public const WALL_NETHER_BRICK = 9;
	public const WALL_END_STONE_BRICK = 10;
	public const WALL_PRISMARINE = 11;
	public const WALL_RED_SANDSTONE = 12;
	public const WALL_RED_NETHER_BRICK = 13;
}
