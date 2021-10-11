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

require dirname(__DIR__, 3) . '/vendor/autoload.php';

/* This script needs to be re-run after any intentional blockfactory change (adding or removing a block state). */

$factory = new \pocketmine\block\BlockFactory();
$remaps = [];
$new = [];
foreach($factory->getAllKnownStates() as $index => $block){
	if($block->getFullId() !== $index){
		$remaps[$index] = $block->getFullId();
	}else{
		$new[$index] = $block->getName();
	}
}
$oldTable = json_decode(file_get_contents(__DIR__ . '/block_factory_consistency_check.json'), true);
if(!is_array($oldTable)){
	throw new \pocketmine\utils\AssumptionFailedError("Old table should be array{knownStates: array<string, string>, remaps: array<string, int>}");
}
$old = $oldTable["knownStates"];
$oldRemaps = $oldTable["remaps"];

foreach($old as $k => $name){
	if(!isset($new[$k])){
		echo "Removed state for $name (" . ($k >> \pocketmine\block\Block::INTERNAL_METADATA_BITS) . ":" . ($k & \pocketmine\block\Block::INTERNAL_METADATA_MASK) . ")\n";
	}
}
foreach($new as $k => $name){
	if(!isset($old[$k])){
		echo "Added state for $name (" . ($k >> \pocketmine\block\Block::INTERNAL_METADATA_BITS) . ":" . ($k & \pocketmine\block\Block::INTERNAL_METADATA_MASK) . ")\n";
	}elseif($old[$k] !== $name){
		echo "Name changed (" . ($k >> \pocketmine\block\Block::INTERNAL_METADATA_BITS) . ":" . ($k & \pocketmine\block\Block::INTERNAL_METADATA_MASK) . "): " . $old[$k] . " -> " . $name . "\n";
	}
}

foreach($oldRemaps as $index => $mapped){
	if(!isset($remaps[$index])){
		echo "Removed remap of " . ($index >> 4) . ":" . ($index & 0xf) . "\n";
	}
}
foreach($remaps as $index => $mapped){
	if(!isset($oldRemaps[$index])){
		echo "New remap of " . ($index >> 4) . ":" . ($index & 0xf) . " (" . ($mapped >> 4) . ":" . ($mapped & 0xf) . ") (" . $new[$mapped] . ")\n";
	}elseif($oldRemaps[$index] !== $mapped){
		echo "Remap changed for " . ($index >> 4) . ":" . ($index & 0xf) . " (" . ($oldRemaps[$index] >> 4) . ":" . ($oldRemaps[$index] & 0xf) . " (" . $old[$oldRemaps[$index]] . ") -> " . ($mapped >> 4) . ":" . ($mapped & 0xf) . " (" . $new[$mapped] . "))\n";
	}
}
file_put_contents(__DIR__ . '/block_factory_consistency_check.json', json_encode(
	[
		"knownStates" => $new,
		"remaps" => $remaps
	],
));
