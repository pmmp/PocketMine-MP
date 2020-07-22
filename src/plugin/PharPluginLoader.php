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

use SOFe\Pathetique\Path;
use function is_file;
use function strlen;
use function substr;

/**
 * Handles different types of plugins
 */
class PharPluginLoader implements PluginLoader{

	/** @var \DynamicClassLoader */
	private $loader;

	public function __construct(\DynamicClassLoader $loader){
		$this->loader = $loader;
	}

	public function canLoadPlugin(Path $path) : bool{
		return $path->isFile() && $path->getExtension() === "phar";
	}

	/**
	 * Loads the plugin contained in $file
	 */
	public function loadPlugin(Path $file) : void{
		$this->loader->addPath($file->join("src")->toString());
	}

	/**
	 * Gets the PluginDescription from the file
	 */
	public function getPluginDescription(Path $file) : ?PluginDescription{
		$phar = new \Phar($file->toString());
		if(isset($phar["plugin.yml"])){
			return new PluginDescription($phar["plugin.yml"]->getContent());
		}

		return null;
	}

	public function getAccessProtocol() : string{
		return "phar://";
	}
}
