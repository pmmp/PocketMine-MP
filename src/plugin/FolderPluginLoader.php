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

use pocketmine\thread\ThreadSafeClassLoader;
use pocketmine\utils\Filesystem;
use Symfony\Component\Filesystem\Path;
use function file_exists;
use function is_dir;

class FolderPluginLoader implements PluginLoader{
	public function __construct(
		private readonly ThreadSafeClassLoader $loader
	){}

	public function canLoadPlugin(string $path) : bool{
		return is_dir($path) && file_exists(Path::join($path, "plugin.yml")) && file_exists(Path::join($path, "src"));
	}

	/**
	 * Loads the plugin contained in $file
	 */
	public function loadPlugin(string $path) : void{
		$description = $this->getPluginDescription($path);
		if($description !== null){
			$this->loader->addPath($description->getSrcNamespacePrefix(), "$path/src");
		}
	}

	/**
	 * Gets the PluginDescription from the file
	 */
	public function getPluginDescription(string $path) : ?PluginDescription{
		$pluginYmlPath = Path::join($path, "plugin.yml");
		if(is_dir($path) && file_exists($pluginYmlPath)){
			try{
				$yaml = Filesystem::fileGetContents($pluginYmlPath);
			}catch(\RuntimeException){
				//TODO: this ought to be logged
				return null;
			}
			return new PluginDescription($yaml);
		}

		return null;
	}

	public function getAccessProtocol() : string{
		return "";
	}
}
