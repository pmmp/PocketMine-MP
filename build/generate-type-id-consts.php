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
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Filesystem;
use pocketmine\utils\Utils;
use function array_filter;
use function array_map;
use function asort;
use function basename;
use function dirname;
use function max;
use function preg_last_error_msg;
use function preg_match;
use function preg_replace;
use const SORT_NUMERIC;

require dirname(__DIR__) . '/vendor/autoload.php';

/**
 * @param int[] $stringToTypeIdMap
 * @param int[] $backwardsCompatibilityConstants
 *
 * @phpstan-param array<string, int> $stringToTypeIdMap
 * @phpstan-param array<string, int> $backwardsCompatibilityConstants
 */
function patchTypeIds(string $file, array $stringToTypeIdMap, array $backwardsCompatibilityConstants) : void{
	$replacement = "";

	foreach(Utils::stringifyKeys($backwardsCompatibilityConstants) as $name => $id){
		if(!isset($stringToTypeIdMap[$name])){
			$stringToTypeIdMap[$name] = $id;
		}else{
			\GlobalLogger::get()->warning("Backwards compatibility constant $name no longer required for $file, please remove it from the list in " . basename(__FILE__));
		}
	}

	asort($stringToTypeIdMap, SORT_NUMERIC);
	$previousTypeName = null;
	foreach(Utils::stringifyKeys($stringToTypeIdMap) as $typeName => $typeId){
		//this shouldn't need any preprocessing - VanillaBlocks/VanillaItems should enforce that only valid type names are used
		if($previousTypeName !== null && $typeId !== $stringToTypeIdMap[$previousTypeName] + 1){
			$replacement .= "\n";
		}
		$replacement .= "\tpublic const $typeName = $typeId;\n";
		$previousTypeName = $typeName;
	}

	$contents = Filesystem::fileGetContents($file);
	if(preg_match('/[\h ]*public const (FIRST_UNUSED_[A-Z_\d]+_ID) = \d+;/', $contents, $matches) !== 1){
		throw new AssumptionFailedError("Failed to find FIRST_UNUSED_*_ID constant in $file");
	}
	$firstUnusedName = $matches[1];
	$firstUnusedValue = max($stringToTypeIdMap) + 1;
	$replacement .= "\n\tpublic const $firstUnusedName = $firstUnusedValue;\n\n";

	$patched = preg_replace(
		'/((?:\h*public const [A-Z_\d]+ = (?:self::[A-Z_\d]+(?: \+ \d)?|\d+);\n+)+)/',
		$replacement,
		$contents
	) ?? throw new \RuntimeException("Failed to patch $file (PCRE error " . preg_last_error_msg() . ")");

	Filesystem::safeFilePutContents($file, $patched);
	echo "Successfully updated $file\n";
}

patchTypeIds(
	dirname(__DIR__) . '/src/block/BlockTypeIds.php',
	array_map(array: VanillaBlocks::getAll(), callback: fn(Block $b) => $b->getTypeId()),
	[
		"POWDER_SNOW_CAULDRON" => 10674,
		"CHERRY_SAPLING" => 10699,
	] //not yet implemented stuff that had manually-defined type IDs prior to 5.0.0 release - we need to make sure these still exist for backwards compatibility
);
patchTypeIds(
	dirname(__DIR__) . '/src/item/ItemTypeIds.php',
	array_map(array: array_filter(VanillaItems::getAll(), fn(Item $b) => !$b instanceof ItemBlock), callback: fn(Item $b) => $b->getTypeId()),
	[
		"POWDER_SNOW_BUCKET" => 20258,
		"LINGERING_POTION" => 20259,
	] //not yet implemented stuff that had manually-defined type IDs prior to 5.0.0 release - we need to make sure these still exist for backwards compatibility
);
echo "Done. Don't forget to run CS fixup after generating code.\n";
