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

use pocketmine\world\format\io\FormatConverter;
use pocketmine\world\format\io\WorldProviderManager;
use pocketmine\world\format\io\WorldProviderManagerEntry;
use pocketmine\world\format\io\WritableWorldProviderManagerEntry;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$providerManager = new WorldProviderManager();
$writableFormats = array_filter($providerManager->getAvailableProviders(), fn(WorldProviderManagerEntry $class) => $class instanceof WritableWorldProviderManagerEntry);
$requiredOpts = [
	"world" => "path to the input world for conversion",
	"backup" => "path to back up the original files",
	"format" => "desired output format (can be one of: " . implode(",", array_keys($writableFormats)) . ")"
];
$usageMessage = "Options:\n";
foreach($requiredOpts as $_opt => $_desc){
	$usageMessage .= "\t--$_opt : $_desc\n";
}
$plainArgs = getopt("", array_map(function(string $str){ return "$str:"; }, array_keys($requiredOpts)));
$args = [];
foreach($requiredOpts as $opt => $desc){
	if(!isset($plainArgs[$opt]) || !is_string($plainArgs[$opt])){
		fwrite(STDERR, $usageMessage);
		exit(1);
	}
	$args[$opt] = $plainArgs[$opt];
}
if(!array_key_exists($args["format"], $writableFormats)){
	fwrite(STDERR, $usageMessage);
	exit(1);
}

$inputPath = realpath($args["world"]);
if($inputPath === false){
	fwrite(STDERR, "Cannot find input world at location: " . $args["world"] . PHP_EOL);
	exit(1);
}
$backupPath = realpath($args["backup"]);
if($backupPath === false || (!@mkdir($backupPath, 0777, true) && !is_dir($backupPath)) || !is_writable($backupPath)){
	fwrite(STDERR, "Backup file path " . $args["backup"] . " is not writable (permission error or doesn't exist), aborting" . PHP_EOL);
	exit(1);
}

$oldProviderClasses = $providerManager->getMatchingProviders($inputPath);
if(count($oldProviderClasses) === 0){
	fwrite(STDERR, "Unknown input world format" . PHP_EOL);
	exit(1);
}
if(count($oldProviderClasses) > 1){
	fwrite(STDERR, "Ambiguous input world format: matched " . count($oldProviderClasses) . " (" . implode(array_keys($oldProviderClasses)) . ")" . PHP_EOL);
	exit(1);
}
$oldProviderClass = array_shift($oldProviderClasses);
$oldProvider = $oldProviderClass->fromPath($inputPath);

$converter = new FormatConverter($oldProvider, $writableFormats[$args["format"]], $backupPath, GlobalLogger::get());
$converter->execute();
