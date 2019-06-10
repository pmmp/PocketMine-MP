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

use ClassLoader;
use function class_exists;
use function glob;
use function is_a;
use function is_file;
use function realpath;

/**
 * Handles different types of plugins
 */
final class PharPluginLoader{
	/**
	 * Load script plugins in the directory $dir into the provided PluginManager
	 *
	 * @param PluginManager $manager
	 * @param ClassLoader   $classLoader
	 * @param string        $dir
	 */
	public static function scanPlugins(PluginManager $manager, ClassLoader $classLoader, string $dir) : void{
		foreach(glob("$dir/*.phar") as $file){
			if(is_file($file)){
				self::loadPlugin($manager, $classLoader, $file);
			}
		}
	}

	public static function loadPlugin(PluginManager $manager, ClassLoader $classLoader, string $file) : bool{
		$description = self::getPluginDescription($file);
		if($description === null){
			return false;
		}
		$file = "phar://" . realpath($file);

		self::loadClassical($manager, $classLoader, $file, $description);
		return true;
	}

	/**
	 * Gets the PluginDescription from the file
	 *
	 * @param string $file
	 *
	 * @return null|PluginDescription
	 */
	public static function getPluginDescription(string $file) : ?PluginDescription{
		$phar = new \Phar($file);
		if(isset($phar["plugin.yml"])){
			$pluginYml = $phar["plugin.yml"];
			if($pluginYml instanceof \PharFileInfo){
				return new PluginDescription($pluginYml->getContent());
			}
		}

		return null;
	}

	/**
	 * @param PluginManager     $manager
	 * @param ClassLoader       $classLoader
	 * @param string            $file
	 * @param PluginDescription $description
	 */
	public static function loadClassical(PluginManager $manager, ClassLoader $classLoader, string $file, PluginDescription $description) : void{
		$dataFolder = $manager->getDataDirectory($file, $description->getName());
		$manager->loadPlugin($description, $dataFolder, function(PluginDescription $description, string $dataFolder) use ($classLoader, $manager, $file){
			$classLoader->addPath("$file/src");

			$main = $description->getMain();
			if(!class_exists($main)){
				$manager->getServer()->getLogger()->error("Main class for plugin " . $description->getName() . " not found");
				return null;
			}
			if(!is_a($main, PluginBase::class, true)){
				throw new PluginException("$main does not extend " . PluginBase::class);
			}

			$resourceProvider = new DiskResourceProvider($file);
			/** @var PluginBase $ret */
			$ret = new $main($manager->getServer(), $description, $dataFolder, self::class, $resourceProvider, $file);
			return $ret;
		});
	}
}
