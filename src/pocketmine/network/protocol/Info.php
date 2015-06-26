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
	const CURRENT_PROTOCOL = 28;

	const LOGIN_PACKET = 0x82;
	const PLAY_STATUS_PACKET = 0x83;
	const DISCONNECT_PACKET = 0x84;	
	const BATCH_PACKET = 0x85;
	const TEXT_PACKET = 0x86;
	const SET_TIME_PACKET = 0x87;
	const START_GAME_PACKET = 0x88;
	const ADD_PLAYER_PACKET = 0x89;
	const REMOVE_PLAYER_PACKET = 0x8a;
	const ADD_ENTITY_PACKET = 0x8b;
	const REMOVE_ENTITY_PACKET = 0x8c;
	const ADD_ITEM_ENTITY_PACKET = 0x8d;
	const TAKE_ITEM_ENTITY_PACKET = 0x8e;
	const MOVE_ENTITY_PACKET = 0x8f;
	const MOVE_PLAYER_PACKET = 0x90;
	const REMOVE_BLOCK_PACKET = 0x91;
	const UPDATE_BLOCK_PACKET = 0x92;
	const ADD_PAINTING_PACKET = 0x93;
	const EXPLODE_PACKET = 0x94;
	const LEVEL_EVENT_PACKET = 0x95;
	const TILE_EVENT_PACKET = 0x96;
	const ENTITY_EVENT_PACKET = 0x97;
	const MOB_EFFECT_PACKET = 0x98;
	const UPDATE_ATTRIBUTES_PACKET = 0x99;
	const PLAYER_EQUIPMENT_PACKET = 0x9a;
	const PLAYER_ARMOR_EQUIPMENT_PACKET = 0x9b;
	const INTERACT_PACKET = 0x9c;
	const USE_ITEM_PACKET = 0x9d;
	const PLAYER_ACTION_PACKET = 0x9e;
	const HURT_ARMOR_PACKET = 0x9f;
	const SET_ENTITY_DATA_PACKET = 0xa0;
	const SET_ENTITY_MOTION_PACKET = 0xa1;
	const SET_ENTITY_LINK_PACKET = 0xa2;
	const SET_HEALTH_PACKET = 0xa3;
	const SET_SPAWN_POSITION_PACKET = 0xa4;
	const ANIMATE_PACKET = 0xa5;
	const RESPAWN_PACKET = 0xa6;
	const DROP_ITEM_PACKET = 0xa7;
	const CONTAINER_OPEN_PACKET = 0xa8;
	const CONTAINER_CLOSE_PACKET = 0xa9;
	const CONTAINER_SET_SLOT_PACKET = 0xaa;
	const CONTAINER_SET_DATA_PACKET = 0xab;
	const CONTAINER_SET_CONTENT_PACKET = 0xac;
	const ADVENTURE_SETTINGS_PACKET = 0xad;
	const TILE_ENTITY_DATA_PACKET = 0xae;
	//const PLAYER_INPUT_PACKET = 0xaf;
	const FULL_CHUNK_DATA_PACKET = 0xb0;
	const SET_DIFFICULTY_PACKET = 0xb1;
	//const CHANGE_DIMENSION_PACKET = 0xb2;
	//const SET_PLAYER_GAMETYPE_PACKET = 0xb3;
	//const PLAYER_LIST_PACKET = 0xb4;

}





