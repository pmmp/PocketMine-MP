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

namespace pocketmine\build\generate_type_id_consts;

/*
 * This script patches BlockTypeIds and ItemTypeIds with updated constants generated from VanillaBlocks and VanillaItems
 * respectively.
 */

use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\item\ItemBlock;
use pocketmine\item\VanillaItems;
use pocketmine\utils\Filesystem;
use pocketmine\utils\Utils;
use function array_filter;
use function array_map;
use function asort;
use function dirname;
use function max;
use function preg_last_error_msg;
use function preg_replace;
use const SORT_NUMERIC;

require dirname(__DIR__) . '/vendor/autoload.php';

/**
 * @param int[]    $stringToTypeIdMap
 * @param string[] $backwardsCompatibilityConstants
 *
 * @phpstan-param array<string, int> $stringToTypeIdMap
 * @phpstan-param array<string>      $backwardsCompatibilityConstants
 */
function patchTypeIds(string $file, array $stringToTypeIdMap, array $backwardsCompatibilityConstants) : void{
	$replacement = "";
	asort($stringToTypeIdMap, SORT_NUMERIC);
	foreach(Utils::stringifyKeys($stringToTypeIdMap) as $typeName => $typeId){
		//this shouldn't need any preprocessing - VanillaBlocks/VanillaItems should enforce that only valid type names are used
		$replacement .= "\tpublic const $typeName = $typeId;\n";
	}
	$max = max($stringToTypeIdMap);
	foreach($backwardsCompatibilityConstants as $name){
		if(!isset($stringToTypeIdMap[$name])){
			$replacement .= "\tpublic const $name = " . (++$max) . ";\n";
		}
	}

	$contents = Filesystem::fileGetContents($file);
	$patched = preg_replace(
		'/((?:[\h ]*public const (?!FIRST_UNUSED)[A-Z_\d]+ = \d+;\n+)+)/',
		$replacement . "\n",
		$contents
	) ?? throw new \RuntimeException("Failed to patch $file (PCRE error " . preg_last_error_msg() . ")");
	$patched = preg_replace(
		'/([\h ]*public const FIRST_UNUSED_[A-Z_\d]+_ID = )(\d+);/',
		'${1}' . ($max + 1) . ';',
		$patched
	) ?? throw new \RuntimeException("Failed to patch $file (PCRE error " . preg_last_error_msg() . ")");

	Filesystem::safeFilePutContents($file, $patched);
	echo "Successfully updated $file\n";
}

patchTypeIds(
	dirname(__DIR__) . '/src/block/BlockTypeIds.php',
	array_map(array: VanillaBlocks::getAll(), callback: fn(Block $b) => $b->getTypeId()),
	[
		"POWDER_SNOW_CAULDRON"
	] //not yet implemented stuff that had manually-defined type IDs prior to 5.0.0 release - we need to make sure these still exist for backwards compatibility
);
patchTypeIds(
	dirname(__DIR__) . '/src/item/ItemTypeIds.php',
	array_map(array: array_filter(VanillaItems::getAll(), fn(Item $b) => !$b instanceof ItemBlock), callback: fn(Item $b) => $b->getTypeId()),
	[
		"POWDER_SNOW_BUCKET",
		"LINGERING_POTION",
	] //not yet implemented stuff that had manually-defined type IDs prior to 5.0.0 release - we need to make sure these still exist for backwards compatibility
);
echo "Done. Don't forget to run CS fixup after generating code.\n";
