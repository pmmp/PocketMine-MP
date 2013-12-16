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

define("RAKNET_STRUCTURE", 5);
define("RAKNET_MAGIC", "\x00\xff\xff\x00\xfe\xfe\xfe\xfe\xfd\xfd\xfd\xfd\x12\x34\x56\x78");

define("RAKNET_UNCONNECTED_PING", 0x01);
define("RAKNET_UNCONNECTED_PING_OPEN_CONNECTIONS", 0x02);

define("RAKNET_OPEN_CONNECTION_REQUEST_1", 0x05);
define("RAKNET_OPEN_CONNECTION_REPLY_1", 0x06);
define("RAKNET_OPEN_CONNECTION_REQUEST_2", 0x07);
define("RAKNET_OPEN_CONNECTION_REPLY_2", 0x08);

define("RAKNET_INCOMPATIBLE_PROTOCOL_VERSION", 0x1a); //CHECK THIS

define("RAKNET_UNCONNECTED_PONG", 0x1c);
define("RAKNET_ADVERTISE_SYSTEM", 0x1c);

define("RAKNET_DATA_PACKET_0", 0x80);
define("RAKNET_DATA_PACKET_1", 0x81);
define("RAKNET_DATA_PACKET_2", 0x82);
define("RAKNET_DATA_PACKET_3", 0x83);
define("RAKNET_DATA_PACKET_4", 0x84);
define("RAKNET_DATA_PACKET_5", 0x85);
define("RAKNET_DATA_PACKET_6", 0x86);
define("RAKNET_DATA_PACKET_7", 0x87);
define("RAKNET_DATA_PACKET_8", 0x88);
define("RAKNET_DATA_PACKET_9", 0x89);
define("RAKNET_DATA_PACKET_A", 0x8a);
define("RAKNET_DATA_PACKET_B", 0x8b);
define("RAKNET_DATA_PACKET_C", 0x8c);
define("RAKNET_DATA_PACKET_D", 0x8d);
define("RAKNET_DATA_PACKET_E", 0x8e);
define("RAKNET_DATA_PACKET_F", 0x8f);

define("RAKNET_NACK", 0xa0);
define("RAKNET_ACK", 0xc0);

class Protocol{	
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