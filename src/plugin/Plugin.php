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
 * Plugin related classes
 */
namespace pocketmine\plugin;

use pocketmine\scheduler\TaskScheduler;
use pocketmine\Server;

/**
 * It is recommended to use PluginBase for the actual plugin
 */
interface Plugin{

	public function __construct(PluginLoader $loader, Server $server, PluginDescription $description, string $dataFolder, string $file, ResourceProvider $resourceProvider);

	/**
	 * @return bool
	 */
	public function isEnabled() : bool;

	/**
	 * Called by the plugin manager when the plugin is enabled or disabled to inform the plugin of its enabled state.
	 *
	 * @internal This is intended for core use only and should not be used by plugins
	 * @see PluginManager::enablePlugin()
	 * @see PluginManager::disablePlugin()
	 *
	 * @param bool $enabled
	 */
	public function onEnableStateChange(bool $enabled) : void;

	/**
	 * @return bool
	 */
	public function isDisabled() : bool;

	/**
	 * Gets the plugin's data folder to save files and configuration.
	 * This directory name has a trailing slash.
	 *
	 * @return string
	 */
	public function getDataFolder() : string;

	/**
	 * @return PluginDescription
	 */
	public function getDescription() : PluginDescription;

	/**
	 * @return Server
	 */
	public function getServer() : Server;

	/**
	 * @return string
	 */
	public function getName() : string;

	/**
	 * @return \AttachableLogger
	 */
	public function getLogger() : \AttachableLogger;

	/**
	 * @return PluginLoader
	 */
	public function getPluginLoader() : PluginLoader;

	/**
	 * @return TaskScheduler
	 */
	public function getScheduler() : TaskScheduler;

}
