<?php

/**
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


class ProtocolInfo{

	const CURRENT_PROTOCOL = 14;
	
	

	const PING_PACKET = 0x00;

	const PONG_PACKET = 0x03;

	const CLIENT_CONNECT_PACKET = 0x09;
	const SERVER_HANDSHAKE_PACKET = 0x10;

	const CLIENT_HANDSHAKE_PACKET = 0x13;
	//const SERVER_FULL_PACKET = 0x14;
	const DISCONNECT_PACKET = 0x15;

	//const BANNED_PACKET = 0x17;


	const LOGIN_PACKET = 0x82;
	const LOGIN_STATUS_PACKET = 0x83;
	const READY_PACKET = 0x84;
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

	const MOVE_ENTITY_PACKET_POSROT = 0x93;
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
	const REQUEST_CHUNK_PACKET = 0x9e;
	const CHUNK_DATA_PACKET = 0x9f;
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
	
	public static $packets = array(
		-1 => "UnknownPacket",
		ProtocolInfo::PING_PACKET => "PingPacket",
		ProtocolInfo::PONG_PACKET => "PongPacket",
		ProtocolInfo::CLIENT_CONNECT_PACKET => "ClientConnectPacket",
		ProtocolInfo::SERVER_HANDSHAKE_PACKET => "ServerHandshakePacket",
		ProtocolInfo::DISCONNECT_PACKET => "DisconnectPacket",
		ProtocolInfo::LOGIN_PACKET => "LoginPacket",
		ProtocolInfo::LOGIN_STATUS_PACKET => "LoginStatusPacket",
		ProtocolInfo::READY_PACKET => "ReadyPacket",
		ProtocolInfo::MESSAGE_PACKET => "MessagePacket",
		ProtocolInfo::SET_TIME_PACKET => "SetTimePacket",
		ProtocolInfo::START_GAME_PACKET => "StartGamePacket",
		ProtocolInfo::ADD_MOB_PACKET => "AddMobPacket",
		ProtocolInfo::ADD_PLAYER_PACKET => "AddPlayerPacket",
		ProtocolInfo::REMOVE_PLAYER_PACKET => "RemovePlayerPacket",
		ProtocolInfo::ADD_ENTITY_PACKET => "AddEntityPacket",
		ProtocolInfo::REMOVE_ENTITY_PACKET => "RemoveEntityPacket",
		ProtocolInfo::ADD_ITEM_ENTITY_PACKET => "AddItemEntityPacket",
		ProtocolInfo::TAKE_ITEM_ENTITY_PACKET => "TakeItemEntityPacket",
		ProtocolInfo::MOVE_ENTITY_PACKET => "MoveEntityPacket",
		ProtocolInfo::MOVE_ENTITY_PACKET_POSROT => "MoveEntityPacket_PosRot",
		ProtocolInfo::ROTATE_HEAD_PACKET => "RotateHeadPacket",
		ProtocolInfo::MOVE_PLAYER_PACKET => "MovePlayerPacket",
		ProtocolInfo::REMOVE_BLOCK_PACKET => "RemoveBlockPacket",
		ProtocolInfo::UPDATE_BLOCK_PACKET => "UpdateBlockPacket",
		ProtocolInfo::ADD_PAINTING_PACKET => "AddPaintingPacket",
		ProtocolInfo::EXPLODE_PACKET => "ExplodePacket",
		ProtocolInfo::LEVEL_EVENT_PACKET => "LevelEventPacket",
		ProtocolInfo::TILE_EVENT_PACKET => "TileEventPacket",
		ProtocolInfo::ENTITY_EVENT_PACKET => "EntityEventPacket",
		ProtocolInfo::REQUEST_CHUNK_PACKET => "RequestChunkPacket",
		ProtocolInfo::CHUNK_DATA_PACKET => "ChunkDataPacket",
		ProtocolInfo::PLAYER_EQUIPMENT_PACKET => "PlayerEquipmentPacket",
		ProtocolInfo::PLAYER_ARMOR_EQUIPMENT_PACKET => "PlayerArmorEquipmentPacket",
		ProtocolInfo::INTERACT_PACKET => "InteractPacket",
		ProtocolInfo::USE_ITEM_PACKET => "UseItemPacket",
		ProtocolInfo::PLAYER_ACTION_PACKET => "PlayerActionPacket",
		ProtocolInfo::HURT_ARMOR_PACKET => "HurtArmorPacket",
		ProtocolInfo::SET_ENTITY_DATA_PACKET => "SetEntityDataPacket",
		ProtocolInfo::SET_ENTITY_MOTION_PACKET => "SetEntityMotionPacket",
		ProtocolInfo::SET_HEALTH_PACKET => "SetHealthPacket",
		ProtocolInfo::SET_SPAWN_POSITION_PACKET => "SetSpawnPositionPacket",
		ProtocolInfo::ANIMATE_PACKET => "AnimatePacket",
		ProtocolInfo::RESPAWN_PACKET => "RespawnPacket",
		ProtocolInfo::SEND_INVENTORY_PACKET => "SendInventoryPacket",
		ProtocolInfo::DROP_ITEM_PACKET => "DropItemPacket",
		ProtocolInfo::CONTAINER_OPEN_PACKET => "ContainerOpenPacket",
		ProtocolInfo::CONTAINER_CLOSE_PACKET => "ContainerClosePacket",
		ProtocolInfo::CONTAINER_SET_SLOT_PACKET => "ContainerSetSlotPacket",
		ProtocolInfo::CONTAINER_SET_DATA_PACKET => "ContainerSetDataPacket",
		ProtocolInfo::CONTAINER_SET_CONTENT_PACKET => "ContainerSetContentPacket",
		ProtocolInfo::CHAT_PACKET => "ChatPacket",
		ProtocolInfo::ADVENTURE_SETTINGS_PACKET => "AdventureSettingsPacket",
		ProtocolInfo::ENTITY_DATA_PACKET => "EntityDataPacket",
	);

}

/***REM_START***/
require_once(FILE_PATH . "src/network/raknet/RakNetDataPacket.php");
/***REM_END***/