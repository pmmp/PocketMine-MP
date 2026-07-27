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

use pocketmine\lang\Translatable;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Filesystem;
use pocketmine\utils\Utils;
use Symfony\Component\Filesystem\Path;
use function array_map;
use function count;
use function dirname;
use function fclose;
use function fopen;
use function fwrite;
use function implode;
use function is_dir;
use function is_numeric;
use function ksort;
use function mkdir;
use function ob_start;
use function parse_ini_file;
use function preg_last_error_msg;
use function preg_match_all;
use function str_replace;
use function strtoupper;
use const INI_SCANNER_RAW;
use const SORT_NUMERIC;
use const SORT_STRING;
use const STDERR;

const PARAMETER_REGEX = '/{%(.+?)}/';

require dirname(__DIR__, 2) . '/vendor/autoload.php';

function constantify(string $permissionName) : string{
	return strtoupper(str_replace([".", "-"], "_", $permissionName));
}

function functionify(string $permissionName) : string{
	return str_replace([".", "-"], "_", $permissionName);
}

/**
 * @return resource
 */
function safe_fopen(string $file, string $flags){
	$dir = dirname($file);
	if(!@mkdir($dir, recursive: true) && !is_dir($dir)){
		throw new \RuntimeException("Couldn't create directory: $dir");
	}
	$result = fopen($file, $flags);
	if($result === false){
		throw new \RuntimeException("Failed to open file: $file");
	}
	return $result;
}

const NS_HEADER = <<<'HEADER'

namespace pocketmine\lang;


HEADER;

/**
 * @param string[] $languageDefinitions
 * @phpstan-param array<string, string> $languageDefinitions
 */
function generate_known_translation_keys(array $languageDefinitions, string $fileHeader) : void{
	$file = safe_fopen(dirname(__DIR__, 2) . '/generated/lang/KnownTranslationKeys.php', 'wb');

	fwrite($file, $fileHeader);
	fwrite($file, NS_HEADER);
	fwrite($file, <<<'HEADER'
/**
 * This class contains constants for all the translations known to PocketMine-MP as per the used version of pmmp/Language.
 * This class is generated automatically, do NOT modify it by hand.
 *
 * @internal
 */
final class KnownTranslationKeys{

HEADER);

	ksort($languageDefinitions, SORT_STRING);
	foreach(Utils::stringifyKeys($languageDefinitions) as $k => $_){
		fwrite($file, "\tpublic const " . constantify($k) . " = \"" . $k . "\";\n");
	}

	fwrite($file, "}\n");
	fclose($file);

	echo "Done generating KnownTranslationKeys.\n";
}

/**
 * @param string[] $languageDefinitions
 * @phpstan-param array<string, string> $languageDefinitions
 */
function generate_known_translation_parameter_info(array $languageDefinitions, string $fileHeader) : void{
	$file = safe_fopen(dirname(__DIR__, 2) . '/generated/lang/KnownTranslationParameterInfo.php', 'wb');

	fwrite($file, $fileHeader);
	fwrite($file, NS_HEADER);
	fwrite($file, <<<'HEADER'
use pocketmine\lang\KnownTranslationKeys as Keys;

/**
 * This class contains constants for all the translations known to PocketMine-MP as per the used version of pmmp/Language.
 * This class is generated automatically, do NOT modify it by hand.
 *
 * @internal
 */
final class KnownTranslationParameterInfo{
HEADER);
	ksort($languageDefinitions, SORT_STRING);

	fwrite($file, "\n\tpublic const TABLE = [\n");
	foreach(Utils::stringifyKeys($languageDefinitions) as $k => $v){
		if(preg_match_all(PARAMETER_REGEX, $v, $matches) === false){
			throw new AssumptionFailedError(preg_last_error_msg());
		}
		$uniqueParameters = [];
		foreach($matches[1] as $parameterName){
			$uniqueParameters[$parameterName] = $parameterName;
		}
		fwrite($file, "\t\tKeys::" . constantify($k) . " => [" . implode(", ", array_map(fn(string $s) => "\"$s\"", $uniqueParameters)) . "],\n");
	}
	fwrite($file, "\t];\n");

	fwrite($file, "}\n");

	fclose($file);

	echo "Done generating KnownTranslationParameterInfo.\n";
}

/**
 * @param string[] $languageDefinitions
 * @phpstan-param array<string, string> $languageDefinitions
 */
function generate_known_translation_factory(array $languageDefinitions, string $fileHeader) : void{
	$file = safe_fopen(dirname(__DIR__, 2) . '/generated/lang/KnownTranslationFactory.php', 'wb');
	ob_start();

	fwrite($file, $fileHeader);
	fwrite($file, NS_HEADER);
	fwrite($file, <<<'HEADER'
/**
 * This class contains factory methods for all the translations known to PocketMine-MP as per the used version of
 * pmmp/Language.
 * This class is generated automatically, do NOT modify it by hand.
 *
 * @internal
 */
final class KnownTranslationFactory{

HEADER);
	ksort($languageDefinitions, SORT_STRING);

	$parameterRegex = PARAMETER_REGEX;

	$translationContainerClass = (new \ReflectionClass(Translatable::class))->getShortName();
	foreach(Utils::stringifyKeys($languageDefinitions) as $key => $value){
		$parameters = [];
		$allParametersPositional = true;
		if(preg_match_all($parameterRegex, $value, $matches) > 0){
			foreach($matches[1] as $parameterName){
				if(is_numeric($parameterName)){
					$parameters[$parameterName] = "param$parameterName";
				}else{
					$parameters[$parameterName] = $parameterName;
					$allParametersPositional = false;
				}
			}
		}
		if($allParametersPositional){
			ksort($parameters, SORT_NUMERIC);
		}
		fwrite($file, "\tpublic static function " .
			functionify($key) .
			"(" . implode(", ", array_map(fn(string $paramName) => "$translationContainerClass|string \$$paramName", $parameters)) . ") : $translationContainerClass{\n");
		fwrite($file, "\t\treturn new $translationContainerClass(KnownTranslationKeys::" . constantify($key) . ", [");
		foreach($parameters as $parameterKey => $parameterName){
			fwrite($file, "\n\t\t\t" . (is_numeric($parameterKey) ? $parameterKey : "\"$parameterKey\"") . " => \$$parameterName,");
		}
		if(count($parameters) !== 0){
			fwrite($file, "\n\t\t");
		}
		fwrite($file, "]);\n\t}\n\n");
	}

	fwrite($file, "}\n");

	fclose($file);

	echo "Done generating KnownTranslationFactory.\n";
}

$fileHeader = Filesystem::fileGetContents(__DIR__ . "/templates/header.php");

$lang = parse_ini_file(Path::join(\pocketmine\LOCALE_DATA_PATH, "eng.ini"), false, INI_SCANNER_RAW);
if($lang === false){
	fwrite(STDERR, "Missing language files!\n");
	exit(1);
}

generate_known_translation_keys($lang, $fileHeader);
generate_known_translation_parameter_info($lang, $fileHeader);
generate_known_translation_factory($lang, $fileHeader);
