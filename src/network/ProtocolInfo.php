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


define("DEFLATEPACKET_LEVEL", 1);

define("CURRENT_STRUCTURE", 5);
define("CURRENT_PROTOCOL", 12);

define("RAKNET_MAGIC", "\x00\xff\xff\x00\xfe\xfe\xfe\xfe\xfd\xfd\xfd\xfd\x12\x34\x56\x78");

define("MC_PING", 0x00);

define("MC_PONG", 0x03);

define("MC_CLIENT_CONNECT", 0x09);
define("MC_SERVER_HANDSHAKE", 0x10);

define("MC_CLIENT_HANDSHAKE", 0x13);

define("MC_SERVER_FULL", 0x14);
define("MC_DISCONNECT", 0x15);

define("MC_BANNED", 0x17);

define("MC_LOGIN", 0x82);
define("MC_LOGIN_STATUS", 0x83);
define("MC_READY", 0x84);
define("MC_CHAT", 0x85);
define("MC_SET_TIME", 0x86);
define("MC_START_GAME", 0x87);
define("MC_ADD_MOB", 0x88);
define("MC_ADD_PLAYER", 0x89);
define("MC_REMOVE_PLAYER", 0x8a);

define("MC_ADD_ENTITY", 0x8c);
define("MC_REMOVE_ENTITY", 0x8d);
define("MC_ADD_ITEM_ENTITY", 0x8e);
define("MC_TAKE_ITEM_ENTITY", 0x8f);
define("MC_MOVE_ENTITY", 0x90);

define("MC_MOVE_ENTITY_POSROT", 0x93);
define("MC_MOVE_PLAYER", 0x94);
define("MC_PLACE_BLOCK", 0x95);
define("MC_REMOVE_BLOCK", 0x96);
define("MC_UPDATE_BLOCK", 0x97);
define("MC_ADD_PAINTING", 0x98);
define("MC_EXPLOSION", 0x99);
define("MC_LEVEL_EVENT", 0x9a);
define("MC_TILE_EVENT", 0x9b);
define("MC_ENTITY_EVENT", 0x9c);
define("MC_REQUEST_CHUNK", 0x9d);
define("MC_CHUNK_DATA", 0x9e);
define("MC_PLAYER_EQUIPMENT", 0x9f);
define("MC_PLAYER_ARMOR_EQUIPMENT", 0xa0);
define("MC_INTERACT", 0xa1);
define("MC_USE_ITEM", 0xa2);
define("MC_PLAYER_ACTION", 0xa3);

define("MC_HURT_ARMOR", 0xa5);
define("MC_SET_ENTITY_DATA", 0xa6);
define("MC_SET_ENTITY_MOTION", 0xa7);
//define("MC_SET_RIDING_PACKET", 0xa8);
define("MC_SET_HEALTH", 0xa9);
define("MC_SET_SPAWN_POSITION", 0xaa);
define("MC_ANIMATE", 0xab);
define("MC_RESPAWN", 0xac);
define("MC_SEND_INVENTORY", 0xad);
define("MC_DROP_ITEM", 0xae);
define("MC_CONTAINER_OPEN", 0xaf);
define("MC_CONTAINER_CLOSE", 0xb0);
define("MC_CONTAINER_SET_SLOT", 0xb1);
define("MC_CONTAINER_SET_DATA", 0xb2);
define("MC_CONTAINER_SET_CONTENT", 0xb3);
//define("MC_CONTAINER_ACK", 0xb4);
define("MC_CLIENT_MESSAGE", 0xb5);
define("MC_ADVENTURE_SETTINGS", 0xb6);
define("MC_ENTITY_DATA", 0xb7);


