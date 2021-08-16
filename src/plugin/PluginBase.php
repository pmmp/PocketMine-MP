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
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\scheduler\TaskScheduler;
use pocketmine\Server;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Config;
use Webmozart\PathUtil\Path;
use function count;
use function dirname;
use function fclose;
use function file_exists;
use function fopen;
use function gettype;
use function is_array;
use function is_bool;
use function is_string;
use function mkdir;
use function rtrim;
use function stream_copy_to_stream;
use function strpos;
use function strtolower;
use function trim;

abstract class PluginBase implements Plugin, CommandExecutor{

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

	/** @var ResourceProvider */
	private $resourceProvider;

	public function __construct(PluginLoader $loader, Server $server, PluginDescription $description, string $dataFolder, string $file, ResourceProvider $resourceProvider){
		$this->loader = $loader;
		$this->server = $server;
		$this->description = $description;
		$this->dataFolder = rtrim($dataFolder, "/" . DIRECTORY_SEPARATOR) . "/";
		//TODO: this is accessed externally via reflection, not unused
		$this->file = rtrim($file, "/" . DIRECTORY_SEPARATOR) . "/";
		$this->configFile = Path::join($this->dataFolder, "config.yml");

		$prefix = $this->getDescription()->getPrefix();
		$this->logger = new PluginLogger($server->getLogger(), $prefix !== "" ? $prefix : $this->getName());
		$this->scheduler = new TaskScheduler($this->getFullName());
		$this->resourceProvider = $resourceProvider;

		$this->onLoad();

		$this->registerYamlCommands();
	}

	/**
	 * Called when the plugin is loaded, before calling onEnable()
	 */
	protected function onLoad() : void{

	}

	/**
	 * Called when the plugin is enabled
	 */
	protected function onEnable() : void{

	}

	/**
	 * Called when the plugin is disabled
	 * Use this to free open things and finish actions
	 */
	protected function onDisable() : void{

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
	final public function onEnableStateChange(bool $enabled) : void{
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

	public function getLogger() : \AttachableLogger{
		return $this->logger;
	}

	/**
	 * Registers commands declared in the plugin manifest
	 */
	private function registerYamlCommands() : void{
		$pluginCmds = [];

		foreach($this->getDescription()->getCommands() as $key => $data){
			if(strpos($key, ":") !== false){
				$this->logger->error($this->server->getLanguage()->translate(KnownTranslationFactory::pocketmine_plugin_commandError($key, $this->getDescription()->getFullName())));
				continue;
			}
			if(is_array($data)){ //TODO: error out if it isn't
				$newCmd = new PluginCommand($key, $this, $this);
				if(isset($data["description"])){
					$newCmd->setDescription($data["description"]);
				}

				if(isset($data["usage"])){
					$newCmd->setUsage($data["usage"]);
				}

				if(isset($data["aliases"]) and is_array($data["aliases"])){
					$aliasList = [];
					foreach($data["aliases"] as $alias){
						if(strpos($alias, ":") !== false){
							$this->logger->error($this->server->getLanguage()->translate(KnownTranslationFactory::pocketmine_plugin_aliasError($alias, $this->getDescription()->getFullName())));
							continue;
						}
						$aliasList[] = $alias;
					}

					$newCmd->setAliases($aliasList);
				}

				if(isset($data["permission"])){
					if(is_bool($data["permission"])){
						$newCmd->setPermission($data["permission"] ? "true" : "false");
					}elseif(is_string($data["permission"])){
						$newCmd->setPermission($data["permission"]);
					}else{
						$this->logger->error("Permission must be a string, " . gettype($data["permission"]) . " given for command $key");
					}
				}

				if(isset($data["permission-message"])){
					$newCmd->setPermissionMessage($data["permission-message"]);
				}

				$pluginCmds[] = $newCmd;
			}
		}

		if(count($pluginCmds) > 0){
			$this->server->getCommandMap()->registerAll($this->getDescription()->getName(), $pluginCmds);
		}
	}

	/**
	 * @return Command|PluginOwned|null
	 * @phpstan-return (Command&PluginOwned)|null
	 */
	public function getCommand(string $name){
		$command = $this->getServer()->getPluginCommand($name);
		if($command === null or $command->getOwningPlugin() !== $this){
			$command = $this->getServer()->getPluginCommand(strtolower($this->description->getName()) . ":" . $name);
		}

		if($command instanceof PluginOwned and $command->getOwningPlugin() === $this){
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

	/**
	 * Gets an embedded resource on the plugin file.
	 * WARNING: You must close the resource given using fclose()
	 *
	 * @return null|resource Resource data, or null
	 */
	public function getResource(string $filename){
		return $this->resourceProvider->getResource($filename);
	}

	/**
	 * Saves an embedded resource to its relative location in the data folder
	 */
	public function saveResource(string $filename, bool $replace = false) : bool{
		if(trim($filename) === ""){
			return false;
		}

		if(($resource = $this->getResource($filename)) === null){
			return false;
		}

		$out = Path::join($this->dataFolder, $filename);
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
		return $this->resourceProvider->getResources();
	}

	public function getConfig() : Config{
		if($this->config === null){
			$this->reloadConfig();
		}

		return $this->config;
	}

	public function saveConfig() : void{
		$this->getConfig()->save();
	}

	public function saveDefaultConfig() : bool{
		if(!file_exists($this->configFile)){
			return $this->saveResource("config.yml", false);
		}
		return false;
	}

	public function reloadConfig() : void{
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

	public function getPluginLoader() : PluginLoader{
		return $this->loader;
	}

	public function getScheduler() : TaskScheduler{
		return $this->scheduler;
	}
}
