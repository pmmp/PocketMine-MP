<?php

/*

           -
         /   \
      /         \
   /    POCKET     \
/    MINECRAFT PHP    \
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

$dataName = array(
	MC_KEEP_ALIVE => "KeepAlive",
	
	MC_CLIENT_HANDSHAKE => "ClientHandshake",
	MC_SERVER_HANDSHAKE => "ServerHandshake",
	
	MC_CLIENT_CONNECT => "ClientConnect",
	
	MC_CLIENT_DISCONNECT => "ClientDisconnect",
	
	0x18 => "Unknown",
	
	MC_LOGIN => "Login",
	MC_LOGIN_STATUS => "LoginStatus",
	MC_READY => "Ready",
	MC_CHAT => "Chat",
	MC_SET_TIME => "SetTime",
	MC_START_GAME => "StartGame",
	
	0x93 => "MoveEntity_PosRot",
	0x94 => "MovePlayer",
	
	0x96 => "RemoveBlock",
	
	0x9d => "RequestChunk",
	
	0x9f => "PlayerEquipment",
	
	0xa1 => "UseItem",
	
	0xa4 => "SetEntityMotion",
	0xa5 => "SetHealth",
	
	0xa7 => "Animate",
	
	0xb1 => "ClientMessage"
);