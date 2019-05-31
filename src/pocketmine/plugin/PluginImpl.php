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

use pocketmine\scheduler\TaskScheduler;
use pocketmine\Server;
use function rtrim;

/**
 * This class is a basic implementation of the Plugin interface. It should only be directly extended by plugin loader wrappers.
 * It only serves as a convenient class to extract the "common" parts of different plugin loaders.
 *
 * Ordinary plugins should extend subclasses of this class, such as:
 * @see PluginBase
 * @see ScriptPlugin
 */
abstract class PluginImpl implements Plugin{
	/** @var Server */
	private $server;
	/** @var PluginDescription */
	private $description;
	/** @var string */
	private $dataFolder;
	/** @var PluginLogger */
	private $logger;
	/** @var TaskScheduler */
	private $scheduler;

	private $isEnabled = false;
	/**
	 * @var string
	 */
	private $loaderType;

	public function __construct(Server $server, PluginDescription $description, string $dataFolder, string $loaderType){
		$this->server = $server;
		$this->description = $description;
		$this->dataFolder = rtrim($dataFolder, "\\/") . "/";
		$this->loaderType = $loaderType;
		$this->logger = new PluginLogger($this);
		$this->scheduler = new TaskScheduler($this->getDescription()->getFullName());
	}

	public function isEnabled() : bool{
		return $this->isEnabled;
	}

	public function onEnableStateChange(bool $enabled) : void{
		if($this->isEnabled !== $enabled){
			$this->isEnabled = $enabled;
			if($this->isEnabled){
				$this->onEnable();
			}else{
				$this->onDisable();
			}
		}
	}

	protected function onEnable() : void{
	}

	protected function onDisable() : void{
	}

	public function getDataFolder() : string{
		return $this->dataFolder;
	}

	public function getLoaderType() : string{
		return $this->loaderType;
	}

	public function getName() : string{
		return $this->description->getName();
	}

	public function getDescription() : PluginDescription{
		return $this->description;
	}

	public function getServer() : Server{
		return $this->server;
	}

	public function getLogger() : PluginLogger{
		return $this->logger;
	}

	public function getScheduler() : TaskScheduler{
		return $this->scheduler;
	}
}
