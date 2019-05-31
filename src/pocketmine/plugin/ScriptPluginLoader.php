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

use function class_exists;
use function file;
use function glob;
use function is_a;
use function is_file;
use function preg_match;
use function realpath;
use function strpos;
use function trim;
use const FILE_IGNORE_NEW_LINES;
use const FILE_SKIP_EMPTY_LINES;

/**
 * Simple script loader, not for plugin development
 * For an example see https://gist.github.com/shoghicp/516105d470cf7d140757
 */
final class ScriptPluginLoader{
	/**
	 * Load script plugins in the directory $dir into the provided PluginManager
	 *
	 * @param PluginManager $manager
	 * @param string        $dir
	 */
	public static function scanPlugins(PluginManager $manager, string $dir) : void{
		foreach(glob("$dir/*.php") as $file){
			if(is_file($file)){
				self::loadPlugin($manager, $file);
			}
		}
	}

	public static function loadPlugin(PluginManager $manager, string $file) : bool{
		$description = self::getPluginDescription($file);
		if($description === null){
			return false;
		}
		$file = realpath($file);

		$dataFolder = $manager->getDataDirectory($file, $description->getName());
		$manager->loadPlugin($description, $dataFolder, function(PluginDescription $description, string $dataFolder) use ($manager, $file){
			include_once $file;

			$main = $description->getMain();
			if(!class_exists($main)){
				$manager->getServer()->getLogger()->error("Main class for plugin " . $description->getName() . " not found");
				return null;
			}
			if(!is_a($main, ScriptPlugin::class, true)){
				throw new PluginException("$main does not extend " . ScriptPlugin::class . ", so it cannot be loaded by ScriptPluginLoader");
			}

			/** @var ScriptPlugin $ret */
			$ret = new $main($manager->getServer(), $description, $dataFolder, self::class);
			return $ret;
		});
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
		$content = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

		$data = [];

		$insideHeader = false;
		foreach($content as $line){
			if(!$insideHeader and strpos($line, "/**") !== false){
				$insideHeader = true;
			}

			if(preg_match("/^[ \t]+\\*[ \t]+@([a-zA-Z]+)([ \t]+(.*))?$/", $line, $matches) > 0){
				$key = $matches[1];
				$content = trim($matches[3] ?? "");

				if($key === "notscript"){
					return null;
				}

				$data[$key] = $content;
			}

			if($insideHeader and strpos($line, "*/") !== false){
				break;
			}
		}
		if($insideHeader){
			return new PluginDescription($data);
		}

		return null;
	}
}
