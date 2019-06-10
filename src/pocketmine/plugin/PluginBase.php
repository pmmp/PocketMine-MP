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
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Server;
use pocketmine\utils\Config;
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
use function stream_copy_to_stream;
use function strpos;
use function strtolower;

abstract class PluginBase extends PluginImpl implements CommandExecutor{

	/** @var ResourceProvider */
	private $resourceProvider;
	/** @var string */
	private $file;

	/** @var Config|null */
	private $config = null;
	/** @var string */
	private $configFile;

	final public function __construct(Server $server, PluginDescription $description, string $dataFolder, string $loaderType, ResourceProvider $resourceProvider, string $file){
		parent::__construct($server, $description, $dataFolder, $loaderType);
		$this->resourceProvider = $resourceProvider;
		$this->file = $file;
		$this->configFile = $this->getDataFolder() . "config.yml";

		$this->registerYamlCommands();
	}


	/**
	 * To trigger a warning if plugins try to override the old onLoad() method
	 */
	final protected function onLoad() : void{
		throw new \BadMethodCallException("PluginBase::onLoad() should never be called");
	}

	/**
	 * @return bool
	 */
	final public function isDisabled() : bool{
		return !$this->isEnabled();
	}

	/**
	 * @return string
	 */
	final public function getFullName() : string{
		return $this->getDescription()->getFullName();
	}


	/**
	 * Registers commands declared in the plugin manifest
	 */
	private function registerYamlCommands() : void{
		$pluginCmds = [];

		foreach($this->getDescription()->getCommands() as $key => $data){
			if(strpos($key, ":") !== false){
				$this->getLogger()->error($this->getServer()->getLanguage()->translateString("pocketmine.plugin.commandError", [$key, $this->getDescription()->getFullName()]));
				continue;
			}
			if(is_array($data)){ //TODO: error out if it isn't
				$newCmd = new PluginCommand($key, $this);
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
							$this->getLogger()->error($this->getServer()->getLanguage()->translateString("pocketmine.plugin.aliasError", [$alias, $this->getDescription()->getFullName()]));
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
						$this->getLogger()->error("Permission must be a string, " . gettype($data["permission"]) . " given for command $key");
					}
				}

				if(isset($data["permission-message"])){
					$newCmd->setPermissionMessage($data["permission-message"]);
				}

				$pluginCmds[] = $newCmd;
			}
		}

		if(count($pluginCmds) > 0){
			$this->getServer()->getCommandMap()->registerAll($this->getDescription()->getName(), $pluginCmds);
		}
	}

	/**
	 * @param string $name
	 *
	 * @return Command|PluginIdentifiableCommand|null
	 */
	public function getCommand(string $name){
		$command = $this->getServer()->getPluginCommand($name);
		if($command === null or $command->getPlugin() !== $this){
			$command = $this->getServer()->getPluginCommand(strtolower($this->getDescription()->getName()) . ":" . $name);
		}

		return ($command instanceof PluginIdentifiableCommand and $command->getPlugin() === $this) ? $command : null;
	}

	/**
	 * @param CommandSender $sender
	 * @param Command       $command
	 * @param string        $label
	 * @param string[]      $args
	 *
	 * @return bool
	 */
	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		return false;
	}


	/**
	 * Gets an embedded resource on the plugin file.
	 * WARNING: You must close the resource given using fclose()
	 *
	 * @param string $filename
	 *
	 * @return null|resource Resource data, or null
	 */
	public function getResource(string $filename){
		return $this->resourceProvider->getResource($filename);
	}

	/**
	 * Saves an embedded resource to its relative location in the data folder
	 *
	 * @param string $filename
	 * @param bool   $replace
	 *
	 * @return bool
	 */
	public function saveResource(string $filename, bool $replace = false) : bool{
		$resource = $this->getResource($filename);
		if($resource === null){
			throw new \RuntimeException("Resource $filename does not exist");
		}

		$out = $this->getDataFolder() . $filename;
		if(!file_exists(dirname($out))){
			mkdir(dirname($out), 0755, true);
		}

		if(file_exists($out) and !$replace){
			return false;
		}

		$ret = stream_copy_to_stream($resource, $fp = fopen($out, "wb")) > 0;
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

	/**
	 * @return Config
	 */
	public function getConfig() : Config{
		if($this->config === null){
			$this->reloadConfig();
		}

		return $this->config;
	}

	public function saveConfig(){
		$this->getConfig()->save();
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


	/**
	 * @return string
	 */
	public function getFile() : string{
		return $this->file;
	}
}
