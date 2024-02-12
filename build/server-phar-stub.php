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

/**
 * Prepares a decompressed .tar of PocketMine-MP.phar in the system temp directory for loading code from.
 *
 * @return string[] path to the temporary decompressed phar (actually a .tar) + path to tmpfile used to reserve the name
 */
function preparePharCache() : array{
	$tmp = tempnam(sys_get_temp_dir(), "PMMP");
	if($tmp === false){
		throw new RuntimeException("Failed to create temporary file");
	}

	$tmpPharPath = $tmp . ".phar";
	copy(__FILE__, $tmpPharPath);

	$phar = new \Phar($tmpPharPath);
	//phar requires phar.readonly=0, and zip doesn't support disabling compression - tar is the only viable option
	//we don't need phar anyway since we don't need to directly execute the file, only require files from inside it
	$phar->convertToData(\Phar::TAR, \Phar::NONE);
	unset($phar);
	\Phar::unlinkArchive($tmpPharPath);

	return [$tmp . '.tar', $tmp];
}

echo "Preparing PocketMine-MP.phar decompressed cache...\n";
$start = hrtime(true);
[$cacheName, $tmpReserveName] = preparePharCache();
echo "Cache ready at $cacheName in " . number_format((hrtime(true) - $start) / 1e9, 2) . "s\n";
register_shutdown_function(static function() use ($cacheName, $tmpReserveName) : void{
	if(is_file($cacheName)){
		\Phar::unlinkArchive($cacheName);
		unlink($tmpReserveName);
	}
});

require 'phar://' . str_replace(DIRECTORY_SEPARATOR, '/', $cacheName) . '/src/PocketMine.php';
