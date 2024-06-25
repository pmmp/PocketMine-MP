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

use pocketmine\errorhandler\ErrorToExceptionHandler;
use Symfony\Component\Filesystem\Path;
use function array_change_key_case;
use function array_fill_keys;
use function array_keys;
use function array_shift;
use function count;
use function date;
use function explode;
use function file_exists;
use function get_debug_type;
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
use const JSON_THROW_ON_ERROR;
use const YAML_UTF8_ENCODING;

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
	private array $config = [];

	/**
	 * @var mixed[]
	 * @phpstan-var array<string, mixed>
	 */
	private array $nestedCache = [];

	private string $file;
	private int $type = Config::DETECT;
	private int $jsonOptions = JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING;

	private bool $changed = false;

	/** @var int[] */
	public static array $formats = [
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
	 * @phpstan-param array<string, mixed> $default
	 */
	public function __construct(string $file, int $type = Config::DETECT, array $default = []){
		$this->load($file, $type, $default);
	}

	/**
	 * Removes all the changes in memory and loads the file again
	 */
	public function reload() : void{
		$this->config = [];
		$this->nestedCache = [];
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
	 *
	 * @throws \InvalidArgumentException if config type is invalid or could not be auto-detected
	 */
	private function load(string $file, int $type = Config::DETECT, array $default = []) : void{
		$this->file = $file;

		$this->type = $type;
		if($this->type === Config::DETECT){
			$extension = strtolower(Path::getExtension($this->file));
			if(isset(Config::$formats[$extension])){
				$this->type = Config::$formats[$extension];
			}else{
				throw new \InvalidArgumentException("Cannot detect config type of " . $this->file);
			}
		}

		if(!file_exists($file)){
			$this->config = $default;
			$this->save();
		}else{
			$content = Filesystem::fileGetContents($this->file);
			switch($this->type){
				case Config::PROPERTIES:
					$config = self::parseProperties($content);
					break;
				case Config::JSON:
					try{
						$config = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
					}catch(\JsonException $e){
						throw ConfigLoadException::wrap($this->file, $e);
					}
					break;
				case Config::YAML:
					$content = self::fixYAMLIndexes($content);
					try{
						$config = ErrorToExceptionHandler::trap(fn() => yaml_parse($content));
					}catch(\ErrorException $e){
						throw ConfigLoadException::wrap($this->file, $e);
					}
					break;
				case Config::SERIALIZED:
					try{
						$config = ErrorToExceptionHandler::trap(fn() => unserialize($content));
					}catch(\ErrorException $e){
						throw ConfigLoadException::wrap($this->file, $e);
					}
					break;
				case Config::ENUM:
					$config = array_fill_keys(self::parseList($content), true);
					break;
				default:
					throw new \InvalidArgumentException("Invalid config type specified");
			}
			if(!is_array($config)){
				throw new ConfigLoadException("Failed to load config $this->file: Expected array for base type, but got " . get_debug_type($config));
			}
			$this->config = $config;
			if($this->fillDefaults($default, $this->config) > 0){
				$this->save();
			}
		}
	}

	/**
	 * Returns the path of the config.
	 */
	public function getPath() : string{
		return $this->file;
	}

	/**
	 * Flushes the config to disk in the appropriate format.
	 */
	public function save() : void{
		$content = null;
		switch($this->type){
			case Config::PROPERTIES:
				$content = self::writeProperties($this->config);
				break;
			case Config::JSON:
				$content = json_encode($this->config, $this->jsonOptions | JSON_THROW_ON_ERROR);
				break;
			case Config::YAML:
				$content = yaml_emit($this->config, YAML_UTF8_ENCODING);
				break;
			case Config::SERIALIZED:
				$content = serialize($this->config);
				break;
			case Config::ENUM:
				$content = self::writeList(array_keys($this->config));
				break;
			default:
				throw new AssumptionFailedError("Config type is unknown, has not been set or not detected");
		}

		Filesystem::safeFilePutContents($this->file, $content);

		$this->changed = false;
	}

	/**
	 * Sets the options for the JSON encoding when saving
	 *
	 * @return $this
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
	 * @return $this
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
	 * @return $this
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
	 */
	public function __set($k, $v) : void{
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

	public function setNested(string $key, mixed $value) : void{
		$vars = explode(".", $key);
		$base = array_shift($vars);

		if(!isset($this->config[$base])){
			$this->config[$base] = [];
		}

		$base = &$this->config[$base];

		while(count($vars) > 0){
			$baseKey = array_shift($vars);
			if(!isset($base[$baseKey])){
				$base[$baseKey] = [];
			}
			$base = &$base[$baseKey];
		}

		$base = $value;
		$this->nestedCache = [];
		$this->changed = true;
	}

	public function getNested(string $key, mixed $default = null) : mixed{
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
			if(is_array($base) && isset($base[$baseKey])){
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

		$currentNode = &$this->config;
		while(count($vars) > 0){
			$nodeName = array_shift($vars);
			if(isset($currentNode[$nodeName])){
				if(count($vars) === 0){ //final node
					unset($currentNode[$nodeName]);
				}elseif(is_array($currentNode[$nodeName])){
					$currentNode = &$currentNode[$nodeName];
				}
			}else{
				break;
			}
		}
	}

	public function get(string $k, mixed $default = false) : mixed{
		return $this->config[$k] ?? $default;
	}

	public function set(string $k, mixed $v = true) : void{
		$this->config[$k] = $v;
		$this->changed = true;
		foreach(Utils::stringifyKeys($this->nestedCache) as $nestedKey => $nvalue){
			if(substr($nestedKey, 0, strlen($k) + 1) === ($k . ".")){
				unset($this->nestedCache[$nestedKey]);
			}
		}
	}

	/**
	 * @param mixed[] $v
	 * @phpstan-param array<string, mixed> $v
	 */
	public function setAll(array $v) : void{
		$this->config = $v;
		$this->changed = true;
	}

	/**
	 * @param bool $lowercase If set, searches Config in single-case / lowercase.
	 */
	public function exists(string $k, bool $lowercase = false) : bool{
		if($lowercase){
			$k = strtolower($k); //Convert requested  key to lower
			$array = array_change_key_case($this->config, CASE_LOWER); //Change all keys in array to lower
			return isset($array[$k]); //Find $k in modified array
		}else{
			return isset($this->config[$k]);
		}
	}

	public function remove(string $k) : void{
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
	 */
	public function setDefaults(array $defaults) : void{
		$this->fillDefaults($defaults, $this->config);
	}

	/**
	 * @param mixed[] $default
	 * @param mixed[] $data    reference parameter
	 * @phpstan-param array<string, mixed> $default
	 * @phpstan-param array<string, mixed> $data
	 * @phpstan-param-out array<string, mixed> $data
	 */
	private function fillDefaults(array $default, array &$data) : int{
		$changed = 0;
		foreach(Utils::stringifyKeys($default) as $k => $v){
			if(is_array($v)){
				if(!isset($data[$k]) || !is_array($data[$k])){
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
	 * @return string[]
	 * @phpstan-return list<string>
	 */
	public static function parseList(string $content) : array{
		$result = [];
		foreach(explode("\n", trim(str_replace("\r\n", "\n", $content))) as $v){
			$v = trim($v);
			if($v === ""){
				continue;
			}
			$result[] = $v;
		}
		return $result;
	}

	/**
	 * @param string[] $entries
	 * @phpstan-param list<string> $entries
	 */
	public static function writeList(array $entries) : string{
		return implode("\n", $entries);
	}

	/**
	 * @param string[]|int[]|float[]|bool[] $config
	 * @phpstan-param array<string, string|int|float|bool> $config
	 */
	public static function writeProperties(array $config) : string{
		$content = "#Properties Config file\r\n#" . date("D M j H:i:s T Y") . "\r\n";
		foreach(Utils::stringifyKeys($config) as $k => $v){
			if(is_bool($v)){
				$v = $v ? "on" : "off";
			}
			$content .= $k . "=" . $v . "\r\n";
		}

		return $content;
	}

	/**
	 * @return string[]|int[]|float[]|bool[]
	 * @phpstan-return array<string, string|int|float|bool>
	 */
	public static function parseProperties(string $content) : array{
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
					default:
						$v = match($v){
							(string) ((int) $v) => (int) $v,
							(string) ((float) $v) => (float) $v,
							default => $v,
						};
						break;
				}
				$result[(string) $k] = $v;
			}
		}

		return $result;
	}
}
