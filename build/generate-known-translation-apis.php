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
use Webmozart\PathUtil\Path;
use function array_map;
use function count;
use function dirname;
use function file_put_contents;
use function fwrite;
use function implode;
use function is_numeric;
use function ksort;
use function ob_get_clean;
use function ob_start;
use function parse_ini_file;
use function preg_match_all;
use function str_replace;
use function strtoupper;
use const INI_SCANNER_RAW;
use const SORT_STRING;
use const STDERR;

require dirname(__DIR__) . '/vendor/autoload.php';

function constantify(string $permissionName) : string{
	return strtoupper(str_replace([".", "-"], "_", $permissionName));
}

function functionify(string $permissionName) : string{
	return str_replace([".", "-"], "_", $permissionName);
}

const SHARED_HEADER = <<<'HEADER'
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

namespace pocketmine\lang;


HEADER;

/**
 * @param string[] $languageDefinitions
 * @phpstan-param array<string, string> $languageDefinitions
 */
function generate_known_translation_keys(array $languageDefinitions) : void{
	ob_start();

	echo SHARED_HEADER;
	echo <<<'HEADER'
/**
 * This class contains constants for all the translations known to PocketMine-MP as per the used version of pmmp/Language.
 * This class is generated automatically, do NOT modify it by hand.
 */
final class KnownTranslationKeys{

HEADER;

	ksort($languageDefinitions, SORT_STRING);
	foreach($languageDefinitions as $k => $_){
		echo "\tpublic const ";
		echo constantify($k);
		echo " = \"" . $k . "\";\n";
	}

	echo "}\n";

	file_put_contents(dirname(__DIR__) . '/src/lang/KnownTranslationKeys.php', ob_get_clean());

	echo "Done generating KnownTranslationKeys.\n";
}

/**
 * @param string[] $languageDefinitions
 * @phpstan-param array<string, string> $languageDefinitions
 */
function generate_known_translation_factory(array $languageDefinitions) : void{
	ob_start();

	echo SHARED_HEADER;
	echo <<<'HEADER'
/**
 * This class contains factory methods for all the translations known to PocketMine-MP as per the used version of
 * pmmp/Language.
 * This class is generated automatically, do NOT modify it by hand.
 */
final class KnownTranslationFactory{

HEADER;
	ksort($languageDefinitions, SORT_STRING);

	$parameterRegex = '/{%(.+?)}/';

	$translationContainerClass = (new \ReflectionClass(Translatable::class))->getShortName();
	foreach($languageDefinitions as $key => $value){
		$parameters = [];
		if(preg_match_all($parameterRegex, $value, $matches) > 0){
			foreach($matches[1] as $parameterName){
				if(is_numeric($parameterName)){
					$parameters[$parameterName] = "param$parameterName";
				}else{
					$parameters[$parameterName] = $parameterName;
				}
			}
		}
		echo "\tpublic static function " .
			functionify($key) .
			"(" . implode(", ", array_map(fn(string $paramName) => "$translationContainerClass|string \$$paramName", $parameters)) . ") : $translationContainerClass{\n";
		echo "\t\treturn new $translationContainerClass(KnownTranslationKeys::" . constantify($key) . ", [";
		foreach($parameters as $parameterKey => $parameterName){
			echo "\n\t\t\t";
			if(!is_numeric($parameterKey)){
				echo "\"$parameterKey\"";
			}else{
				echo $parameterKey;
			}
			echo " => \$$parameterName,";
		}
		if(count($parameters) !== 0){
			echo "\n\t\t";
		}
		echo "]);\n\t}\n\n";
	}

	echo "}\n";

	file_put_contents(dirname(__DIR__) . '/src/lang/KnownTranslationFactory.php', ob_get_clean());

	echo "Done generating KnownTranslationFactory.\n";
}

$lang = parse_ini_file(Path::join(\pocketmine\RESOURCE_PATH, "locale", "eng.ini"), false, INI_SCANNER_RAW);
if($lang === false){
	fwrite(STDERR, "Missing language files!\n");
	exit(1);
}

generate_known_translation_keys($lang);
generate_known_translation_factory($lang);
