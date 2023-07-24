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

use pocketmine\block\Block;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\utils\AssumptionFailedError;

require dirname(__DIR__, 3) . '/vendor/autoload.php';

/* This script needs to be re-run after any intentional blockfactory change (adding or removing a block state). */

$factory = new \pocketmine\block\RuntimeBlockStateRegistry();
$remaps = [];
$new = [];
foreach(RuntimeBlockStateRegistry::getInstance()->getAllKnownStates() as $index => $block){
	if($index !== $block->getStateId()){
		throw new AssumptionFailedError("State index should always match state ID");
	}
	$new[$index] = $block->getName();
}

$oldTablePath = __DIR__ . '/block_factory_consistency_check.json';
if(file_exists($oldTablePath)){
	$oldTable = json_decode(file_get_contents($oldTablePath), true);
	if(!is_array($oldTable)){
		throw new \pocketmine\utils\AssumptionFailedError("Old table should be array{knownStates: array<string, string>, stateDataBits: int}");
	}
	$old = [];
	/**
	 * @var string $name
	 * @var int[]  $stateIds
	 */
	foreach($oldTable["knownStates"] as $name => $stateIds){
		foreach($stateIds as $stateId){
			$old[$stateId] = $name;
		}
	}
	$oldStateDataSize = $oldTable["stateDataBits"];
	$oldStateDataMask = ~(~0 << $oldStateDataSize);

	if($oldStateDataSize !== Block::INTERNAL_STATE_DATA_BITS){
		echo "State data bits changed from $oldStateDataSize to " . Block::INTERNAL_STATE_DATA_BITS . "\n";
	}

	foreach($old as $k => $name){
		[$oldId, $oldStateData] = [$k >> $oldStateDataSize, $k & $oldStateDataMask];
		$reconstructedK = ($oldId << Block::INTERNAL_STATE_DATA_BITS) | $oldStateData;
		if(!isset($new[$reconstructedK])){
			echo "Removed state for $name ($oldId:$oldStateData)\n";
		}
	}
	foreach($new as $k => $name){
		[$newId, $newStateData] = [$k >> Block::INTERNAL_STATE_DATA_BITS, $k & Block::INTERNAL_STATE_DATA_MASK];
		if($newStateData > $oldStateDataMask){
			echo "Added state for $name ($newId, $newStateData)\n";
		}else{
			$reconstructedK = ($newId << $oldStateDataSize) | $newStateData;
			if(!isset($old[$reconstructedK])){
				echo "Added state for $name ($newId:$newStateData)\n";
			}elseif($old[$reconstructedK] !== $name){
				echo "Name changed ($newId:$newStateData) " . $old[$reconstructedK] . " -> " . $name . "\n";
			}
		}
	}
}else{
	echo "WARNING: Unable to calculate diff, no previous consistency check file found\n";
}

$newTable = [];
foreach($new as $stateId => $name){
	$newTable[$name][] = $stateId;
}
ksort($newTable, SORT_STRING);
foreach($newTable as &$stateIds){
	sort($stateIds, SORT_NUMERIC);
}

file_put_contents(__DIR__ . '/block_factory_consistency_check.json', json_encode(
	[
		"knownStates" => $newTable,
		"stateDataBits" => Block::INTERNAL_STATE_DATA_BITS
	],
	JSON_THROW_ON_ERROR
));
