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

use function is_dir;
use function is_file;
use function ltrim;
use function rmdir;
use function rtrim;
use function scandir;
use function str_replace;
use function strpos;
use function unlink;
use const SCANDIR_SORT_NONE;

final class Filesystem{

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
}
