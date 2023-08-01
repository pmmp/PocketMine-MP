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

use pmmp\thread\ThreadSafeArray;
use pocketmine\errorhandler\ErrorToExceptionHandler;
use pocketmine\Server;
use function error_get_last;
use function error_reporting;
use function register_shutdown_function;
use function set_exception_handler;

trait CommonThreadPartsTrait{
	/**
	 * @var ThreadSafeArray|ThreadSafeClassLoader[]|null
	 * @phpstan-var ThreadSafeArray<int, ThreadSafeClassLoader>|null
	 */
	private ?ThreadSafeArray $classLoaders = null;
	protected ?string $composerAutoloaderPath = null;

	protected bool $isKilled = false;

	private ?ThreadCrashInfo $crashInfo = null;

	/**
	 * @return ThreadSafeClassLoader[]
	 */
	public function getClassLoaders() : ?array{
		return $this->classLoaders !== null ? (array) $this->classLoaders : null;
	}

	/**
	 * @param ThreadSafeClassLoader[] $autoloaders
	 */
	public function setClassLoaders(?array $autoloaders = null) : void{
		$this->composerAutoloaderPath = \pocketmine\COMPOSER_AUTOLOADER_PATH;

		if($autoloaders === null){
			$autoloaders = [Server::getInstance()->getLoader()];
		}

		if($this->classLoaders === null){
			$loaders = $this->classLoaders = new ThreadSafeArray();
		}else{
			$loaders = $this->classLoaders;
			foreach($this->classLoaders as $k => $autoloader){
				unset($this->classLoaders[$k]);
			}
		}
		foreach($autoloaders as $autoloader){
			$loaders[] = $autoloader;
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
				/** @var ThreadSafeClassLoader $autoloader */
				$autoloader->register(false);
			}
		}
	}

	public function getCrashInfo() : ?ThreadCrashInfo{ return $this->crashInfo; }

	final public function run() : void{
		error_reporting(-1);
		$this->registerClassLoaders();
		//set this after the autoloader is registered
		ErrorToExceptionHandler::set();

		//this permits adding extra functionality to the exception and shutdown handlers via overriding
		set_exception_handler($this->onUncaughtException(...));
		register_shutdown_function($this->onShutdown(...));

		$this->onRun();
		$this->isKilled = true;
	}

	/**
	 * Called by set_exception_handler() when an uncaught exception is thrown.
	 */
	protected function onUncaughtException(\Throwable $e) : void{
		$this->synchronized(function() use ($e) : void{
			$this->crashInfo = ThreadCrashInfo::fromThrowable($e, $this->getThreadName());
		});
	}

	/**
	 * Called by register_shutdown_function() when the thread shuts down. This may be because of a benign shutdown, or
	 * because of a fatal error. Use isKilled to determine which.
	 */
	protected function onShutdown() : void{
		$this->synchronized(function() : void{
			if(!$this->isKilled && $this->crashInfo === null){
				$last = error_get_last();
				if($last !== null){
					//fatal error
					$this->crashInfo = ThreadCrashInfo::fromLastErrorInfo($last, $this->getThreadName());
				}else{
					//probably misused exit()
					$this->crashInfo = ThreadCrashInfo::fromThrowable(new \RuntimeException("Thread crashed without an error - perhaps exit() was called?"), $this->getThreadName());
				}
			}
		});
	}

	/**
	 * Runs code on the thread.
	 */
	abstract protected function onRun() : void;

	public function getThreadName() : string{
		return (new \ReflectionClass($this))->getShortName();
	}
}
