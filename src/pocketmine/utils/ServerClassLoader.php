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

namespace pocketmine\utils;

use ClassLoader;
use Composer\Autoload\ClassLoader as ComposerClassLoader;
use jacknoordhuis\Autoload\ThreadedClassLoader;

class ServerClassLoader extends ThreadedClassLoader implements ClassLoader{

	public function addPath($path, $prepend = false){
		$this->add(null, $path, $prepend);
	}

	/**
	 * Creates a new threaded class loader from a default composer class loader.
	 *
	 * Note: This override is here for documentation and code completion purposes only.
	 *
	 * @param \Composer\Autoload\ClassLoader $loader The composer class loader.
	 * @param array $includeFiles Array of files to be included by the loader.
	 * @param bool $register      If the new autoloader should be registered.
	 * @param bool $unregister    If the composer autoloader should be unregistered.
	 *
	 * @return ServerClassLoader|ThreadedClassLoader
	 */
	public static function fromComposerLoader(ComposerClassLoader $loader, array $includeFiles = [], bool $register = true, bool $unregister = true){
		return parent::fromComposerLoader($loader, $includeFiles, $register, $unregister);
	}

}