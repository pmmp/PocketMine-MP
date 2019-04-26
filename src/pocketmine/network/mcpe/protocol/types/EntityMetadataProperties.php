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

namespace pocketmine\network\mcpe\protocol\types;

final class EntityMetadataProperties{

	private function __construct(){
		//NOOP
	}

	/*
	 * Readers beware: this isn't a nice list. Some of the properties have different types for different entities, and
	 * are used for entirely different things.
	 */
	public const FLAGS = 0;
	public const HEALTH = 1; //int (minecart/boat)
	public const VARIANT = 2; //int
	public const COLOR = 3, COLOUR = 3; //byte
	public const NAMETAG = 4; //string
	public const OWNER_EID = 5; //long
	public const TARGET_EID = 6; //long
	public const AIR = 7; //short
	public const POTION_COLOR = 8; //int (ARGB!)
	public const POTION_AMBIENT = 9; //byte
	/* 10 (byte) */
	public const HURT_TIME = 11; //int (minecart/boat)
	public const HURT_DIRECTION = 12; //int (minecart/boat)
	public const PADDLE_TIME_LEFT = 13; //float
	public const PADDLE_TIME_RIGHT = 14; //float
	public const EXPERIENCE_VALUE = 15; //int (xp orb)
	public const MINECART_DISPLAY_BLOCK = 16; //int (id | (data << 16))
	public const HORSE_FLAGS = 16; //int
	/* 16 (byte) used by wither skull */
	public const MINECART_DISPLAY_OFFSET = 17; //int
	public const SHOOTER_ID = 17; //long (used by arrows)
	public const MINECART_HAS_DISPLAY = 18; //byte (must be 1 for minecart to show block inside)
	public const HORSE_TYPE = 19; //byte
	/* 20 (unknown)
	 * 21 (unknown) */
	public const CHARGE_AMOUNT = 22; //int8, used for ghasts and also crossbow charging
	public const ENDERMAN_HELD_ITEM_ID = 23; //short
	public const ENTITY_AGE = 24; //short
	/* 25 (int) used by horse, (byte) used by witch */
	public const PLAYER_FLAGS = 26; //byte
	public const PLAYER_INDEX = 27; //int, used for marker colours and agent nametag colours
	public const PLAYER_BED_POSITION = 28; //blockpos
	public const FIREBALL_POWER_X = 29; //float
	public const FIREBALL_POWER_Y = 30;
	public const FIREBALL_POWER_Z = 31;
	/* 32 (unknown)
	 * 33 (float) fishing bobber
	 * 34 (float) fishing bobber
	 * 35 (float) fishing bobber */
	public const POTION_AUX_VALUE = 36; //short
	public const LEAD_HOLDER_EID = 37; //long
	public const SCALE = 38; //float
	public const HAS_NPC_COMPONENT = 39; //byte (???)
	public const SKIN_ID = 40; //string
	public const NPC_SKIN_ID = 41; //string
	public const URL_TAG = 42; //string
	public const MAX_AIR = 43; //short
	public const MARK_VARIANT = 44; //int
	public const CONTAINER_TYPE = 45; //byte (ContainerComponent)
	public const CONTAINER_BASE_SIZE = 46; //int (ContainerComponent)
	public const CONTAINER_EXTRA_SLOTS_PER_STRENGTH = 47; //int (used for llamas, inventory size is baseSize + thisProp * strength)
	public const BLOCK_TARGET = 48; //block coords (ender crystal)
	public const WITHER_INVULNERABLE_TICKS = 49; //int
	public const WITHER_TARGET_1 = 50; //long
	public const WITHER_TARGET_2 = 51; //long
	public const WITHER_TARGET_3 = 52; //long
	/* 53 (short) */
	public const BOUNDING_BOX_WIDTH = 54; //float
	public const BOUNDING_BOX_HEIGHT = 55; //float
	public const FUSE_LENGTH = 56; //int
	public const RIDER_SEAT_POSITION = 57; //vector3f
	public const RIDER_ROTATION_LOCKED = 58; //byte
	public const RIDER_MAX_ROTATION = 59; //float
	public const RIDER_MIN_ROTATION = 60; //float
	public const AREA_EFFECT_CLOUD_RADIUS = 61; //float
	public const AREA_EFFECT_CLOUD_WAITING = 62; //int
	public const AREA_EFFECT_CLOUD_PARTICLE_ID = 63; //int
	/* 64 (int) shulker-related */
	public const SHULKER_ATTACH_FACE = 65; //byte
	/* 66 (short) shulker-related */
	public const SHULKER_ATTACH_POS = 67; //block coords
	public const TRADING_PLAYER_EID = 68; //long

	/* 70 (byte) command-block */
	public const COMMAND_BLOCK_COMMAND = 71; //string
	public const COMMAND_BLOCK_LAST_OUTPUT = 72; //string
	public const COMMAND_BLOCK_TRACK_OUTPUT = 73; //byte
	public const CONTROLLING_RIDER_SEAT_NUMBER = 74; //byte
	public const STRENGTH = 75; //int
	public const MAX_STRENGTH = 76; //int
	/* 77 (int) */
	public const LIMITED_LIFE = 78;
	public const ARMOR_STAND_POSE_INDEX = 79; //int
	public const ENDER_CRYSTAL_TIME_OFFSET = 80; //int
	public const ALWAYS_SHOW_NAMETAG = 81; //byte: -1 = default, 0 = only when looked at, 1 = always
	public const COLOR_2 = 82; //byte
	/* 83 (unknown) */
	public const SCORE_TAG = 84; //string
	public const BALLOON_ATTACHED_ENTITY = 85; //int64, entity unique ID of owner
	public const PUFFERFISH_SIZE = 86; //byte
	public const BOAT_BUBBLE_TIME = 87; //int (time in bubble column)
	public const PLAYER_AGENT_EID = 88; //long
	/* 89 (float) related to panda sitting
	 * 90 (float) related to panda sitting */
	public const EAT_COUNTER = 91; //int (used by pandas)
	public const FLAGS2 = 92; //long (extended data flags)
	/* 93 (float) related to panda lying down
	 * 94 (float) related to panda lying down */
	public const AREA_EFFECT_CLOUD_DURATION = 95; //int
	public const AREA_EFFECT_CLOUD_SPAWN_TIME = 96; //int
	public const AREA_EFFECT_CLOUD_RADIUS_PER_TICK = 97; //float, usually negative
	public const AREA_EFFECT_CLOUD_RADIUS_CHANGE_ON_PICKUP = 98; //float
	public const AREA_EFFECT_CLOUD_PICKUP_COUNT = 99; //int
	public const INTERACTIVE_TAG = 100; //string (button text)
	public const TRADE_TIER = 101; //int
	public const MAX_TRADE_TIER = 102; //int
	public const TRADE_XP = 103; //int
}
