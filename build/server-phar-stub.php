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

namespace pocketmine\server_phar_stub;

use function clearstatcache;
use function copy;
use function define;
use function fclose;
use function fflush;
use function flock;
use function fopen;
use function fwrite;
use function getmypid;
use function hrtime;
use function is_dir;
use function is_file;
use function mkdir;
use function number_format;
use function str_replace;
use function stream_get_contents;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;
use const DIRECTORY_SEPARATOR;
use const LOCK_EX;
use const LOCK_NB;
use const LOCK_UN;

/**
 * Finds the appropriate tmp directory to store the decompressed phar cache, accounting for potential file name
 * collisions.
 */
function preparePharCacheDirectory() : string{
	clearstatcache();

	$i = 0;
	do{
		$tmpPath = sys_get_temp_dir() . '/PocketMine-MP-phar-cache.' . $i;
		$i++;
	}while(is_file($tmpPath));
	if(!@mkdir($tmpPath) && !is_dir($tmpPath)){
		throw new \RuntimeException("Failed to create temporary directory $tmpPath. Please ensure the disk has enough space and that the current user has permission to write to this location.");
	}

	return $tmpPath;
}

/**
 * Deletes caches left behind by previous server instances.
 * This ensures that the tmp directory doesn't get flooded by servers crashing in restart loops.
 */
function cleanupPharCache(string $tmpPath) : void{
	clearstatcache();

	/** @var string[] $matches */
	foreach(new \RegexIterator(
		new \FilesystemIterator(
			$tmpPath,
			\FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS
		),
		'/(.+)\.lock$/',
		\RegexIterator::GET_MATCH
	) as $matches){
		$lockFilePath = $matches[0];
		$baseTmpPath = $matches[1];

		$file = @fopen($lockFilePath, "rb");
		if($file === false){
			//another process probably deleted the lock file already
			continue;
		}

		if(flock($file, LOCK_EX | LOCK_NB)){
			//this tmpfile is no longer in use
			flock($file, LOCK_UN);
			fclose($file);

			unlink($lockFilePath);
			unlink($baseTmpPath . ".tar");
			unlink($baseTmpPath);
			echo "Deleted stale phar cache at $baseTmpPath\n";
		}else{
			$pid = stream_get_contents($file);
			fclose($file);

			echo "Phar cache at $baseTmpPath is still in use by PID $pid\n";
		}
	}
}

function convertPharToTar(string $tmpName, string $pharPath) : string{
	$tmpPharPath = $tmpName . ".phar";
	copy($pharPath, $tmpPharPath);

	$phar = new \Phar($tmpPharPath);
	//phar requires phar.readonly=0, and zip doesn't support disabling compression - tar is the only viable option
	//we don't need phar anyway since we don't need to directly execute the file, only require files from inside it
	$phar->convertToData(\Phar::TAR, \Phar::NONE);
	unset($phar);
	\Phar::unlinkArchive($tmpPharPath);

	return $tmpName . ".tar";
}

/**
 * Locks a phar tmp cache to prevent it from being deleted by other server instances.
 * This code looks similar to Filesystem::createLockFile(), but we can't use that because it's inside the compressed
 * phar.
 */
function lockPharCache(string $lockFilePath) : void{
	//this static variable will keep the file(s) locked until the process ends
	static $lockFiles = [];

	$lockFile = fopen($lockFilePath, "wb");
	if($lockFile === false){
		throw new \RuntimeException("Failed to open temporary file");
	}
	flock($lockFile, LOCK_EX); //this tells other server instances not to delete this cache file
	fwrite($lockFile, (string) getmypid()); //maybe useful for debugging
	fflush($lockFile);
	$lockFiles[$lockFilePath] = $lockFile;
}

/**
 * Prepares a decompressed .tar of PocketMine-MP.phar in the system temp directory for loading code from.
 *
 * @return string path to the temporary decompressed phar (actually a .tar)
 */
function preparePharCache(string $tmpPath, string $pharPath) : string{
	clearstatcache();

	$tmpName = tempnam($tmpPath, "PMMP");
	if($tmpName === false){
		throw new \RuntimeException("Failed to create temporary file");
	}

	lockPharCache($tmpName . ".lock");
	return convertPharToTar($tmpName, $pharPath);
}

$tmpDir = preparePharCacheDirectory();
cleanupPharCache($tmpDir);
echo "Preparing PocketMine-MP.phar decompressed cache...\n";
$start = hrtime(true);
$cacheName = preparePharCache($tmpDir, __FILE__);
echo "Cache ready at $cacheName in " . number_format((hrtime(true) - $start) / 1e9, 2) . "s\n";

define('pocketmine\ORIGINAL_PHAR_PATH', __FILE__);
require 'phar://' . str_replace(DIRECTORY_SEPARATOR, '/', $cacheName) . '/src/PocketMine.php';
