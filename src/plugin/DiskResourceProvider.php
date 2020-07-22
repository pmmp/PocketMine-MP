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
use SOFe\Pathetique\Path;
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

	/** @var Path */
	private $file;

	public function __construct(Path $path){
		$this->file = $path;
	}

	/**
	 * Gets an embedded resource on the plugin file.
	 * WARNING: You must close the resource given using fclose()
	 *
	 * @return null|resource Resource data, or null
	 */
	public function getResource(string $filename){
		$path = $this->file->join($filename);
		if($path->exists()){
			$resource = fopen($path->toString(), "rb");
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
		if($this->file->isDir()){
			foreach($this->file->scanRecursively() as $resource){
				if($resource->isFile()){
					$path = $this->file->findPath($resource);
					$resources[$path->toString()] = $resource;
				}
			}
		}

		return $resources;
	}
}
