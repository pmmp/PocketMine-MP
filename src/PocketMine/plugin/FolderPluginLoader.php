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

namespace PocketMine\Plugin;

use PocketMine\Event\EventHandler;
use PocketMine\Server;
use PocketMine\Event\Plugin\PluginEnableEvent;
use PocketMine\Event\Plugin\PluginDisableEvent;

/**
 * Handles different types of plugins
 */
class FolderPluginLoader implements PluginLoader{

	/**
	 * @var Server
	 */
	private $server;

	/**
	 * @param Server $server
	 */
	public function __construct(Server $server){
		$this->server = $server;
	}

	/**
	 * Loads the plugin contained in $file
	 *
	 * @param string $file
	 *
	 * @return Plugin
	 */
	public function loadPlugin($file){
		if(is_dir($file) and file_exists($file . "/plugin.yml") and file_exists($file . "/src/")){
			if(($description = $this->getPluginDescription($file)) instanceof PluginDescription){
				$dataFolder = basename($file) . "/" . $description->getName();
				if(file_exists($dataFolder) and !is_dir($dataFolder)){
					trigger_error("Projected dataFolder '".$dataFolder."' for ".$description->getName()." exists and is not a directory", E_USER_WARNING);
					return null;
				}

				$className = $description->getMain();
				if(class_exists($className, true)){ //call autoloader, TODO: replace this with a specific Plugin autoload
					$plugin = new $className();
					$this->initPlugin($plugin, $description, $dataFolder, $file);
					return $plugin;
				}
			}
		}
		return null;
	}

	/**
	 * Gets the PluginDescription from the file
	 *
	 * @param string $file
	 *
	 * @return PluginDescription
	 */
	public function getPluginDescription($file){
		if(is_dir($file) and file_exists($file . "/plugin.yml")){
			$yaml = @file_get_contents($file . "/plugin.yml");
			if($yaml != ""){
				return new PluginDescription($yaml);
			}
		}
		return null;
	}

	/**
	 * Returns the filename patterns that this loader accepts
	 *
	 * @return array
	 */
	public function getPluginFilters(){
		return "/[^\\.]/";
	}

	private function initPlugin(PluginBase $plugin, PluginDescription $description, $dataFolder, $file){
		$plugin->init($this, $this->server, $description, $dataFolder, $file);
	}

	public function enablePlugin(Plugin $plugin){
		if($plugin instanceof PluginBase and !$plugin->isEnabled()){
			console("[INFO] Enabling ".$plugin->getDescription()->getName());

			$plugin->setEnabled(true);

			EventHandler::callEvent(new PluginEnableEvent($plugin));
		}
	}

	public function disablePlugin(Plugin $plugin){
		if($plugin instanceof PluginBase and $plugin->isEnabled()){
			console("[INFO] Disabling ".$plugin->getDescription()->getName());

			EventHandler::callEvent(new PluginDisableEvent($plugin));

			$plugin->setEnabled(false);
		}
	}
}