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

declare(strict_types=1);

namespace pocketmine\console;

use function cli_set_process_title;
use function count;
use function dirname;
use function fwrite;
use function stream_socket_client;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

if(count($argv) !== 2){
	die("Please provide a server to connect to");
}

@cli_set_process_title('PocketMine-MP Console Reader');
$errCode = null;
$errMessage = null;
$socket = stream_socket_client($argv[1], $errCode, $errMessage, 15.0);
if($socket === false){
	throw new \RuntimeException("Failed to connect to server process ($errCode): $errMessage");
}
$consoleReader = new ConsoleReader();
while(!feof($socket)){
	$line = $consoleReader->readLine();
	if($line !== null){
		fwrite($socket, $line . "\n");
	}
}
