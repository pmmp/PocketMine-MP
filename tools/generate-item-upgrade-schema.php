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

/**
 * This script is used to generate a version-to-version item upgrade schema.
 * The following inputs are required:
 * - A full item mapping table from a current version (e.g. r16_to_current_block_map.json)
 * - A directory containing schemas for previous versions' incremental updates
 */
namespace pocketmine\tools\generate_item_upgrade_schema;

use pocketmine\errorhandler\ErrorToExceptionHandler;
use pocketmine\utils\Filesystem;
use Symfony\Component\Filesystem\Path;
use function count;
use function dirname;
use function file_put_contents;
use function is_array;
use function json_decode;
use function json_encode;
use function ksort;
use function scandir;
use const JSON_FORCE_OBJECT;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use const SCANDIR_SORT_ASCENDING;
use const SORT_STRING;

require dirname(__DIR__) . '/vendor/autoload.php';

if(count($argv) !== 4){
	\GlobalLogger::get()->error("Required arguments: path to mapping table, path to current schemas, path to output file");
	exit(1);
}

[, $mappingTableFile, $upgradeSchemasDir, $outputFile] = $argv;

$target = json_decode(Filesystem::fileGetContents($mappingTableFile), true, JSON_THROW_ON_ERROR);
if(!is_array($target)){
	\GlobalLogger::get()->error("Invalid mapping table file");
	exit(1);
}

$files = ErrorToExceptionHandler::trapAndRemoveFalse(fn() => scandir($upgradeSchemasDir, SCANDIR_SORT_ASCENDING));

$merged = [];
foreach($files as $file){
	if($file === "." || $file === ".."){
		continue;
	}
	\GlobalLogger::get()->info("Processing schema file $file");
	$data = json_decode(Filesystem::fileGetContents(Path::join($upgradeSchemasDir, $file)), associative: true, flags: JSON_THROW_ON_ERROR);
	if(!is_array($data)){
		\GlobalLogger::get()->error("Invalid schema file $file");
		exit(1);
	}
	foreach(($data["renamedIds"] ?? []) as $oldId => $newId){
		if(isset($merged["simple"][$oldId])){
			\GlobalLogger::get()->warning("Duplicate rename for $oldId in file $file (was " . $merged["simple"][$oldId] . ", now $newId)");
		}
		$merged["simple"][$oldId] = $newId;
	}

	foreach(($data["remappedMetas"] ?? []) as $oldId => $mappings){
		foreach($mappings as $meta => $newId){
			if(isset($merged["complex"][$oldId][$meta])){
				\GlobalLogger::get()->warning("Duplicate meta remap for $oldId meta $meta in file $file (was " . $merged["complex"][$oldId][$meta] . ", now $newId)");
			}
			$merged["complex"][$oldId][$meta] = $newId;
		}
	}
}

$newDiff = [];

foreach($target["simple"] as $oldId => $newId){
	$previousNewId = $merged["simple"][$oldId] ?? null;
	if(
		$previousNewId === $newId || //if previous schemas already accounted for this
		($previousNewId !== null && isset($target["simple"][$previousNewId])) //or the item's ID has been changed for a second time
	){
		continue;
	}
	$newDiff["renamedIds"][$oldId] = $newId;
}
if(isset($newDiff["renamedIds"])){
	ksort($newDiff["renamedIds"], SORT_STRING);
}

foreach($target["complex"] as $oldId => $mappings){
	foreach($mappings as $meta => $newId){
		if(($merged["complex"][$oldId][$meta] ?? null) !== $newId){
			if($oldId === "minecraft:spawn_egg" && $meta === 130 && ($newId === "minecraft:axolotl_bucket" || $newId === "minecraft:axolotl_spawn_egg")){
				//TODO: hack for vanilla bug workaround
				continue;
			}
			$newDiff["remappedMetas"][$oldId][$meta] = $newId;
		}
	}
	if(isset($newDiff["remappedMetas"][$oldId])){
		ksort($newDiff["remappedMetas"][$oldId], SORT_STRING);
	}
}
if(isset($newDiff["remappedMetas"])){
	ksort($newDiff["remappedMetas"], SORT_STRING);
}
ksort($newDiff, SORT_STRING);

\GlobalLogger::get()->info("Writing output file to $outputFile");
file_put_contents($outputFile, json_encode($newDiff, JSON_PRETTY_PRINT | JSON_FORCE_OBJECT));
