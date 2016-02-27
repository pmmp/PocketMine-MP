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

$server = proc_open(PHP_BINARY . " src/pocketmine/PocketMine.php --no-wizard --disable-readline", [
	0 => ["pipe", "r"],
	1 => ["pipe", "w"],
	2 => ["pipe", "w"]
], $pipes);

if(!is_resource($server)){
	die('Failed to create process');
}

fwrite($pipes[0], "version\nmakeserver\nstop\n\n");
fclose($pipes[0]);

while(!feof($pipes[1])){
	echo fgets($pipes[1]);
}

fclose($pipes[1]);
fclose($pipes[2]);

echo "\n\nReturn value: ". proc_close($server) ."\n";

if(count(glob("plugins/DevTools/PocketMine-MP*.phar")) === 0){
	echo "No server Phar created!\n";
	exit(1);
}else{
	exit(0);
}
