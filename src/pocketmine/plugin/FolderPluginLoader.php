<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\plugin;

class FolderPluginLoader implements PluginLoader{

	/** @var \ClassLoader */
	private $loader;

	public function __construct(\ClassLoader $loader){
		$this->loader = $loader;
	}

	public function canLoadPlugin(string $path) : bool{
		return is_dir($path) and file_exists($path . "/plugin.yml") and file_exists($path . "/src/");
	}

	/**
	 * Loads the plugin contained in $file
	 *
	 * @param string $file
	 */
	public function loadPlugin(string $file) : void{
		$this->loader->addPath("$file/src");
	}

	/**
	 * Gets the PluginDescription from the file
	 *
	 * @param string $file
	 *
	 * @return null|PluginDescription
	 */
	public function getPluginDescription(string $file) : ?PluginDescription{
		if(is_dir($file) and file_exists($file . "/plugin.yml")){
			$yaml = @file_get_contents($file . "/plugin.yml");
			if($yaml != ""){
				return new PluginDescription($yaml);
			}
		}

		return null;
	}

	public function getAccessProtocol() : string{
		return "";
	}
}