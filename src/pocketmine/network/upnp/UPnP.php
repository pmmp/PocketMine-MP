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
 * UPnP port forwarding support. Only for Windows
 */
namespace pocketmine\network\upnp;

use pocketmine\utils\Internet;
use pocketmine\utils\Utils;
use function class_exists;
use function is_object;

abstract class UPnP{

	public static function PortForward(int $port) : void{
		if(!Internet::$online){
			throw new \RuntimeException("Server is offline");
		}
		if(Utils::getOS() !== Utils::OS_WINDOWS){
			throw new \RuntimeException("UPnP is only supported on Windows");
		}
		if(!class_exists("COM")){
			throw new \RuntimeException("UPnP requires the com_dotnet extension");
		}

		$myLocalIP = Internet::getInternalIP();

		/** @noinspection PhpUndefinedClassInspection */
		$com = new \COM("HNetCfg.NATUPnP");
		/** @noinspection PhpUndefinedFieldInspection */

		if(!is_object($com->StaticPortMappingCollection)){
			throw new \RuntimeException("Failed to portforward using UPnP. Ensure that network discovery is enabled in Control Panel.");
		}

		/** @noinspection PhpUndefinedFieldInspection */
		$com->StaticPortMappingCollection->Add($port, "UDP", $port, $myLocalIP, true, "PocketMine-MP");
	}

	public static function RemovePortForward(int $port) : bool{
		if(!Internet::$online){
			return false;
		}
		if(Utils::getOS() !== Utils::OS_WINDOWS or !class_exists("COM")){
			return false;
		}

		try{
			/** @noinspection PhpUndefinedClassInspection */
			$com = new \COM("HNetCfg.NATUPnP");
			/** @noinspection PhpUndefinedFieldInspection */
			if(!is_object($com->StaticPortMappingCollection)){
				return false;
			}
			/** @noinspection PhpUndefinedFieldInspection */
			$com->StaticPortMappingCollection->Remove($port, "UDP");
		}catch(\Throwable $e){
			return false;
		}

		return true;
	}
}
