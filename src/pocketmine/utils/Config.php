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

use function array_change_key_case;
use function array_keys;
use function array_pop;
use function array_shift;
use function basename;
use function count;
use function date;
use function explode;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function implode;
use function is_array;
use function is_bool;
use function json_decode;
use function json_encode;
use function preg_match_all;
use function preg_replace;
use function serialize;
use function str_replace;
use function strlen;
use function strtolower;
use function substr;
use function trim;
use function unserialize;
use function yaml_emit;
use function yaml_parse;
use const CASE_LOWER;
use const JSON_BIGINT_AS_STRING;
use const JSON_PRETTY_PRINT;

/**
 * Config Class for simple config manipulation of multiple formats.
 */
class Config{
	public const DETECT = -1; //Detect by file extension
	public const PROPERTIES = 0; // .properties
	public const CNF = Config::PROPERTIES; // .cnf
	public const JSON = 1; // .js, .json
	public const YAML = 2; // .yml, .yaml
	//const EXPORT = 3; // .export, .xport
	public const SERIALIZED = 4; // .sl
	public const ENUM = 5; // .txt, .list, .enum
	public const ENUMERATION = Config::ENUM;

	/**
	 * @var mixed[]
	 * @phpstan-var array<string, mixed>
	 */
	private $config = [];

	/**
	 * @var mixed[]
	 * @phpstan-var array<string, mixed>
	 */
	private $nestedCache = [];

	/** @var string */
	private $file;
	/** @var bool */
	private $correct = false;
	/** @var int */
	private $type = Config::DETECT;
	/** @var int */
	private $jsonOptions = JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING;

	/** @var bool */
	private $changed = false;

	/** @var int[] */
	public static $formats = [
		"properties" => Config::PROPERTIES,
		"cnf" => Config::CNF,
		"conf" => Config::CNF,
		"config" => Config::CNF,
		"json" => Config::JSON,
		"js" => Config::JSON,
		"yml" => Config::YAML,
		"yaml" => Config::YAML,
		//"export" => Config::EXPORT,
		//"xport" => Config::EXPORT,
		"sl" => Config::SERIALIZED,
		"serialize" => Config::SERIALIZED,
		"txt" => Config::ENUM,
		"list" => Config::ENUM,
		"enum" => Config::ENUM
	];

	/**
	 * @param string  $file    Path of the file to be loaded
	 * @param int     $type    Config type to load, -1 by default (detect)
	 * @param mixed[] $default Array with the default values that will be written to the file if it did not exist
	 * @param null    $correct reference parameter, Sets correct to true if everything has been loaded correctly
	 * @phpstan-param array<string, mixed> $default
	 */
	public function __construct(string $file, int $type = Config::DETECT, array $default = [], &$correct = null){
		$this->load($file, $type, $default);
		$correct = $this->correct;
	}

	/**
	 * Removes all the changes in memory and loads the file again
	 *
	 * @return void
	 */
	public function reload(){
		$this->config = [];
		$this->nestedCache = [];
		$this->correct = false;
		$this->load($this->file, $this->type);
	}

	public function hasChanged() : bool{
		return $this->changed;
	}

	public function setChanged(bool $changed = true) : void{
		$this->changed = $changed;
	}

	public static function fixYAMLIndexes(string $str) : string{
		return preg_replace("#^( *)(y|Y|yes|Yes|YES|n|N|no|No|NO|true|True|TRUE|false|False|FALSE|on|On|ON|off|Off|OFF)( *)\:#m", "$1\"$2\"$3:", $str);
	}

	/**
	 * @param mixed[] $default
	 * @phpstan-param array<string, mixed> $default
	 */
	public function load(string $file, int $type = Config::DETECT, array $default = []) : bool{
		$this->correct = true;
		$this->file = $file;

		$this->type = $type;
		if($this->type === Config::DETECT){
			$extension = explode(".", basename($this->file));
			$extension = strtolower(trim(array_pop($extension)));
			if(isset(Config::$formats[$extension])){
				$this->type = Config::$formats[$extension];
			}else{
				$this->correct = false;
			}
		}

		if(!file_exists($file)){
			$this->config = $default;
			$this->save();
		}else{
			if($this->correct){
				$content = file_get_contents($this->file);
				if($content === false){
					$this->correct = false;
					return false;
				}
				$config = null;
				switch($this->type){
					case Config::PROPERTIES:
						$config = $this->parseProperties($content);
						break;
					case Config::JSON:
						$config = json_decode($content, true);
						break;
					case Config::YAML:
						$content = self::fixYAMLIndexes($content);
						$config = yaml_parse($content);
						break;
					case Config::SERIALIZED:
						$config = unserialize($content);
						break;
					case Config::ENUM:
						$config = self::parseList($content);
						break;
					default:
						$this->correct = false;

						return false;
				}
				$this->config = is_array($config) ? $config : $default;
				if($this->fillDefaults($default, $this->config) > 0){
					$this->save();
				}
			}else{
				return false;
			}
		}

		return true;
	}

