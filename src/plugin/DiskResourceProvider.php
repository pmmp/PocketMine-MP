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

use pocketmine\utils\AssumptionFailedError;
use function file_exists;
use function fopen;
use function is_dir;
use function rtrim;
use function str_replace;
use function strlen;
use function substr;
use const DIRECTORY_SEPARATOR;

/**
 * Provides resources from the given plugin directory on disk. The path may be prefixed with a specific access protocol
 * to enable special types of access.
 */
class DiskResourceProvider implements ResourceProvider{
	private string $file;

	public function __construct(string $path){
		$this->file = rtrim(str_replace(DIRECTORY_SEPARATOR, "/", $path), "/") . "/";
	}

	/**
	 * Gets an embedded resource on the plugin file.
	 * WARNING: You must close the resource given using fclose()
	 *
	 * @return null|resource Resource data, or null
	 */
	public function getResource(string $filename){
		$filename = rtrim(str_replace(DIRECTORY_SEPARATOR, "/", $filename), "/");
		if(file_exists($this->file . $filename)){
			$resource = fopen($this->file . $filename, "rb");
			if($resource === false) throw new AssumptionFailedError("fopen() should not fail on a file which exists");
			return $resource;
		}

		return null;
	}

	/**
	 * Returns all the resources packaged with the plugin in the form ["path/in/resources" => SplFileInfo]
	 *
	 * @return \SplFileInfo[]
	 */
	public function getResources() : array{
		$resources = [];
		if(is_dir($this->file)){
			/** @var \SplFileInfo $resource */
			foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->file)) as $resource){
				if($resource->isFile()){
					$path = str_replace(DIRECTORY_SEPARATOR, "/", substr((string) $resource, strlen($this->file)));
					$resources[$path] = $resource;
				}
			}
		}

		return $resources;
	}
}
