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

//Protocol Version: 5


define("MC_KEEP_ALIVE", 0x00);
define("MC_CLIENT_HANDSHAKE", 0x09);
define("MC_SERVER_HANDSHAKE", 0x10);
define("MC_CLIENT_CONNECT", 0x13);
define("MC_CLIENT_DISCONNECT", 0x15);
define("MC_LOGIN", 0x82);
define("MC_LOGIN_STATUS", 0x83);
define("MC_READY", 0x84);
define("MC_CHAT", 0x85);
define("MC_SET_TIME", 0x86);
define("MC_START_GAME", 0x87);