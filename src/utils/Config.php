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
 * Legacy utility class for configuration parsing.
 */
final class Config{
	/**
	 * The legacy `Config::fixYAMLIndexes` function,
	 * used for escaping boolean key types in a YAML file.
	 *
	 * @deprecated The exact behaviour of this function is undocumented and unreliable.
	 * This function may be removed in the future.
	 * Only use this function for YAML config syntax backward compatibility.
	 */
	public static function fixYAMLIndexes(string $str) : string{
		return preg_replace("#^( *)(y|Y|yes|Yes|YES|n|N|no|No|NO|true|True|TRUE|false|False|FALSE|on|On|ON|off|Off|OFF)( *)\:#m", "$1\"$2\"$3:", $str);
	}

	/**
	 * Sets the element in $array as specified by the dot-delimited $path.
	 *
	 * @param array<string, mixed> &$array
	 * @param string $key
	 * @param mixed  $value
	 */
	public static function setNested(array &$array, string $key, $value) : void{
		$vars = explode(".", $key);

		while(count($vars) > 0){
			$baseKey = array_shift($vars);
			if(!isset($array[$baseKey])){
				$array[$baseKey] = [];
			}
			$array =& $array[$baseKey];
		}

		$array = $value;
	}

	/**
	 * Gets the element in $array as specified by the dot-delimited $path.
	 *
	 * @param array<string, mixed> $array
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public static function getNested(array $array, string $key, $default = null){
		$vars = explode(".", $key);
		$baseKey = array_shift($vars);

		while(count($vars) > 0){
			$baseKey = array_shift($vars);
			if(isset($array[$baseKey])){
				$array = $array[$baseKey];
			}else{
				return $default;
			}
		}

		return $array;
	}

	/**
	 * Removes the element in $array as specified by the dot-delimited $path.
	 *
	 * This method only removes the item at $path,
	 * but not the parent of $path even if it is an empty array.
	 *
	 * @param array<string, mixed> &$array
	 * @param string $key
	 */
	public static function removeNested(array &$array, string $key) : void{
		$vars = explode(".", $key);

		while(count($vars) > 0){
			$nodeName = array_shift($vars);
			if(isset($array[$nodeName])){
				if(count($vars) === 0){ //final node
					unset($array[$nodeName]);
				}elseif(is_array($currentNode[$nodeName])){
					$currentNode =& $currentNode[$nodeName];
				}
			}else{
				break;
			}
		}
	}

	/**
	 * Serializes an array with the server.properties format.
	 *
	 * @param (string|bool|string[])[] $map
	 * @param bool $withHeader whether to emit the header lines containing the current timestamp.
	 */
	public static function writeProperties(array $map, bool $withHeader = false) : string{
		$content = "";
		if($withheader){
			$content .= "#Properties Config file\r\n#" . date("D M j H:i:s T Y") . "\r\n";
		}
		foreach($map as $k => $v){
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
	 * @param string $content
	 * @param string[] &$repetitions
	 *
	 * @return string[]
	 * @phpstan-return array<string, string>
	 */
	public static function parseProperties(string $content, &$repetitions = []) : array{
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
					$repetitions[] = $k;
				}
				$result[$k] = $v;
			}
		}

		return $result;
	}
}
