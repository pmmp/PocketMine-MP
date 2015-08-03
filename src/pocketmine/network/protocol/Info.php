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
	const CURRENT_PROTOCOL = 30;

	const LOGIN_PACKET = 0x87;
	const PLAY_STATUS_PACKET = 0x88;
	const DISCONNECT_PACKET = 0x89;
	const BATCH_PACKET = 0x8a;
	const TEXT_PACKET = 0x8b;
	const SET_TIME_PACKET = 0x8c;
	const START_GAME_PACKET = 0x8d;
	const ADD_PLAYER_PACKET = 0x8e;
	const REMOVE_PLAYER_PACKET = 0x8f;
	const ADD_ENTITY_PACKET = 0x90;
	const REMOVE_ENTITY_PACKET = 0x91;
	const ADD_ITEM_ENTITY_PACKET = 0x92;
	const TAKE_ITEM_ENTITY_PACKET = 0x93;
	const MOVE_ENTITY_PACKET = 0x94;
	const MOVE_PLAYER_PACKET = 0x95;
	const REMOVE_BLOCK_PACKET = 0x96;
	const UPDATE_BLOCK_PACKET = 0x97;
	const ADD_PAINTING_PACKET = 0x98;
	const EXPLODE_PACKET = 0x99;
	const LEVEL_EVENT_PACKET = 0x9a;
	const TILE_EVENT_PACKET = 0x9b;
	const ENTITY_EVENT_PACKET = 0x9c;
	const MOB_EFFECT_PACKET = 0x9d;
	const UPDATE_ATTRIBUTES_PACKET = 0x9e;
	const MOB_EQUIPMENT_PACKET = 0x9f;
	const MOB_ARMOR_EQUIPMENT_PACKET = 0xa0;
	const INTERACT_PACKET = 0xa1;
	const USE_ITEM_PACKET = 0xa2;
	const PLAYER_ACTION_PACKET = 0xa3;
	const HURT_ARMOR_PACKET = 0xa4;
	const SET_ENTITY_DATA_PACKET = 0xa5;
	const SET_ENTITY_MOTION_PACKET = 0xa6;
	const SET_ENTITY_LINK_PACKET = 0xa7;
	const SET_HEALTH_PACKET = 0xa8;
	const SET_SPAWN_POSITION_PACKET = 0xa9;
	const ANIMATE_PACKET = 0xaa;
	const RESPAWN_PACKET = 0xab;
	const DROP_ITEM_PACKET = 0xac;
	const CONTAINER_OPEN_PACKET = 0xad;
	const CONTAINER_CLOSE_PACKET = 0xae;
	const CONTAINER_SET_SLOT_PACKET = 0xaf;
	const CONTAINER_SET_DATA_PACKET = 0xb0;
	const CONTAINER_SET_CONTENT_PACKET = 0xb1;
	const CRAFTING_DATA_PACKET = 0xb2;
	const CRAFTING_EVENT_PACKET = 0xb3;
	const ADVENTURE_SETTINGS_PACKET = 0xb4;
	const TILE_ENTITY_DATA_PACKET = 0xb5;
	//const PLAYER_INPUT_PACKET = 0xb6;
	const FULL_CHUNK_DATA_PACKET = 0xb7;
	const SET_DIFFICULTY_PACKET = 0xb8;
	//const CHANGE_DIMENSION_PACKET = 0xb9;
	//const SET_PLAYER_GAMETYPE_PACKET = 0xba;
	const PLAYER_LIST_PACKET = 0xbb;
	//const TELEMETRY_EVENT_PACKET = 0xbc;

}










