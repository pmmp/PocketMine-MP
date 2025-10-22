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

namespace pocketmine\build\generate_known_translation_apis;

use function array_fill_keys;
use function count;
use function explode;
use function file_get_contents;
use function fwrite;
use function in_array;
use function is_array;
use function json_decode;
use function json_encode;
use function parse_ini_file;
use function preg_match_all;
use function str_starts_with;
use const INI_SCANNER_RAW;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use const PHP_EOL;
use const PHP_INT_MAX;
use const STDERR;

/**
 * @param string[]                      $baseLanguageDef
 * @param string[]                      $altLanguageDef
 *
 * @phpstan-param array<string, string> $baseLanguageDef
 * @phpstan-param array<string, string> $altLanguageDef
 *
 * @return bool true if everything is OK, false otherwise
 */
function verify_translations(array $baseLanguageDef, string $altLanguageName, array $altLanguageDef) : bool{
	$parameterRegex = '/{%(.+?)}/';

	$ok = true;
	foreach($baseLanguageDef as $key => $baseString){
		if(!isset($altLanguageDef[$key])){
			continue;
		}
		$altString = $altLanguageDef[$key];
		$baseParams = preg_match_all($parameterRegex, $baseString, $baseMatches);
		$altParams = preg_match_all($parameterRegex, $altString, $altMatches);
		if($baseParams === false || $altParams === false){
			throw new \Error("preg_match_all() should not have failed here");
		}
		foreach($baseMatches[1] as $paramName){
			if(!in_array($paramName, $altMatches[1], true)){
				fwrite(STDERR, "$altLanguageName: missing parameter %$paramName in string $key" . PHP_EOL);
				$ok = false;
			}
		}
		foreach($altMatches[1] as $paramName){
			if(!in_array($paramName, $baseMatches[1], true)){
				fwrite(STDERR, "$altLanguageName: unexpected extra parameter %$paramName in string $key" . PHP_EOL);
				$ok = false;
			}
		}
	}
	foreach($altLanguageDef as $key => $altString){
		if(!isset($baseLanguageDef[$key])){
			fwrite(STDERR, "$altLanguageName: unexpected extra string $key with no base in eng.ini" . PHP_EOL);
			$ok = false;
		}
	}
	return $ok;
}

/**
 * @return string[]|null
 * @phpstan-return array<string, string>|null
 */
function parse_language_file(string $path, string $code) : ?array{
	$lang = parse_ini_file($path . "/" . "$code.ini", false, INI_SCANNER_RAW);
	if($lang === false){
		return null;
	}
	return $lang;
}

/**
 * @return string[]
 * @phpstan-return array<string, string>
 */
function parse_mojang_language_defs(string $contents) : array{
	$result = [];
	foreach(explode("\n", $contents, limit: PHP_INT_MAX) as $line){
		$stripped = explode("##", $line, 2)[0];
		$kv = explode("=", $stripped, 2);
		if(count($kv) !== 2){
			continue;
		}
		$result[$kv[0]] = $kv[1];
	}

	return $result;
}

/**
 * @param string[] $pocketmine
 * @param string[] $mojang
 * @param string[] $knownBadKeys
 * @phpstan-param array<string, string> $pocketmine
 * @phpstan-param array<string, string> $mojang
 * @phpstan-param array<string, bool> $knownBadKeys
 *
 * @return string[]
 * @phpstan-return list<string>
 */
function verify_keys(array $pocketmine, array $mojang, array $knownBadKeys) : array{
	$wrong = [];
	foreach($pocketmine as $k => $v){
		if(str_starts_with($k, "pocketmine.")){
			continue;
		}

		if(!isset($mojang[$k]) && !isset($knownBadKeys[$k])){
			$wrong[] = $k;
		}
	}
	foreach($knownBadKeys as $k => $_){
		if(!isset($pocketmine[$k])){
			fwrite(STDERR, "known-bad-keys.json contains key \"$k\" which does not exist in eng.ini\n");
		}
	}
	return $wrong;
}

if(count($argv) !== 2){
	fwrite(STDERR, "Required arguments: path\n");
	exit(1);
}
$eng = parse_language_file($argv[1], "eng");
if($eng === null){
	fwrite(STDERR, "Failed to parse eng.ini\n");
	exit(1);
}

$mojangRaw = file_get_contents("https://raw.githubusercontent.com/Mojang/bedrock-samples/refs/heads/main/resource_pack/texts/en_US.lang");
if($mojangRaw === false){
	fwrite(STDERR, "Failed to fetch official Mojang sources for verification\n");
	exit(1);
}
$mojang = parse_mojang_language_defs($mojangRaw);

$knownBadKeysRaw = file_get_contents($argv[1] . "/known-bad-keys.json");
$knownBadKeysDecoded = $knownBadKeysRaw !== false ? json_decode($knownBadKeysRaw, associative: true, flags: JSON_THROW_ON_ERROR) : [];

if(!is_array($knownBadKeysDecoded)){
	fwrite(STDERR, "known-bad-keys.json should contain an array of strings\n");
	exit(1);
}
$knownBadKeys = [];
foreach($knownBadKeysDecoded as $key){
	if(!is_string($key)){
		fwrite(STDERR, "known-bad-keys.json should contain an array of strings\n");
		exit(1);
	}
	$knownBadKeys[] = $key;
}

$badKeys = verify_keys($eng, $mojang, array_fill_keys($knownBadKeys, true));
if(count($badKeys) !== 0){
	fwrite(STDERR, "The following non-\"pocketmine.\" keys are not matched by Mojang sources and are not whitelisted:\n");
	fwrite(STDERR, json_encode($badKeys, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . "\n");
	fwrite(STDERR, "Keys must either match Mojang sources, or be prefixed with \"pocketmine.\"\n");
	fwrite(STDERR, "Failure to do so will cause these to be shown incorrectly on clients, as the server won't translate them\n");
	exit(1);
}

$exit = 0;
/**
 * @var string[] $match
 * @phpstan-var array{0: string, 1: string} $match
 */
foreach(new \RegexIterator(new \FilesystemIterator($argv[1], \FilesystemIterator::CURRENT_AS_PATHNAME), "/([a-z]+)\.ini$/", \RegexIterator::GET_MATCH) as $match){
	$code = $match[1];
	if($code === "eng"){
		continue;
	}
	$otherLang = parse_language_file($argv[1], $code);
	if($otherLang === null){
		fwrite(STDERR, "Error parsing $code.ini\n");
		$exit = 1;
		continue;
	}
	if(!verify_translations($eng, $code, $otherLang)){
		fwrite(STDERR, "Errors found in $code.ini\n");
		$exit = 1;
		continue;
	}

	echo "Everything OK in $code.ini\n";
}
exit($exit);
