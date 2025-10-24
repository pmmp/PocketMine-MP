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

namespace pocketmine\build\remove_dead_translations;

use pocketmine\utils\Utils;
use function array_filter;
use function array_values;
use function count;
use function file_get_contents;
use function file_put_contents;
use function fwrite;
use function is_array;
use function is_int;
use function is_string;
use function json_decode;
use function json_encode;
use function parse_ini_file;
use function preg_last_error_msg;
use function preg_quote;
use function preg_replace;
use function scandir;
use function str_ends_with;
use const INI_SCANNER_RAW;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use const STDERR;

require __DIR__ . "/../vendor/autoload.php";

/**
 * @return string[]|null
 * @phpstan-return array<string, string>|null
 */
function parse_language_file(string $path, string $file) : ?array{
	$lang = parse_ini_file($path . "/" . $file, false, INI_SCANNER_RAW);
	if($lang === false){
		return null;
	}
	return $lang;
}

if(count($argv) !== 2){
	fwrite(STDERR, "Usage: php remove-dead-translations.php <translations folder>\n");
	exit(1);
}

$base = parse_language_file($argv[1], "eng.ini");

$fileList = scandir($argv[1]);
if($fileList === false){
	fwrite(STDERR, "Failed to read directory " . $argv[1] . "\n");
	exit(1);
}
foreach($fileList as $file){
	if($file === "." || $file === ".." || $file === "eng.ini" || !str_ends_with($file, ".ini")){
		continue;
	}

	$lang = parse_language_file($argv[1], $file);
	if($lang === null){
		fwrite(STDERR, "Failed to parse file " . $argv[1] . "/" . $file . "\n");
		exit(1);
	}
	$remove = [];
	foreach(Utils::stringifyKeys($lang) as $key => $value){
		if(!isset($base[$key])){
			$remove[] = $key;
		}
	}
	if(count($remove) === 0){
		echo "No dead translations found in $file\n";
		continue;
	}
	$contents = file_get_contents($argv[1] . "/" . $file);
	if($contents === false){
		fwrite(STDERR, "Failed to read file " . $argv[1] . "/" . $file . "\n");
		exit(1);
	}
	//regex strip to avoid messing with whitespace or other formatting
	foreach($remove as $key){
		$pattern = '/^' . preg_quote($key, '/') . '\s*=.+(\r?\n)/m';
		$contents = preg_replace($pattern, '', $contents);
		if($contents === null){
			fwrite(STDERR, "Regex error updating $file: " . preg_last_error_msg() . "\n");
			exit(1);
		}
	}
	file_put_contents($argv[1] . "/" . $file, $contents);
	echo "Processed $file, removed " . count($remove) . " dead translations\n";
}

$knownBadKeysRaw = file_get_contents($argv[1] . "/known-bad-keys.json");
if($knownBadKeysRaw === false){
	fwrite(STDERR, "Failed to read known-bad-keys.json\n");
	exit(1);
}
$oldKnownBadKeys = json_decode($knownBadKeysRaw, flags: JSON_THROW_ON_ERROR);
if(!is_array($oldKnownBadKeys)){
	fwrite(STDERR, "known-bad-keys.json is not an array\n");
	exit(1);
}
$knownBadKeys = array_filter($oldKnownBadKeys, fn($key) => (is_string($key) || is_int($key)) && isset($base[$key]));
if(count($knownBadKeys) === count($oldKnownBadKeys)){
	echo "No dead translations found in known-bad-keys.json\n";
	exit(0);
}
file_put_contents($argv[1] . "/known-bad-keys.json", json_encode(array_values($knownBadKeys), JSON_PRETTY_PRINT) . "\n");
echo "Updated known-bad-keys.json, removed " . (count($oldKnownBadKeys) - count($knownBadKeys)) . " dead translations\n";