	public function check() : bool{
		return $this->correct;
	}

	public function save() : bool{
		if($this->correct){
			$content = null;
			switch($this->type){
				case Config::PROPERTIES:
					$content = $this->writeProperties();
					break;
				case Config::JSON:
					$content = json_encode($this->config, $this->jsonOptions);
					break;
				case Config::YAML:
					$content = yaml_emit($this->config, YAML_UTF8_ENCODING);
					break;
				case Config::SERIALIZED:
					$content = serialize($this->config);
					break;
				case Config::ENUM:
					$content = implode("\r\n", array_keys($this->config));
					break;
				default:
					throw new \InvalidStateException("Config type is unknown, has not been set or not detected");
			}

			file_put_contents($this->file, $content);

			$this->changed = false;

			return true;
		}else{
			return false;
		}
	}

	/**
	 * Returns the path of the config.
	 */
	public function getPath() : string{
		return $this->file;
	}

	/**
	 * Sets the options for the JSON encoding when saving
	 *
	 * @return Config $this
	 * @throws \RuntimeException if the Config is not in JSON
	 * @see json_encode
	 */
	public function setJsonOptions(int $options) : Config{
		if($this->type !== Config::JSON){
			throw new \RuntimeException("Attempt to set JSON options for non-JSON config");
		}
		$this->jsonOptions = $options;
		$this->changed = true;

		return $this;
	}

	/**
	 * Enables the given option in addition to the currently set JSON options
	 *
	 * @return Config $this
	 * @throws \RuntimeException if the Config is not in JSON
	 * @see json_encode
	 */
	public function enableJsonOption(int $option) : Config{
		if($this->type !== Config::JSON){
			throw new \RuntimeException("Attempt to enable JSON option for non-JSON config");
		}
		$this->jsonOptions |= $option;
		$this->changed = true;

		return $this;
	}

	/**
	 * Disables the given option for the JSON encoding when saving
	 *
	 * @return Config $this
	 * @throws \RuntimeException if the Config is not in JSON
	 * @see json_encode
	 */
	public function disableJsonOption(int $option) : Config{
		if($this->type !== Config::JSON){
			throw new \RuntimeException("Attempt to disable JSON option for non-JSON config");
		}
		$this->jsonOptions &= ~$option;
		$this->changed = true;

		return $this;
	}

	/**
	 * Returns the options for the JSON encoding when saving
	 *
	 * @throws \RuntimeException if the Config is not in JSON
	 * @see json_encode
	 */
	public function getJsonOptions() : int{
		if($this->type !== Config::JSON){
			throw new \RuntimeException("Attempt to get JSON options for non-JSON config");
		}
		return $this->jsonOptions;
	}

	/**
	 * @param string $k
	 *
	 * @return bool|mixed
	 */
	public function __get($k){
		return $this->get($k);
	}

	/**
	 * @param string $k
	 * @param mixed  $v
	 *
	 * @return void
	 */
	public function __set($k, $v){
		$this->set($k, $v);
	}

	/**
	 * @param string $k
	 *
	 * @return bool
	 */
	public function __isset($k){
		return $this->exists($k);
	}

	/**
	 * @param string $k
	 */
	public function __unset($k){
		$this->remove($k);
	}

	/**
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return void
	 */
	public function setNested($key, $value){
		$vars = explode(".", $key);
		$base = array_shift($vars);

		if(!isset($this->config[$base])){
			$this->config[$base] = [];
		}

		$base =& $this->config[$base];

		while(count($vars) > 0){
			$baseKey = array_shift($vars);
			if(!isset($base[$baseKey])){
				$base[$baseKey] = [];
			}
			$base =& $base[$baseKey];
		}

		$base = $value;
		$this->nestedCache = [];
		$this->changed = true;
	}

