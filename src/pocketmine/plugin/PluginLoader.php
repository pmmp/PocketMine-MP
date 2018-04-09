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

/**
 * Handles different types of plugins
 */
interface PluginLoader{

	/**
	 * Loads the plugin contained in $file
	 *
	 * @param string $file
	 *
	 * @return Plugin|null
	 */
	public function loadPlugin(string $file);

	/**
	 * Gets the PluginDescription from the file
	 *
	 * @param string $file
	 *
	 * @return null|PluginDescription
	 */
	public function getPluginDescription(string $file);

	/**
	 * Returns the filename regex patterns that this loader accepts
	 *
	 * @return string
	 */
	public function getPluginFilters() : string;

	/**
	 * Returns whether this PluginLoader can load the plugin in the given path.
	 *
	 * @param string $path
	 *
	 * @return bool
	 */
	public function canLoadPlugin(string $path) : bool;

	/**
	 * @param Plugin $plugin
	 *
	 * @return void
	 */
	public function enablePlugin(Plugin $plugin);

	/**
	 * @param Plugin $plugin
	 *
	 * @return void
	 */
	public function disablePlugin(Plugin $plugin);


}
