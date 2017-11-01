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

namespace pocketmine\network\mcpe\protocol;

/**
 * Version numbers and packet IDs for the current Minecraft PE protocol
 */
interface ProtocolInfo{

	/**
	 * NOTE TO DEVELOPERS
	 * Do not waste your time or ours submitting pull requests changing game and/or protocol version numbers.
	 * Pull requests changing game and/or protocol version numbers will be closed.
	 *
	 * This file is generated automatically, do not edit it manually.
	 */

	/**
	 * Actual Minecraft: PE protocol version
	 */
	const CURRENT_PROTOCOL = 141;
	/**
	 * Current Minecraft PE version reported by the server. This is usually the earliest currently supported version.
	 */
	const MINECRAFT_VERSION = 'v1.2.5.15 beta';
	/**
	 * Version number sent to clients in ping responses.
	 */
	const MINECRAFT_VERSION_NETWORK = '1.2.5.15';

	const LOGIN_PACKET = 0x01;
	const PLAY_STATUS_PACKET = 0x02;
	const SERVER_TO_CLIENT_HANDSHAKE_PACKET = 0x03;
	const CLIENT_TO_SERVER_HANDSHAKE_PACKET = 0x04;
	const DISCONNECT_PACKET = 0x05;
	const RESOURCE_PACKS_INFO_PACKET = 0x06;
	const RESOURCE_PACK_STACK_PACKET = 0x07;
	const RESOURCE_PACK_CLIENT_RESPONSE_PACKET = 0x08;
	const TEXT_PACKET = 0x09;
	const SET_TIME_PACKET = 0x0a;
	const START_GAME_PACKET = 0x0b;
	const ADD_PLAYER_PACKET = 0x0c;
	const ADD_ENTITY_PACKET = 0x0d;
	const REMOVE_ENTITY_PACKET = 0x0e;
	const ADD_ITEM_ENTITY_PACKET = 0x0f;
	const ADD_HANGING_ENTITY_PACKET = 0x10;
	const TAKE_ITEM_ENTITY_PACKET = 0x11;
	const MOVE_ENTITY_PACKET = 0x12;
	const MOVE_PLAYER_PACKET = 0x13;
	const RIDER_JUMP_PACKET = 0x14;
	const UPDATE_BLOCK_PACKET = 0x15;
	const ADD_PAINTING_PACKET = 0x16;
	const EXPLODE_PACKET = 0x17;
	const LEVEL_SOUND_EVENT_PACKET = 0x18;
	const LEVEL_EVENT_PACKET = 0x19;
	const BLOCK_EVENT_PACKET = 0x1a;
	const ENTITY_EVENT_PACKET = 0x1b;
	const MOB_EFFECT_PACKET = 0x1c;
	const UPDATE_ATTRIBUTES_PACKET = 0x1d;
	const INVENTORY_TRANSACTION_PACKET = 0x1e;
	const MOB_EQUIPMENT_PACKET = 0x1f;
	const MOB_ARMOR_EQUIPMENT_PACKET = 0x20;
	const INTERACT_PACKET = 0x21;
	const BLOCK_PICK_REQUEST_PACKET = 0x22;
	const ENTITY_PICK_REQUEST_PACKET = 0x23;
	const PLAYER_ACTION_PACKET = 0x24;
	const ENTITY_FALL_PACKET = 0x25;
	const HURT_ARMOR_PACKET = 0x26;
	const SET_ENTITY_DATA_PACKET = 0x27;
	const SET_ENTITY_MOTION_PACKET = 0x28;
	const SET_ENTITY_LINK_PACKET = 0x29;
	const SET_HEALTH_PACKET = 0x2a;
	const SET_SPAWN_POSITION_PACKET = 0x2b;
	const ANIMATE_PACKET = 0x2c;
	const RESPAWN_PACKET = 0x2d;
	const CONTAINER_OPEN_PACKET = 0x2e;
	const CONTAINER_CLOSE_PACKET = 0x2f;
	const PLAYER_HOTBAR_PACKET = 0x30;
	const INVENTORY_CONTENT_PACKET = 0x31;
	const INVENTORY_SLOT_PACKET = 0x32;
	const CONTAINER_SET_DATA_PACKET = 0x33;
	const CRAFTING_DATA_PACKET = 0x34;
	const CRAFTING_EVENT_PACKET = 0x35;
	const GUI_DATA_PICK_ITEM_PACKET = 0x36;
	const ADVENTURE_SETTINGS_PACKET = 0x37;
	const BLOCK_ENTITY_DATA_PACKET = 0x38;
	const PLAYER_INPUT_PACKET = 0x39;
	const FULL_CHUNK_DATA_PACKET = 0x3a;
	const SET_COMMANDS_ENABLED_PACKET = 0x3b;
	const SET_DIFFICULTY_PACKET = 0x3c;
	const CHANGE_DIMENSION_PACKET = 0x3d;
	const SET_PLAYER_GAME_TYPE_PACKET = 0x3e;
	const PLAYER_LIST_PACKET = 0x3f;
	const SIMPLE_EVENT_PACKET = 0x40;
	const EVENT_PACKET = 0x41;
	const SPAWN_EXPERIENCE_ORB_PACKET = 0x42;
	const CLIENTBOUND_MAP_ITEM_DATA_PACKET = 0x43;
	const MAP_INFO_REQUEST_PACKET = 0x44;
	const REQUEST_CHUNK_RADIUS_PACKET = 0x45;
	const CHUNK_RADIUS_UPDATED_PACKET = 0x46;
	const ITEM_FRAME_DROP_ITEM_PACKET = 0x47;
	const GAME_RULES_CHANGED_PACKET = 0x48;
	const CAMERA_PACKET = 0x49;
	const BOSS_EVENT_PACKET = 0x4a;
	const SHOW_CREDITS_PACKET = 0x4b;
	const AVAILABLE_COMMANDS_PACKET = 0x4c;
	const COMMAND_REQUEST_PACKET = 0x4d;
	const COMMAND_BLOCK_UPDATE_PACKET = 0x4e;
	const COMMAND_OUTPUT_PACKET = 0x4f;
	const UPDATE_TRADE_PACKET = 0x50;
	const UPDATE_EQUIP_PACKET = 0x51;
	const RESOURCE_PACK_DATA_INFO_PACKET = 0x52;
	const RESOURCE_PACK_CHUNK_DATA_PACKET = 0x53;
	const RESOURCE_PACK_CHUNK_REQUEST_PACKET = 0x54;
	const TRANSFER_PACKET = 0x55;
	const PLAY_SOUND_PACKET = 0x56;
	const STOP_SOUND_PACKET = 0x57;
	const SET_TITLE_PACKET = 0x58;
	const ADD_BEHAVIOR_TREE_PACKET = 0x59;
	const STRUCTURE_BLOCK_UPDATE_PACKET = 0x5a;
	const SHOW_STORE_OFFER_PACKET = 0x5b;
	const PURCHASE_RECEIPT_PACKET = 0x5c;
	const PLAYER_SKIN_PACKET = 0x5d;
	const SUB_CLIENT_LOGIN_PACKET = 0x5e;
	const W_S_CONNECT_PACKET = 0x5f;
	const SET_LAST_HURT_BY_PACKET = 0x60;
	const BOOK_EDIT_PACKET = 0x61;
	const NPC_REQUEST_PACKET = 0x62;
	const PHOTO_TRANSFER_PACKET = 0x63;
	const MODAL_FORM_REQUEST_PACKET = 0x64;
	const MODAL_FORM_RESPONSE_PACKET = 0x65;
	const SERVER_SETTINGS_REQUEST_PACKET = 0x66;
	const SERVER_SETTINGS_RESPONSE_PACKET = 0x67;
	const SHOW_PROFILE_PACKET = 0x68;
	const SET_DEFAULT_GAME_TYPE_PACKET = 0x69;

}
