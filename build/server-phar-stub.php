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
 * Dependency-free version of Filesystem::recursiveUnlink().
 * We can't access that from here because it's in the phar, which we've yet to extract.
 */
function recursiveUnlink(string $dir) : void{
	if(is_dir($dir)){
		$objects = scandir($dir, SCANDIR_SORT_NONE);
		if($objects === false){
			throw new RuntimeException("Failed to scan directory \"$dir\"");
		}
		foreach($objects as $object){
			if($object !== "." && $object !== ".."){
				recursiveUnlink($dir . DIRECTORY_SEPARATOR . $object);
			}
		}
		rmdir($dir);
	}elseif(is_file($dir)){
		unlink($dir);
	}
}

function prepareSourceCache(string $sourceDir) : void{
	$expectedHash = hash_file("sha256", __FILE__);

	$prepare = true;
	if(file_exists($sourceDir)){
		$cacheHash = @file_get_contents($sourceDir . DIRECTORY_SEPARATOR . "phar-hash.txt");
		if($cacheHash === $expectedHash){
			echo "Using existing source cache" . PHP_EOL;
			$prepare = false;
		}else{
			echo "Removing old source cache..." . PHP_EOL;
			recursiveUnlink($sourceDir);
			echo "Done!" . PHP_EOL;
		}
	}

	if($prepare){
		$phar = new \Phar(__FILE__);
		echo "Preparing source cache..." . PHP_EOL;
		$phar->extractTo($sourceDir);
		file_put_contents($sourceDir . DIRECTORY_SEPARATOR . "phar-hash.txt", $expectedHash);
		echo "Done!" . PHP_EOL;
	}
}

$sourceDir = getcwd() . "/.PocketMine-MP.phar/";
prepareSourceCache($sourceDir);

require $sourceDir . '/src/PocketMine.php';
