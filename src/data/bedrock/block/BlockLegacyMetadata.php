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

namespace pocketmine\data\bedrock\block;

/**
 * Constants for legacy metadata for various blocks.
 */
final class BlockLegacyMetadata{

	private function __construct(){
		//NOOP
	}

	public const CORAL_VARIANT_TUBE = 0;
	public const CORAL_VARIANT_BRAIN = 1;
	public const CORAL_VARIANT_BUBBLE = 2;
	public const CORAL_VARIANT_FIRE = 3;
	public const CORAL_VARIANT_HORN = 4;

	public const MULTI_FACE_DIRECTION_FLAG_DOWN = 0x01;
	public const MULTI_FACE_DIRECTION_FLAG_UP = 0x02;
	public const MULTI_FACE_DIRECTION_FLAG_SOUTH = 0x04;
	public const MULTI_FACE_DIRECTION_FLAG_WEST = 0x08;
	public const MULTI_FACE_DIRECTION_FLAG_NORTH = 0x10;
	public const MULTI_FACE_DIRECTION_FLAG_EAST = 0x20;

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

	public const VINE_FLAG_SOUTH = 0x01;
	public const VINE_FLAG_WEST = 0x02;
	public const VINE_FLAG_NORTH = 0x04;
	public const VINE_FLAG_EAST = 0x08;
}
