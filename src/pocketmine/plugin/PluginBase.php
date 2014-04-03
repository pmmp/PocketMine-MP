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

namespace pocketmine\plugin;

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Server;
use pocketmine\utils\Config;

abstract class PluginBase implements Plugin{

	/** @var PluginLoader */
	private $loader;

	/** @var \pocketmine\Server */
	private $server;

	/** @var bool */
	private $isEnabled = false;

	/** @var bool */
	private $initialized = false;

	/** @var PluginDescription */
	private $description;

	/** @var string */
	private $dataFolder;
	private $config;
	/** @var string */
	private $configFile;
	private $file;

	/**
	 * Called when the plugin is loaded, before calling onEnable()
	 */
	public function onLoad(){

	}

	public function onEnable(){

	}

	public function onDisable(){

	}

	/**
	 * @return bool
	 */
	public final function isEnabled(){
		return $this->isEnabled === true;
	}

	/**
	 * @param bool $boolean
	 */
	public final function setEnabled($boolean = true){
		if($this->isEnabled !== $boolean){
			$this->isEnabled = $boolean;
			if($this->isEnabled === true){
				$this->onEnable();
			}else{
				$this->onDisable();
			}
		}
	}

	/**
	 * @return bool
	 */
	public final function isDisabled(){
		return $this->isEnabled === false;
	}

	public final function getDataFolder(){
		return $this->dataFolder;
	}

	public final function getDescription(){
		return $this->description;
	}

	public final function init(PluginLoader $loader, Server $server, PluginDescription $description, $dataFolder, $file){
		if($this->initialized === false){
			$this->initialized = true;
			$this->loader = $loader;
			$this->server = $server;
			$this->description = $description;
			$this->dataFolder = rtrim($dataFolder, "\\/") . "/";
			$this->file = rtrim($file, "\\/") . "/";
			$this->configFile = $this->dataFolder . "config.yml";
		}
	}

	/**
	 * @return bool
	 */
	public final function isInitialized(){
		return $this->initialized;
	}

	/**
	 * @param string $name
	 *
	 * @return PluginIdentifiableCommand
	 */
	public function getCommand($name){
		$command = $this->getServer()->getPluginCommand($name);
		if($command === null or $command->getPlugin() !== $this){
			$command = $this->getServer()->getPluginCommand(strtolower($this->description->getName()) . ":" . $name);
		}

		if($command instanceof PluginIdentifiableCommand and $command->getPlugin() === $this){
			return $command;
		}else{
			return null;
		}
	}

	/**
	 * @return bool
	 */
	protected function isPhar(){
		return substr($this->file, 0, 7) === "phar://";
	}

	/**
	 * Gets an embedded resource on the plugin file.
	 *
	 * @param string $filename
	 *
	 * @return bool|string Resource data, or false
	 */
	public function getResource($filename){
		$filename = rtrim(str_replace("\\", "/", $filename), "/");
		if(file_exists($this->file . "resources/" . $filename)){
			return file_get_contents($this->file . "resources/" . $filename);
		}

		return false;
	}

	/**
	 * @param string $filename
	 * @param bool   $replace
	 *
	 * @return bool
	 */
	public function saveResource($filename, $replace = false){
		if(trim($filename) === ""){
			return false;
		}

		if(($resource = $this->getResource($filename)) === false){
			return false;
		}

		$out = $this->dataFolder . $filename;
		if(!file_exists($this->dataFolder)){
			@mkdir($this->dataFolder, 0755, true);
		}

		if(file_exists($out) and $replace !== true){
			return false;
		}

		return @file_put_contents($out, $resource) !== false;
	}

	/**
	 * Returns all the resources incrusted on the plugin
	 *
	 * @return string[]
	 */
	public function getResources(){
		$resources = array();
		if(is_dir($this->file . "resources/")){
			foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->file . "resources/")) as $resource){
				$resources[] = $resource;
			}
		}

		return $resources;
	}

	/**
	 * @return Config
	 */
	public function getConfig(){
		if(!isset($this->config)){
			$this->reloadConfig();
		}

		return $this->config;
	}

	public function saveConfig(){
		if($this->getConfig()->save() === false){
			console("[SEVERE] Could not save config to " . $this->configFile);
		}
	}

	public function saveDefaultConfig(){
		if(!file_exists($this->configFile)){
			$this->saveResource("config.yml", false);
		}
	}

	public function reloadConfig(){
		$this->config = new Config($this->configFile);
		if(($configStream = $this->getResource("config.yml")) !== false){
			$this->config->setDefaults(yaml_parse(config::fixYAMLIndexes($configStream)));
		}
	}

	/**
	 * @return Server
	 */
	public final function getServer(){
		return $this->server;
	}

	/**
	 * @return string
	 */
	public final function getName(){
		return $this->description->getName();
	}

	protected function getFile(){
		return $this->file;
	}

	/**
	 * @return PluginLoader
	 */
	public function getPluginLoader(){
		return $this->loader;
	}

}