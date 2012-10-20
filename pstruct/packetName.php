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

$packetName = array(
	0x02 => "ID_UNCONNECTED_PING_OPEN_CONNECTIONS", //RakNet
	0x05 => "ID_OPEN_CONNECTION_REQUEST_1", //RakNet
	0x06 => "ID_OPEN_CONNECTION_REPLY_1", //RakNet
	0x07 => "ID_OPEN_CONNECTION_REQUEST_2", //RakNet
	0x08 => "ID_OPEN_CONNECTION_REPLY_2", //RakNet
	0x1a => "ID_INCOMPATIBLE_PROTOCOL_VERSION", //RakNet
	0x1c => "ID_UNCONNECTED_PONG", //RakNet
	0x1d => "ID_ADVERTISE_SYSTEM", //RakNet
	0x84 => "ID_RESERVED_7", //Minecraft Implementation
	0xc0 => "Unknown", //Minecraft Implementation
);