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

namespace pocketmine;

use pocketmine\utils\Git;
use pocketmine\utils\VersionString;
use function is_array;
use function is_int;
use function str_repeat;

final class VersionInfo{
	public const NAME = "PocketMine-MP";
	public const BASE_VERSION = "4.12.3";
	public const IS_DEVELOPMENT_BUILD = true;
	public const BUILD_CHANNEL = "stable";

	private function __construct(){
		//NOOP
	}

	private static ?string $gitHash = null;

	public static function GIT_HASH() : string{
		if(self::$gitHash === null){
			$gitHash = str_repeat("00", 20);

			if(\Phar::running(true) === ""){
				$gitHash = Git::getRepositoryStatePretty(\pocketmine\PATH);
			}else{
				$phar = new \Phar(\Phar::running(false));
				$meta = $phar->getMetadata();
				if(isset($meta["git"])){
					$gitHash = $meta["git"];
				}
			}

			self::$gitHash = $gitHash;
		}

		return self::$gitHash;
	}

	private static ?int $buildNumber = null;

	public static function BUILD_NUMBER() : int{
		if(self::$buildNumber === null){
			self::$buildNumber = 0;
			if(\Phar::running(true) !== ""){
				$phar = new \Phar(\Phar::running(false));
				$meta = $phar->getMetadata();
				if(is_array($meta) && isset($meta["build"]) && is_int($meta["build"])){
					self::$buildNumber = $meta["build"];
				}
			}
		}

		return self::$buildNumber;
	}

	private static ?VersionString $fullVersion = null;

	public static function VERSION() : VersionString{
		if(self::$fullVersion === null){
			self::$fullVersion = new VersionString(self::BASE_VERSION, self::IS_DEVELOPMENT_BUILD, self::BUILD_NUMBER());
		}
		return self::$fullVersion;
	}
}