	/**
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	public function getNested($key, $default = null){
		if(isset($this->nestedCache[$key])){
			return $this->nestedCache[$key];
		}

		$vars = explode(".", $key);
		$base = array_shift($vars);
		if(isset($this->config[$base])){
			$base = $this->config[$base];
		}else{
			return $default;
		}

		while(count($vars) > 0){
			$baseKey = array_shift($vars);
			if(is_array($base) and isset($base[$baseKey])){
				$base = $base[$baseKey];
			}else{
				return $default;
			}
		}

		return $this->nestedCache[$key] = $base;
	}

	public function removeNested(string $key) : void{
		$this->nestedCache = [];
		$this->changed = true;

		$vars = explode(".", $key);

		$currentNode =& $this->config;
		while(count($vars) > 0){
			$nodeName = array_shift($vars);
			if(isset($currentNode[$nodeName])){
				if(count($vars) === 0){ //final node
					unset($currentNode[$nodeName]);
				}elseif(is_array($currentNode[$nodeName])){
					$currentNode =& $currentNode[$nodeName];
				}
			}else{
				break;
			}
		}
	}

	/**
	 * @param string $k
	 * @param mixed  $default
	 *
	 * @return bool|mixed
	 */
	public function get($k, $default = false){
		return ($this->correct and isset($this->config[$k])) ? $this->config[$k] : $default;
	}

	/**
	 * @param string $k key to be set
	 * @param mixed  $v value to set key
	 *
	 * @return void
	 */
	public function set($k, $v = true){
		$this->config[$k] = $v;
		$this->changed = true;
		foreach($this->nestedCache as $nestedKey => $nvalue){
			if(substr($nestedKey, 0, strlen($k) + 1) === ($k . ".")){
				unset($this->nestedCache[$nestedKey]);
			}
		}
	}

	/**
	 * @param mixed[] $v
	 * @phpstan-param array<string, mixed> $v
	 *
	 * @return void
	 */
	public function setAll(array $v){
		$this->config = $v;
		$this->changed = true;
	}

	/**
	 * @param string $k
	 * @param bool   $lowercase If set, searches Config in single-case / lowercase.
	 */
	public function exists($k, bool $lowercase = false) : bool{
		if($lowercase){
			$k = strtolower($k); //Convert requested  key to lower
			$array = array_change_key_case($this->config, CASE_LOWER); //Change all keys in array to lower
			return isset($array[$k]); //Find $k in modified array
		}else{
			return isset($this->config[$k]);
		}
	}

	/**
	 * @param string $k
	 *
	 * @return void
	 */
	public function remove($k){
		unset($this->config[$k]);
		$this->changed = true;
	}

	/**
	 * @return mixed[]
	 * @phpstan-return list<string>|array<string, mixed>
	 */
	public function getAll(bool $keys = false) : array{
		return ($keys ? array_keys($this->config) : $this->config);
	}

	/**
	 * @param mixed[] $defaults
	 * @phpstan-param array<string, mixed> $defaults
	 *
	 * @return void
	 */
	public function setDefaults(array $defaults){
		$this->fillDefaults($defaults, $this->config);
	}

	/**
	 * @param mixed[] $default
	 * @param mixed[] $data reference parameter
	 * @phpstan-param array<string, mixed> $default
	 * @phpstan-param array<string, mixed> $data
	 */
	private function fillDefaults(array $default, &$data) : int{
		$changed = 0;
		foreach($default as $k => $v){
			if(is_array($v)){
				if(!isset($data[$k]) or !is_array($data[$k])){
					$data[$k] = [];
				}
				$changed += $this->fillDefaults($v, $data[$k]);
			}elseif(!isset($data[$k])){
				$data[$k] = $v;
				++$changed;
			}
		}

		if($changed > 0){
			$this->changed = true;
		}

		return $changed;
	}

	/**
	 * @return true[]
	 * @phpstan-return array<string, true>
	 */
	private static function parseList(string $content) : array{
		$result = [];
		foreach(explode("\n", trim(str_replace("\r\n", "\n", $content))) as $v){
			$v = trim($v);
			if($v == ""){
				continue;
			}
			$result[$v] = true;
		}
		return $result;
	}

	private function writeProperties() : string{
		$content = "#Properties Config file\r\n#" . date("D M j H:i:s T Y") . "\r\n";
		foreach($this->config as $k => $v){
			if(is_bool($v)){
				$v = $v ? "on" : "off";
			}elseif(is_array($v)){
				$v = implode(";", $v);
			}
			$content .= $k . "=" . $v . "\r\n";
		}

		return $content;
	}

	/**
	 * @return mixed[]
	 */
	private function parseProperties(string $content) : array{
		$result = [];
		if(preg_match_all('/^\s*([a-zA-Z0-9\-_\.]+)[ \t]*=([^\r\n]*)/um', $content, $matches) > 0){ //false or 0 matches
			foreach($matches[1] as $i => $k){
				$v = trim($matches[2][$i]);
				switch(strtolower($v)){
					case "on":
					case "true":
					case "yes":
						$v = true;
						break;
					case "off":
					case "false":
					case "no":
						$v = false;
						break;
				}
				if(isset($result[$k])){
					MainLogger::getLogger()->debug("[Config] Repeated property " . $k . " on file " . $this->file);
				}
				$result[$k] = $v;
			}
		}

		return $result;
	}
}
