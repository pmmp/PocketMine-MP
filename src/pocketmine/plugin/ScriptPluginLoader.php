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

use function basename;
use function file;
use function is_file;
use function preg_match;
use function strlen;
use function strpos;
use function strtolower;
use function substr;
use function trim;
use const FILE_IGNORE_NEW_LINES;
use const FILE_SKIP_EMPTY_LINES;
use const pocketmine\BASE_VERSION;

/**
 * Simple script loader, not for plugin development
 * For an example see https://gist.github.com/shoghicp/516105d470cf7d140757
 */
class ScriptPluginLoader implements PluginLoader{

	public function canLoadPlugin(string $path) : bool{
		$ext = ".php";
		return is_file($path) and substr($path, -strlen($ext)) === $ext;
	}

	/**
	 * Loads the plugin contained in $file
	 *
	 * @param string $file
	 */
	public function loadPlugin(string $file) : void{
		include_once $file;
	}

	/**
	 * Gets the PluginDescription from the file
	 *
	 * @param string $file
	 *
	 * @return null|PluginDescription
	 */
	public function getPluginDescription(string $file) : ?PluginDescription{
		$data = self::findHeader($file);
		if(isset($data["notscript"])){
			return null;
		}

		$data += [
			"name" => basename($file, ".php"),
			"version" => "1.0.0",
			"api" => BASE_VERSION,
		];

		if(!isset($data["main"])){
			$data["main"] = $main = self::detectMainClass($file);
			if($main === null){
				return null;
			}
		}

		return new PluginDescription($data);
	}

	private static function findHeader(string $file) : array{
		$content = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

		$data = [];

		$insideHeader = false;
		foreach($content as $line){
			if(!$insideHeader and strpos($line, "/**") !== false){
				$insideHeader = true;
			}

			if(preg_match("/^[ \t]+\\*[ \t]+@([a-zA-Z]+)([ \t]+(.*))?$/", $line, $matches) > 0){
				$key = strtolower($matches[1]);
				$content = trim($matches[3] ?? "");

				$data[$key] = $content;
			}

			if($insideHeader and strpos($line, "*/") !== false){
				break;
			}
		}

		return $data;
	}

	private static function detectMainClass(string $file) : ?string{
		// It is possible to use token_get_all() to analyze the file syntactically,
		// but it is usually not necessary, and is too complex for a simple problem.
		$namespace = "";
		foreach(file($file) as $line){
			if(preg_match('/^namespace[ \t]+([A-Za-z0-9_\\]+)/', $line, $match) === 0){
				$namespace = $match[1] . "\\";
				continue;
			}
			if(preg_match('/^class[ \t]+([A_Za-z0-9_]+)[ \t]+extends\b/', $line, $match) === 0){
				return $namespace . $match[1];
			}
		}
		return null;
	}

	public function getAccessProtocol() : string{
		return "";
	}
}