class Protocol{
	public static $dataName = array(
		MC_PING => "Ping",

		MC_CLIENT_CONNECT => "Client Connect",
		MC_SERVER_HANDSHAKE => "Server Handshake",

		MC_CLIENT_HANDSHAKE => "Client Handshake",

		//MC_SERVER_FULL => "Server Full",
		MC_DISCONNECT => "Disconnect",

		0x18 => "Unknown",

		MC_LOGIN => "Login",
		MC_LOGIN_STATUS => "Login Status",
		MC_READY => "Ready",
		MC_CHAT => "Chat",
		MC_SET_TIME => "Set Time",
		MC_START_GAME => "Start Game",

		MC_ADD_MOB => "Add Mob",
		MC_ADD_PLAYER => "Add Player",

		MC_ADD_ENTITY => "Add Entity",
		MC_REMOVE_ENTITY => "Remove Entity",
		MC_ADD_ITEM_ENTITY => "Add Item",
		MC_TAKE_ITEM_ENTITY => "Take Item",

		MC_MOVE_ENTITY => "Move Entity",

		MC_MOVE_ENTITY_POSROT => "Move Entity PosRot",
		MC_MOVE_PLAYER => "Move Player",
		MC_PLACE_BLOCK => "Place Block",
		MC_REMOVE_BLOCK => "Remove Block",
		MC_UPDATE_BLOCK => "Update Block",
		MC_ADD_PAINTING => "Add Painting",
		MC_EXPLOSION => "Explosion",
		
		MC_LEVEL_EVENT => "Level Event",

		MC_ENTITY_EVENT => "Entity Event",
		MC_REQUEST_CHUNK => "Chunk Request",
		MC_CHUNK_DATA => "Chunk Data",

		MC_PLAYER_EQUIPMENT => "Player Equipment",
		MC_PLAYER_ARMOR_EQUIPMENT => "Player Armor",
		MC_INTERACT => "Interact",
		MC_USE_ITEM => "Use Item",
		MC_PLAYER_ACTION => "Player Action",
		MC_SET_ENTITY_DATA => "Entity Data",
		MC_SET_ENTITY_MOTION => "Entity Motion",
		MC_HURT_ARMOR => "Hurt Armor",
		MC_SET_HEALTH => "Set Health",
		MC_SET_SPAWN_POSITION => "Set Spawn Position",
		MC_ANIMATE => "Animate",
		MC_RESPAWN => "Respawn",
		MC_SEND_INVENTORY => "Send Inventory",
		MC_DROP_ITEM => "Drop Item",
		MC_CONTAINER_OPEN => "Open Container",
		MC_CONTAINER_CLOSE => "Close Container",
		MC_CONTAINER_SET_SLOT => "Set Container Slot",

		MC_CLIENT_MESSAGE => "Client Message",
		MC_ADVENTURE_SETTINGS => "Adventure Settings",
		MC_ENTITY_DATA => "Entity Data",
	);
	
	public static $packetName = array(
		0x01 => "ID_CONNECTED_PING_OPEN_CONNECTIONS", //RakNet
		0x02 => "ID_UNCONNECTED_PING_OPEN_CONNECTIONS", //RakNet
		0x05 => "ID_OPEN_CONNECTION_REQUEST_1", //RakNet
		0x06 => "ID_OPEN_CONNECTION_REPLY_1", //RakNet
		0x07 => "ID_OPEN_CONNECTION_REQUEST_2", //RakNet
		0x08 => "ID_OPEN_CONNECTION_REPLY_2", //RakNet
		0x1a => "ID_INCOMPATIBLE_PROTOCOL_VERSION", //RakNet
		0x1c => "ID_UNCONNECTED_PONG", //RakNet
		0x1d => "ID_ADVERTISE_SYSTEM", //RakNet
		0x80 => "Custom Packet", //Minecraft Implementation
		0x84 => "Custom Packet", //Minecraft Implementation
		0x88 => "Custom Packet", //Minecraft Implementation
		0x8c => "Custom Packet", //Minecraft Implementation
		0xa0 => "NACK", //Minecraft Implementation
		0xc0 => "ACK", //Minecraft Implementation
	);
	
	public static $raknet = array(
		0x01 => array(
			"long", //Ping ID
			"magic",
		),
		0x02 => array(
			"long", //Ping ID
			"magic",
		),

		0x05 => array(
			"magic",
			"byte", //Protocol Version
			"special1", //MTU Size Null Lenght
		),

		0x06 => array(
			"magic",
			"long", //Server GUID
			"byte", //Server Security
			"short", //MTU Size
		),

		0x07 => array(
			"magic",
			"special1", //Security Cookie
			"short", //Server UDP Port
			"short", //MTU Size
			"long", //Client GUID
		),

		0x08 => array(
			"magic",
			"long", //Server GUID
			"short", //Client UDP Port
			"short", //MTU Size
			"byte", //Security
		),

		0x1a => array(
			"byte", //Server Version
			"magic",
			"long", //Server GUID
		),

		0x1c => array(
			"long", //Ping ID
			"long", //Server GUID
			"magic",
			"string", //Data
		),

		0x1d => array(
			"long", //Ping ID
			"long", //Server GUID
			"magic",
			"string", //Data
		),

		0x80 => array(
			"itriad",
			"customData",
		),


		0x81 => array(
			"itriad",
			"customData",
		),

		0x82 => array(
			"itriad",
			"customData",
		),

		0x83 => array(
			"itriad",
			"customData",
		),

		0x84 => array(
			"itriad",
			"customData",
		),

		0x85 => array(
			"itriad",
			"customData",
		),

		0x86 => array(
			"itriad",
			"customData",
		),

		0x87 => array(
			"itriad",
			"customData",
		),

		0x88 => array(
			"itriad",
			"customData",
		),

		0x89 => array(
			"itriad",
			"customData",
		),

		0x8a => array(
			"itriad",
			"customData",
		),

		0x8b => array(
			"itriad",
			"customData",
		),

		0x8c => array(
			"itriad",
			"customData",
		),

		0x8d => array(
			"itriad",
			"customData",
		),

		0x8e => array(
			"itriad",
			"customData",
		),

		0x8f => array(
			"itriad",
			"ubyte",
			"customData",
		),
		
		0x99 => array(
			"byte",
			"special1",
		),

		0xa0 => array(
			"special1",
		),

		0xc0 => array(
			"special1",
		),

	);
	
}