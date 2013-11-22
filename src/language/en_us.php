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

//Command Language
const CMD_BANIP_DESC = "<add|remove|list|reload> [IP|player]";
const CMD_BAN_DESC = "<add|remove|list|reload> [username]";
const CMD_KICK_DESC = "<player> [reason ...]";
const CMD_WHITELIST_DESC = "<on|off|list|add|remove|reload> [username]";
const CMD_OP_DESC = "<player>";
const CMD_DEOP_DESC = "<player>";
const CMD_SUDO_DESC = "<player>";

//Ban API Language
const API_BAN_PLAYER_NOT_CONNECTED = "Player not connected.";
const API_BAN_CMD_RUN_AS = "Command ran as {player}";
const API_BAN_NOW_OP = "{player} is now op";
const API_BAN_YOU_NOW_OP = "you are now op.";
const API_BAN_NOW_DEOP = "{player} is no longer op";

?>