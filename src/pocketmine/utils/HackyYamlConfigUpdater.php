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

class HackyYamlConfigUpdater{

	/**
	 * @var string
	 */
	private $inputConfigPath;
	/**
	 * @var string
	 */
	private $newConfigTemplatePath;
	/**
	 * @var string
	 */
	private $outputConfigPath;

	public function __construct(string $inputConfigPath, string $newConfigTemplatePath, string $outputConfigPath = null){
		$this->inputConfigPath = $inputConfigPath;
		$this->newConfigTemplatePath = $newConfigTemplatePath;
		$this->outputConfigPath = $outputConfigPath ?? $inputConfigPath;
	}

	public function getBackupPath() : string{
		return $this->inputConfigPath . ".bak";
	}

	public function process() : bool{
		$inputConfig = new Config($this->inputConfigPath, Config::YAML);
		$template = new Config($this->newConfigTemplatePath, Config::YAML);

		if($template->get("config-version", 0) <= $inputConfig->get("config-version", 0)){
			return false;
		}
		$inputConfig->remove("config-version"); //don't overwrite it in the new file

		copy($this->inputConfigPath, $this->getBackupPath());


		//Convert comments into YAMl keys, and save their contents
		$commentCounter = 0;
		$savedComments = [];

		$result = preg_replace_callback('/( *)#(.*)/m', function($matches) use (&$commentCounter, &$savedComments){
			$commentCounter++;
			$savedComments[$commentCounter] = rtrim($matches[2], "\r\n");
			return $matches[1] . "comment$commentCounter: $commentCounter";
		}, file_get_contents($this->newConfigTemplatePath));

		file_put_contents($this->outputConfigPath . ".temp", $result);

		//Copy relevant config values from the old config to the new one
		$outputConfig = new Config($this->outputConfigPath . ".temp", Config::YAML);
		$this->copyConfigValues("", $inputConfig->getAll(), $outputConfig, ["worlds", "aliases"]);
		$outputConfig->save();

		$done = preg_replace_callback('/^(\s*)comment[0-9:]+ ([0-9]+)/ms', function($matches) use (&$savedComments){
			return $matches[1] . "#" . $savedComments[(int) $matches[2]];
		}, file_get_contents($this->outputConfigPath . ".temp"));

		$done = preg_replace_callback('/^[A-Za-z0-9]+/m', function($matches){
			return PHP_EOL . $matches[0];
		}, $done);

		file_put_contents($this->outputConfigPath, $done);
		unlink($this->outputConfigPath . ".temp");
		return true;
	}

	private function copyConfigValues(string $currentKey, array $old, Config $newConfig, array $dontRemove = []){
		foreach($old as $k => $v){
			$key = $currentKey . $k;
			if(is_array($v) and count($v) > 0){
				$this->copyConfigValues($key . ".", $v, $newConfig, $dontRemove);
			}else{
				$s = strpos($key, '.');
				if($s !== false){
					$root = substr($key, 0, $s);
				}else{
					$root = $key;
				}

				if(isset($dontRemove[$root]) or ($v !== null and $newConfig->getNested($key) !== null)){
					$newConfig->setNested($key, $v);
				}
			}
		}
	}
}