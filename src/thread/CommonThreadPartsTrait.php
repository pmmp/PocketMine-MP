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

namespace pocketmine\thread;

use pocketmine\errorhandler\ErrorToExceptionHandler;
use pocketmine\Server;
use function error_reporting;

trait CommonThreadPartsTrait{
	/** @var \Threaded|\ClassLoader[]|null  */
	private ?\Threaded $classLoaders = null;
	/** @var string|null */
	protected $composerAutoloaderPath;

	/** @var bool */
	protected $isKilled = false;

	/**
	 * @return \ClassLoader[]
	 */
	public function getClassLoaders() : ?array{
		return $this->classLoaders !== null ? (array) $this->classLoaders : null;
	}

	/**
	 * @param \ClassLoader[] $autoloaders
	 */
	public function setClassLoaders(?array $autoloaders = null) : void{
		$this->composerAutoloaderPath = \pocketmine\COMPOSER_AUTOLOADER_PATH;

		if($autoloaders === null){
			$autoloaders = [Server::getInstance()->getLoader()];
		}

		if($this->classLoaders === null){
			$this->classLoaders = new \Threaded();
		}else{
			foreach($this->classLoaders as $k => $autoloader){
				unset($this->classLoaders[$k]);
			}
		}
		foreach($autoloaders as $autoloader){
			$this->classLoaders[] = $autoloader;
		}
	}

	/**
	 * Registers the class loaders for this thread.
	 *
	 * WARNING: This method MUST be called from any descendent threads' run() method to make autoloading usable.
	 * If you do not do this, you will not be able to use new classes that were not loaded when the thread was started
	 * (unless you are using a custom autoloader).
	 */
	public function registerClassLoaders() : void{
		if($this->composerAutoloaderPath !== null){
			require $this->composerAutoloaderPath;
		}
		$autoloaders = $this->classLoaders;
		if($autoloaders !== null){
			foreach($autoloaders as $autoloader){
				/** @var \ClassLoader $autoloader */
				$autoloader->register(false);
			}
		}
	}

	final public function run() : void{
		error_reporting(-1);
		$this->registerClassLoaders();
		//set this after the autoloader is registered
		ErrorToExceptionHandler::set();
		$this->onRun();
	}

	/**
	 * Runs code on the thread.
	 */
	abstract protected function onRun() : void;

	public function getThreadName() : string{
		return (new \ReflectionClass($this))->getShortName();
	}
}
