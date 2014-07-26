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

/**
 * Minecraft: PE multiplayer protocol implementation
 */
namespace pocketmine\network\protocol;


interface Info{

	/**
	 * Actual Minecraft: PE protocol version
	 */
	const CURRENT_PROTOCOL = 18;


	const LOGIN_PACKET = 0x82;
	const LOGIN_STATUS_PACKET = 0x83;

	const MESSAGE_PACKET = 0x85;
	const SET_TIME_PACKET = 0x86;
	const START_GAME_PACKET = 0x87;
	const ADD_MOB_PACKET = 0x88;
	const ADD_PLAYER_PACKET = 0x89;
	const REMOVE_PLAYER_PACKET = 0x8a;

	const ADD_ENTITY_PACKET = 0x8c;
	const REMOVE_ENTITY_PACKET = 0x8d;
	const ADD_ITEM_ENTITY_PACKET = 0x8e;
	const TAKE_ITEM_ENTITY_PACKET = 0x8f;
	const MOVE_ENTITY_PACKET = 0x90;

	const ROTATE_HEAD_PACKET = 0x94;
	const MOVE_PLAYER_PACKET = 0x95;
	//const PLACE_BLOCK_PACKET = 0x96;
	const REMOVE_BLOCK_PACKET = 0x97;
	const UPDATE_BLOCK_PACKET = 0x98;
	const ADD_PAINTING_PACKET = 0x99;
	const EXPLODE_PACKET = 0x9a;
	const LEVEL_EVENT_PACKET = 0x9b;
	const TILE_EVENT_PACKET = 0x9c;
	const ENTITY_EVENT_PACKET = 0x9d;

	const PLAYER_EQUIPMENT_PACKET = 0xa0;
	const PLAYER_ARMOR_EQUIPMENT_PACKET = 0xa1;
	const INTERACT_PACKET = 0xa2;
	const USE_ITEM_PACKET = 0xa3;
	const PLAYER_ACTION_PACKET = 0xa4;

	const HURT_ARMOR_PACKET = 0xa6;
	const SET_ENTITY_DATA_PACKET = 0xa7;
	const SET_ENTITY_MOTION_PACKET = 0xa8;
	//const SET_ENTITY_LINK_PACKET = 0xa9;
	const SET_HEALTH_PACKET = 0xaa;
	const SET_SPAWN_POSITION_PACKET = 0xab;
	const ANIMATE_PACKET = 0xac;
	const RESPAWN_PACKET = 0xad;
	const SEND_INVENTORY_PACKET = 0xae;
	const DROP_ITEM_PACKET = 0xaf;
	const CONTAINER_OPEN_PACKET = 0xb0;
	const CONTAINER_CLOSE_PACKET = 0xb1;
	const CONTAINER_SET_SLOT_PACKET = 0xb2;
	const CONTAINER_SET_DATA_PACKET = 0xb3;
	const CONTAINER_SET_CONTENT_PACKET = 0xb4;
	//const CONTAINER_ACK_PACKET = 0xb5;
	const CHAT_PACKET = 0xb6;
	const ADVENTURE_SETTINGS_PACKET = 0xb7;
	const ENTITY_DATA_PACKET = 0xb8;
	//const PLAYER_INPUT_PACKET = 0xb9;
	const FULL_CHUNK_DATA_PACKET = 0xba;
	const UNLOAD_CHUNK_PACKET = 0xbb;

}