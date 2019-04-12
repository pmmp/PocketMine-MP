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

namespace pocketmine\plugin;

use pocketmine\utils\VersionString;

final class ApiVersion{

	private function __construct(){
		//NOOP
	}

	/**
	 * @param string   $myVersionStr
	 * @param string[] $wantVersionsStr
	 *
	 * @return bool
	 */
	public static function isCompatible(string $myVersionStr, array $wantVersionsStr) : bool{
		$myVersion = new VersionString($myVersionStr);
		foreach($wantVersionsStr as $versionStr){
			$version = new VersionString($versionStr);
			//Format: majorVersion.minorVersion.patch (3.0.0)
			//    or: majorVersion.minorVersion.patch-devBuild (3.0.0-alpha1)
			if($version->getBaseVersion() !== $myVersion->getBaseVersion()){
				if($version->getMajor() !== $myVersion->getMajor() or $version->getSuffix() !== $myVersion->getSuffix()){
					continue;
				}

				if($version->getMinor() > $myVersion->getMinor()){ //If the plugin requires new API features, being backwards compatible
					continue;
				}

				if($version->getMinor() === $myVersion->getMinor() and $version->getPatch() > $myVersion->getPatch()){ //If the plugin requires bug fixes in patches, being backwards compatible
					continue;
				}
			}

			return true;
		}

		return false;
	}
}
