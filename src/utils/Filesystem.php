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

namespace pocketmine\utils;

use function fclose;
use function fflush;
use function flock;
use function fopen;
use function ftruncate;
use function fwrite;
use function getmypid;
use function is_dir;
use function is_file;
use function ltrim;
use function preg_match;
use function realpath;
use function rmdir;
use function rtrim;
use function scandir;
use function str_replace;
use function stream_get_contents;
use function strpos;
use function unlink;
use const LOCK_EX;
use const LOCK_NB;
use const LOCK_SH;
use const SCANDIR_SORT_NONE;

final class Filesystem{
	/** @var resource[] */
	private static $lockFileHandles = [];

	private function __construct(){
		//NOOP
	}

	public static function recursiveUnlink(string $dir) : void{
		if(is_dir($dir)){
			$objects = scandir($dir, SCANDIR_SORT_NONE);
			foreach($objects as $object){
				if($object !== "." and $object !== ".."){
					if(is_dir($dir . "/" . $object)){
						self::recursiveUnlink($dir . "/" . $object);
					}else{
						unlink($dir . "/" . $object);
					}
				}
			}
			rmdir($dir);
		}elseif(is_file($dir)){
			unlink($dir);
		}
	}

	public static function cleanPath($path){
		$result = str_replace(["\\", ".php", "phar://"], ["/", "", ""], $path);

		//remove relative paths
		//TODO: make these paths dynamic so they can be unit-tested against
		static $cleanPaths = [
			\pocketmine\PLUGIN_PATH => "plugins", //this has to come BEFORE \pocketmine\PATH because it's inside that by default on src installations
			\pocketmine\PATH => ""
		];
		foreach($cleanPaths as $cleanPath => $replacement){
			$cleanPath = rtrim(str_replace(["\\", "phar://"], ["/", ""], $cleanPath), "/");
			if(strpos($result, $cleanPath) === 0){
				$result = ltrim(str_replace($cleanPath, $replacement, $result), "/");
			}
		}
		return $result;
	}

	/**
	 * Attempts to get a lock on the specified file, creating it if it does not exist. This is typically used for IPC to
	 * inform other processes that some file or folder is already in use, to avoid data corruption.
	 * If this function succeeds in gaining a lock on the file, it writes the current PID to the file.
	 *
	 * @param string $lockFilePath
	 *
	 * @return int|null process ID of the process currently holding the lock failure, null on success.
	 * @throws \InvalidArgumentException if the lock file path is invalid (e.g. parent directory doesn't exist, permission denied)
	 */
	public static function createLockFile(string $lockFilePath) : ?int{
		$resource = fopen($lockFilePath, "a+b");
		if($resource === false){
			throw new \InvalidArgumentException("Invalid lock file path");
		}
		if(!flock($resource, LOCK_EX | LOCK_NB)){
			//wait for a shared lock to avoid race conditions if two servers started at the same time - this makes sure the
			//other server wrote its PID and released exclusive lock before we get our lock
			flock($resource, LOCK_SH);
			$pid = stream_get_contents($resource);
			if(preg_match('/^\d+$/', $pid) === 1){
				return (int) $pid;
			}
			return -1;
		}
		ftruncate($resource, 0);
		fwrite($resource, (string) getmypid());
		fflush($resource);
		flock($resource, LOCK_SH); //prevent acquiring an exclusive lock from another process, but allow reading
		self::$lockFileHandles[realpath($lockFilePath)] = $resource; //keep the resource alive to preserve the lock
		return null;
	}

	/**
	 * Releases a file lock previously acquired by createLockFile() and deletes the lock file.
	 *
	 * @param string $lockFilePath
	 * @throws \InvalidArgumentException if the lock file path is invalid (e.g. parent directory doesn't exist, permission denied)
	 */
	public static function releaseLockFile(string $lockFilePath) : void{
		$lockFilePath = realpath($lockFilePath);
		if($lockFilePath === false){
			throw new \InvalidArgumentException("Invalid lock file path");
		}
		if(isset(self::$lockFileHandles[$lockFilePath])){
			fclose(self::$lockFileHandles[$lockFilePath]);
			unset(self::$lockFileHandles[$lockFilePath]);
			@unlink($lockFilePath);
		}
	}
}
