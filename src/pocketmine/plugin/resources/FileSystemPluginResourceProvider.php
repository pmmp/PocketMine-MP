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

namespace pocketmine\plugin\resources;

use Generator;
use SplFileInfo;
use function file_exists;
use function fopen;
use function is_dir;
use function rtrim;
use function str_replace;
use function strlen;
use function substr;
use const DIRECTORY_SEPARATOR;

class FileSystemPluginResourceProvider implements PluginResourceProvider{
	/** @var string */
	private $file;

	/**
	 * FileSystemPluginResourceProvider constructor.
	 *
	 * @param string $file
	 */
	public function __construct(string $file){
		$this->file = $file;
	}

	public function getResource(string $filename){
		$filename = rtrim(str_replace("\\", "/", $filename), "/");
		if(file_exists($this->file . "resources/" . $filename)){
			return fopen($this->file . "resources/" . $filename, "rb");
		}

		return null;
	}

	public function listResources() : Generator{
		if(is_dir($this->file . "resources/")){
			/** @var SplFileInfo $info */
			foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->file . "resources/")) as $info){
				if($info->isFile()){
					$path = str_replace(DIRECTORY_SEPARATOR, "/", substr((string) $info, strlen($this->file . "resources/")));
					yield $path => $info;
				}
			}
		}
	}
}
