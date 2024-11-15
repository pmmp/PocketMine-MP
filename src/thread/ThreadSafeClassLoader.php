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

use pmmp\thread\ThreadSafe;
use pmmp\thread\ThreadSafeArray;
use function class_exists;
use function count;
use function explode;
use function file_exists;
use function interface_exists;
use function method_exists;
use function spl_autoload_register;
use function str_replace;
use function strrpos;
use function substr;
use function trait_exists;
use function trim;
use const DIRECTORY_SEPARATOR;

/**
 * This autoloader can be used and updated from multiple threads.
 * Useful if classes need to be dynamically added after threads have already been started.
 *
 * This is used to facilitate loading plugin classes, enabling plugins to be loaded after the server has started.
 */
class ThreadSafeClassLoader extends ThreadSafe{

	/**
	 * @var ThreadSafeArray|string[]
	 * @phpstan-var ThreadSafeArray<int, string>
	 */
	private ThreadSafeArray $fallbackLookup;
	/**
	 * @var ThreadSafeArray|string[][]
	 * @phpstan-var ThreadSafeArray<string, ThreadSafeArray<int, string>>
	 */
	private ThreadSafeArray $psr4Lookup;

	public function __construct(){
		$this->fallbackLookup = new ThreadSafeArray();
		$this->psr4Lookup = new ThreadSafeArray();
	}

	protected function normalizePath(string $path) : string{
		$parts = explode("://", $path, 2);
		if(count($parts) === 2){
			return $parts[0] . "://" . str_replace('/', DIRECTORY_SEPARATOR, $parts[1]);
		}
		return str_replace('/', DIRECTORY_SEPARATOR, $parts[0]);
	}

	public function addPath(string $namespacePrefix, string $path, bool $prepend = false) : void{
		$path = $this->normalizePath($path);
		if($namespacePrefix === '' || $namespacePrefix === '\\'){
			$this->fallbackLookup->synchronized(function() use ($path, $prepend) : void{
				$this->appendOrPrependLookupEntry($this->fallbackLookup, $path, $prepend);
			});
		}else{
			$namespacePrefix = trim($namespacePrefix, '\\') . '\\';
			$this->psr4Lookup->synchronized(function() use ($namespacePrefix, $path, $prepend) : void{
				$list = $this->psr4Lookup[$namespacePrefix] ?? null;
				if($list === null){
					$list = $this->psr4Lookup[$namespacePrefix] = new ThreadSafeArray();
				}
				$this->appendOrPrependLookupEntry($list, $path, $prepend);
			});
		}
	}

	/**
	 * @phpstan-param ThreadSafeArray<int, string> $list
	 */
	protected function appendOrPrependLookupEntry(ThreadSafeArray $list, string $entry, bool $prepend) : void{
		if($prepend){
			$entries = $this->getAndRemoveLookupEntries($list);
			$list[] = $entry;
			foreach($entries as $removedEntry){
				$list[] = $removedEntry;
			}
		}else{
			$list[] = $entry;
		}
	}

	/**
	 * @return string[]
	 *
	 * @phpstan-param ThreadSafeArray<int, string> $list
	 * @phpstan-return list<string>
	 */
	protected function getAndRemoveLookupEntries(ThreadSafeArray $list) : array{
		$entries = [];
		while(($entry = $list->shift()) !== null){
			$entries[] = $entry;
		}
		return $entries;
	}

	public function register(bool $prepend = false) : bool{
		return spl_autoload_register(function(string $name) : void{
			$this->loadClass($name);
		}, true, $prepend);
	}

	/**
	 * Called when there is a class to load
	 */
	public function loadClass(string $name) : bool{
		$path = $this->findClass($name);
		if($path !== null){
			include($path);
			if(!class_exists($name, false) && !interface_exists($name, false) && !trait_exists($name, false)){
				return false;
			}

			if(method_exists($name, "onClassLoaded") && (new \ReflectionClass($name))->getMethod("onClassLoaded")->isStatic()){
				$name::onClassLoaded();
			}

			return true;
		}

		return false;
	}

	/**
	 * Returns the path for the class, if any
	 */
	public function findClass(string $name) : ?string{
		$baseName = str_replace("\\", DIRECTORY_SEPARATOR, $name);

		foreach($this->fallbackLookup as $path){
			$filename = $path . DIRECTORY_SEPARATOR . $baseName . ".php";
			if(file_exists($filename)){
				return $filename;
			}
		}

		// PSR-4 lookup
		$logicalPathPsr4 = $baseName . ".php";

		return $this->psr4Lookup->synchronized(function() use ($name, $logicalPathPsr4) : ?string{
			$subPath = $name;
			while(false !== $lastPos = strrpos($subPath, '\\')){
				$subPath = substr($subPath, 0, $lastPos);
				$search = $subPath . '\\';
				$lookup = $this->psr4Lookup[$search] ?? null;
				if($lookup !== null){
					$pathEnd = DIRECTORY_SEPARATOR . substr($logicalPathPsr4, $lastPos + 1);
					foreach($lookup as $dir){
						if(file_exists($file = $dir . $pathEnd)){
							return $file;
						}
					}
				}
			}
			return null;
		});
	}
}
