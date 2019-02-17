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

namespace pocketmine\plugin\manifest;

use pocketmine\plugin\PluginDescription;
use function file_exists;
use function file_get_contents;
use const DIRECTORY_SEPARATOR;

class YamlManifestLoader extends AbstractManifestLoader{

	/**
	 * @inheritdoc
	 */
	public static function canReadPlugin(string $path) : bool{
		return file_exists($path . DIRECTORY_SEPARATOR . "plugin.yml");
	}

	/**
	 * @inheritdoc
	 */
	public function getPluginDescription() : PluginDescription{
		return new PluginDescription(file_get_contents($this->path . DIRECTORY_SEPARATOR . "plugin.yml"));
	}

	/**
	 * @inheritdoc
	 */
	public function registerPlugin(\ClassLoader $loader) : void{
		$loader->addPath($this->path . DIRECTORY_SEPARATOR . "src");
	}

}