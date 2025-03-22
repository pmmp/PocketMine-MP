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
use pocketmine\utils\Config;
use pocketmine\utils\Utils;
use Symfony\Component\Filesystem\Path;
use function copy;
use function count;
use function dirname;
use function file_exists;
use function mkdir;
use function rtrim;
use function str_contains;
use function strtolower;
use function trim;
use const DIRECTORY_SEPARATOR;

abstract class PluginBase implements Plugin, CommandExecutor{
	private bool $isEnabled = false;

	private string $resourceFolder;

	private ?Config $config = null;
	private string $configFile;

	private PluginLogger $logger;
	private TaskScheduler $scheduler;

	public function __construct(
		private PluginLoader $loader,
		private Server $server,
		private PluginDescription $description,
		private string $dataFolder,
		private string $file,
		private ResourceProvider $resourceProvider
	){
		$this->dataFolder = rtrim($dataFolder, "/" . DIRECTORY_SEPARATOR) . "/";
		//TODO: this is accessed externally via reflection, not unused
		$this->file = rtrim($file, "/" . DIRECTORY_SEPARATOR) . "/";
		$this->resourceFolder = Path::join($this->file, "resources") . "/";

		$this->configFile = Path::join($this->dataFolder, "config.yml");

		$prefix = $this->description->getPrefix();
		$this->logger = new PluginLogger($server->getLogger(), $prefix !== "" ? $prefix : $this->getName());
		$this->scheduler = new TaskScheduler($this->getFullName());

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

		foreach(Utils::stringifyKeys($this->description->getCommands()) as $key => $data){
			if(str_contains($key, ":")){
				$this->logger->error($this->server->getLanguage()->translate(KnownTranslationFactory::pocketmine_plugin_commandError($key, $this->description->getFullName(), ":")));
				continue;
			}

			$newCmd = new PluginCommand($key, $this, $this);
			if(($description = $data->getDescription()) !== null){
				$newCmd->setDescription($description);
			}

			if(($usageMessage = $data->getUsageMessage()) !== null){
				$newCmd->setUsage($usageMessage);
			}

			$aliasList = [];
			foreach($data->getAliases() as $alias){
				if(str_contains($alias, ":")){
					$this->logger->error($this->server->getLanguage()->translate(KnownTranslationFactory::pocketmine_plugin_aliasError($alias, $this->description->getFullName(), ":")));
					continue;
				}
				$aliasList[] = $alias;
			}

			$newCmd->setAliases($aliasList);

			$newCmd->setPermission($data->getPermission());

			if(($permissionDeniedMessage = $data->getPermissionDeniedMessage()) !== null){
				$newCmd->setPermissionMessage($permissionDeniedMessage);
			}

			$pluginCmds[] = $newCmd;
		}

		if(count($pluginCmds) > 0){
			$this->server->getCommandMap()->registerAll($this->description->getName(), $pluginCmds);
		}
	}

	/**
	 * @return Command|PluginOwned|null
	 * @phpstan-return (Command&PluginOwned)|null
	 */
	public function getCommand(string $name){
		$command = $this->server->getPluginCommand($name);
		if($command === null || $command->getOwningPlugin() !== $this){
			$command = $this->server->getPluginCommand(strtolower($this->description->getName()) . ":" . $name);
		}

		if($command instanceof PluginOwned && $command->getOwningPlugin() === $this){
			return $command;
		}else{
			return null;
		}
	}

	/**
	 * @param string[] $args
	 */
	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		return false;
	}

	/**
	 * Returns the path to the folder where the plugin's embedded resource files are usually located.
	 * Note: This is NOT the same as the data folder. The files in this folder should be considered read-only.
	 */
	public function getResourceFolder() : string{
		return $this->resourceFolder;
	}

	/**
	 * Returns the full path to a data file in the plugin's resources folder.
	 * This path can be used with standard PHP functions like fopen() or file_get_contents().
	 *
	 * Note: Any path returned by this function should be considered READ-ONLY.
	 */
	public function getResourcePath(string $filename) : string{
		return Path::join($this->getResourceFolder(), $filename);
	}

	/**
	 * @deprecated Prefer using standard PHP functions with {@link PluginBase::getResourcePath()}, like
	 * file_get_contents() or fopen().
	 *
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

		$source = Path::join($this->resourceFolder, $filename);
		if(!file_exists($source)){
			return false;
		}

		$destination = Path::join($this->dataFolder, $filename);
		if(file_exists($destination) && !$replace){
			return false;
		}

		if(!file_exists(dirname($destination))){
			mkdir(dirname($destination), 0755, true);
		}

		return copy($source, $destination);
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
