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

use pocketmine\command\CommandExecutor;
use pocketmine\scheduler\TaskScheduler;
use pocketmine\Server;
use pocketmine\utils\Config;

/**
 * It is recommended to use PluginBase for the actual plugin
 */
interface Plugin extends CommandExecutor{

	public function __construct(PluginLoader $loader, Server $server, PluginDescription $description, string $dataFolder, string $file);

	/**
	 * Called when the plugin is loaded, before calling onEnable()
	 *
	 * @return void
	 */
	public function onLoad();

	/**
	 * Called when the plugin is enabled
	 *
	 * @return void
	 */
	public function onEnable();

	public function isEnabled() : bool;

	/**
	 * Called by the plugin manager when the plugin is enabled or disabled to inform the plugin of its enabled state.
	 *
	 * @internal This is intended for core use only and should not be used by plugins
	 * @see PluginManager::enablePlugin()
	 * @see PluginManager::disablePlugin()
	 */
	public function setEnabled(bool $enabled = true) : void;

	/**
	 * Called when the plugin is disabled
	 * Use this to free open things and finish actions
	 *
	 * @return void
	 */
	public function onDisable();

	public function isDisabled() : bool;

	/**
	 * Gets the plugin's data folder to save files and configuration.
	 * This directory name has a trailing slash.
	 */
	public function getDataFolder() : string;

	public function getDescription() : PluginDescription;

	/**
	 * Gets an embedded resource in the plugin file.
	 *
	 * @return null|resource Resource data, or null
	 */
	public function getResource(string $filename);

	/**
	 * Saves an embedded resource to its relative location in the data folder
	 */
	public function saveResource(string $filename, bool $replace = false) : bool;

	/**
	 * Returns all the resources packaged with the plugin
	 *
	 * @return \SplFileInfo[]
	 */
	public function getResources() : array;

	public function getConfig() : Config;

	/**
	 * @return void
	 */
	public function saveConfig();

	public function saveDefaultConfig() : bool;

	/**
	 * @return void
	 */
	public function reloadConfig();

	public function getServer() : Server;

	public function getName() : string;

	public function getLogger() : PluginLogger;

	/**
	 * @return PluginLoader
	 */
	public function getPluginLoader();

	public function getScheduler() : TaskScheduler;

}
