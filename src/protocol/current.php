<?php

/*

           -
         /   \
      /         \
   /   PocketMine  \
/          MP         \
|\     @shoghicp     /|
|.   \           /   .|
| ..     \   /     .. |
|    ..    |    ..    |
|       .. | ..       |
\          |          /
   \       |       /
      \    |    /
         \ | /

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Lesser General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.


*/


define("MC_KEEP_ALIVE", 0x00);

define("MC_CLIENT_CONNECT", 0x09);
define("MC_SERVER_HANDSHAKE", 0x10);

define("MC_CLIENT_HANDSHAKE", 0x13);

define("MC_DISCONNECT", 0x15);

define("MC_LOGIN", 0x82);
define("MC_LOGIN_STATUS", 0x83);
define("MC_READY", 0x84);
define("MC_CHAT", 0x85);
define("MC_SET_TIME", 0x86);
define("MC_START_GAME", 0x87);

define("MC_ADD_MOB", 0x88);
define("MC_ADD_PLAYER", 0x89);

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

define("MC_EXPLOSION", 0x99);

define("MC_LEVEL_EVENT", 0x9a);

define("MC_ENTITY_EVENT", 0x9c);
define("MC_REQUEST_CHUNK", 0x9d);
define("MC_CHUNK_DATA", 0x9e);

define("MC_PLAYER_EQUIPMENT", 0x9f);

define("MC_INTERACT", 0xa0);
define("MC_USE_ITEM", 0xa1);
define("MC_PLAYER_ACTION", 0xa2);
define("MC_SET_ENTITY_DATA", 0xa3);
define("MC_SET_ENTITY_MOTION", 0xa4);
define("MC_SET_HEALTH", 0xa5);
define("MC_SET_SPAWN_POSITION", 0xa6);
define("MC_ANIMATE", 0xa7);
define("MC_RESPAWN", 0xa8);

define("MC_DROP_ITEM", 0xaa);
define("MC_CONTAINER_OPEN", 0xab);
define("MC_CONTAINER_CLOSE", 0xac);
define("MC_CONTAINER_SET_SLOT", 0xad);

define("MC_CLIENT_MESSAGE", 0xb1);
define("MC_SIGN_UPDATE", 0xb2);
define("MC_ADVENTURE_SETTINGS", 0xb3);