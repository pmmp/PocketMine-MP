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
use pocketmine\plugin\loader\PluginLoader;

interface PluginManifest{

	/**
	 * Returns whether this manifest implementation can find a readable manifest file.
	 *
	 * @param string $path Root path to a plugin archive or folder (with access protocol).
	 *
	 * @return bool
	 */
	public static function canReadPlugin(string $path) : bool;

	/**
	 * Gets the PluginDescription from the manifest file.
	 *
	 * @return PluginDescription|null
	 */
	public function getPluginDescription() : ?PluginDescription;

	/**
	 * Register the plugin into the runtime.
	 *
	 * @param \ClassLoader $loader
	 */
	public function registerPlugin(\ClassLoader $loader) : void;

}