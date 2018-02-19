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
use pocketmine\Server;
use pocketmine\utils\Config;


/**
 * It is recommended to use PluginBase for the actual plugin
 *
 */
interface Plugin extends CommandExecutor{

	/**
	 * Called when the plugin is loaded, before calling onEnable()
	 */
	public function onLoad();

	/**
	 * Called when the plugin is enabled
	 */
	public function onEnable();

	/**
	 * @return bool
	 */
	public function isEnabled() : bool;

	/**
	 * Called when the plugin is disabled
	 * Use this to free open things and finish actions
	 */
	public function onDisable();

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
	 * Gets an embedded resource in the plugin file.
	 *
	 * @param string $filename
	 *
	 * @return null|resource Resource data, or null
	 */
	public function getResource(string $filename);

	/**
	 * Saves an embedded resource to its relative location in the data folder
	 *
	 * @param string $filename
	 * @param bool $replace
	 *
	 * @return bool
	 */
	public function saveResource(string $filename, bool $replace = false) : bool;

	/**
	 * Returns all the resources packaged with the plugin
	 *
	 * @return string[]
	 */
	public function getResources() : array;

	/**
	 * @return Config
	 */
	public function getConfig() : Config;

	public function saveConfig();

	/**
	 * @return bool
	 */
	public function saveDefaultConfig() : bool;

	public function reloadConfig();

	/**
	 * @return Server
	 */
	public function getServer() : Server;

	/**
	 * @return string
	 */
	public function getName() : string;

	/**
	 * @return PluginLogger
	 */
	public function getLogger() : PluginLogger;

	/**
	 * @return PluginLoader
	 */
	public function getPluginLoader();

}
