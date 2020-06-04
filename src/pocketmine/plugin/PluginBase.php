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

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\scheduler\TaskScheduler;
use pocketmine\Server;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Config;
use function dirname;
use function fclose;
use function file_exists;
use function fopen;
use function is_dir;
use function mkdir;
use function rtrim;
use function str_replace;
use function stream_copy_to_stream;
use function strlen;
use function strpos;
use function strtolower;
use function substr;
use function trim;
use const DIRECTORY_SEPARATOR;

abstract class PluginBase implements Plugin{

	/** @var PluginLoader */
	private $loader;

	/** @var Server */
	private $server;

	/** @var bool */
	private $isEnabled = false;

	/** @var PluginDescription */
	private $description;

	/** @var string */
	private $dataFolder;
	/** @var Config|null */
	private $config = null;
	/** @var string */
	private $configFile;
	/** @var string */
	private $file;

	/** @var PluginLogger */
	private $logger;

	/** @var TaskScheduler */
	private $scheduler;

	public function __construct(PluginLoader $loader, Server $server, PluginDescription $description, string $dataFolder, string $file){
		$this->loader = $loader;
		$this->server = $server;
		$this->description = $description;
		$this->dataFolder = rtrim($dataFolder, "/" . DIRECTORY_SEPARATOR) . "/";
		$this->file = rtrim($file, "/" . DIRECTORY_SEPARATOR) . "/";
		$this->configFile = $this->dataFolder . "config.yml";
		$this->logger = new PluginLogger($this);
		$this->scheduler = new TaskScheduler($this->logger, $this->getFullName());
	}

	public function onLoad(){

	}

	public function onEnable(){

	}

	public function onDisable(){

	}

	final public function isEnabled() : bool{
		return $this->isEnabled;
	}

	/**
	 * Called by the plugin manager when the plugin is enabled or disabled to inform the plugin of its enabled state.
	 *
	 * @internal This is intended for core use only and should not be used by plugins
	 * @see PluginManager::enablePlugin()
	 * @see PluginManager::disablePlugin()
	 */
	final public function setEnabled(bool $enabled = true) : void{
		if($this->isEnabled !== $enabled){
			$this->isEnabled = $enabled;
			if($this->isEnabled){
				$this->onEnable();
			}else{
				$this->onDisable();
			}
		}
	}

	final public function isDisabled() : bool{
		return !$this->isEnabled;
	}

	final public function getDataFolder() : string{
		return $this->dataFolder;
	}

	final public function getDescription() : PluginDescription{
		return $this->description;
	}

	public function getLogger() : PluginLogger{
		return $this->logger;
	}

	/**
	 * @return Command|PluginIdentifiableCommand|null
	 */
	public function getCommand(string $name){
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
	 * @param string[]      $args
	 */
	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		return false;
	}

	protected function isPhar() : bool{
		return strpos($this->file, "phar://") === 0;
	}

	/**
	 * Gets an embedded resource on the plugin file.
	 * WARNING: You must close the resource given using fclose()
	 *
	 * @return null|resource Resource data, or null
	 */
	public function getResource(string $filename){
		$filename = rtrim(str_replace(DIRECTORY_SEPARATOR, "/", $filename), "/");
		if(file_exists($this->file . "resources/" . $filename)){
			$resource = fopen($this->file . "resources/" . $filename, "rb");
			if($resource === false) throw new AssumptionFailedError("fopen() should not fail on a file which exists");
			return $resource;
		}

		return null;
	}

	public function saveResource(string $filename, bool $replace = false) : bool{
		if(trim($filename) === ""){
			return false;
		}

		if(($resource = $this->getResource($filename)) === null){
			return false;
		}

		$out = $this->dataFolder . $filename;
		if(!file_exists(dirname($out))){
			mkdir(dirname($out), 0755, true);
		}

		if(file_exists($out) and !$replace){
			return false;
		}

		$fp = fopen($out, "wb");
		if($fp === false) throw new AssumptionFailedError("fopen() should not fail with wb flags");

		$ret = stream_copy_to_stream($resource, $fp) > 0;
		fclose($fp);
		fclose($resource);
		return $ret;
	}

	/**
	 * Returns all the resources packaged with the plugin in the form ["path/in/resources" => SplFileInfo]
	 *
	 * @return \SplFileInfo[]
	 */
	public function getResources() : array{
		$resources = [];
		if(is_dir($this->file . "resources/")){
			foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->file . "resources/")) as $resource){
				if($resource->isFile()){
					$path = str_replace(DIRECTORY_SEPARATOR, "/", substr((string) $resource, strlen($this->file . "resources/")));
					$resources[$path] = $resource;
				}
			}
		}

		return $resources;
	}

	public function getConfig() : Config{
		if($this->config === null){
			$this->reloadConfig();
		}

		return $this->config;
	}

	public function saveConfig(){
		if(!$this->getConfig()->save()){
			$this->getLogger()->critical("Could not save config to " . $this->configFile);
		}
	}

	public function saveDefaultConfig() : bool{
		if(!file_exists($this->configFile)){
			return $this->saveResource("config.yml", false);
		}
		return false;
	}

	public function reloadConfig(){
		$this->saveDefaultConfig();
		$this->config = new Config($this->configFile);
	}

	final public function getServer() : Server{
		return $this->server;
	}

	final public function getName() : string{
		return $this->description->getName();
	}

	final public function getFullName() : string{
		return $this->description->getFullName();
	}

	protected function getFile() : string{
		return $this->file;
	}

	/**
	 * @return PluginLoader
	 */
	public function getPluginLoader(){
		return $this->loader;
	}

	public function getScheduler() : TaskScheduler{
		return $this->scheduler;
	}
}
