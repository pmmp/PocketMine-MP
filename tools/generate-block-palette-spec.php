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

namespace pocketmine\tools\generate_block_palette_spec;

use pocketmine\data\bedrock\block\BlockStateData;
use pocketmine\errorhandler\ErrorToExceptionHandler;
use pocketmine\nbt\NbtException;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\TreeRoot;
use pocketmine\network\mcpe\convert\BlockStateDictionary;
use pocketmine\network\mcpe\convert\BlockStateDictionaryEntry;
use pocketmine\network\mcpe\protocol\serializer\NetworkNbtSerializer;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Utils;
use function array_map;
use function array_values;
use function count;
use function dirname;
use function file_get_contents;
use function file_put_contents;
use function fwrite;
use function get_class;
use function json_encode;
use function ksort;

require dirname(__DIR__) . '/vendor/autoload.php';

if(count($argv) !== 3){
	fwrite(STDERR, "Required arguments: input palette file path, output JSON file path\n");
	exit(1);
}

[, $inputFile, $outputFile] = $argv;

try{
	$states = array_map(
		fn(TreeRoot $root) => BlockStateData::fromNbt($root->mustGetCompoundTag()),
		(new NetworkNbtSerializer())->readMultiple(ErrorToExceptionHandler::trapAndRemoveFalse(fn() => file_get_contents($inputFile)))
	);
}catch(NbtException){
	fwrite(STDERR, "Invalid block palette file $argv[1]\n");
	exit(1);
}
$entries = [];
$fakeMeta = [];
foreach($states as $state){
	$fakeMeta[$state->getName()] ??= 0;
	$entries[] = new BlockStateDictionaryEntry($state, $fakeMeta[$state->getName()]++);
}
$palette = new BlockStateDictionary($entries);

$reportMap = [];

foreach($palette->getStates() as $entry){
	$state = $entry->getStateData();
	$name = $state->getName();
	$reportMap[$name] ??= [];
	foreach($state->getStates() as $propertyName => $value){
		if($value instanceof IntTag || $value instanceof StringTag){
			$rawValue = $value->getValue();
		}elseif($value instanceof ByteTag){
			$rawValue = match($value->getValue()){
				0 => false,
				1 => true,
				default => throw new AssumptionFailedError("Unexpected ByteTag value for $name -> $propertyName ($value)")
			};
		}else{
			throw new AssumptionFailedError("Unexpected tag type for $name -> $propertyName ($value)");
		}
		$reportMap[$name][$propertyName][get_class($value) . ":" . $value->getValue()] = $rawValue;
	}
}

foreach(Utils::stringifyKeys($reportMap) as $blockName => $propertyList){
	foreach(Utils::stringifyKeys($propertyList) as $propertyName => $propertyValues){
		ksort($reportMap[$blockName][$propertyName]);
		$reportMap[$blockName][$propertyName] = array_values($propertyValues);
	}
}
ksort($reportMap, SORT_STRING);

file_put_contents($outputFile, json_encode($reportMap, JSON_PRETTY_PRINT));
