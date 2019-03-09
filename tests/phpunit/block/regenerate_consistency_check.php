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

\pocketmine\block\BlockFactory::init();

$old = json_decode(file_get_contents(__DIR__ . '/block_factory_consistency_check.json'), true);
$new = array_map(
	function(\pocketmine\block\Block $block) : string{
		return $block->getName();
	},
	\pocketmine\block\BlockFactory::getAllKnownStates()
);
foreach($old as $k => $name){
	if(!isset($new[$k])){
		echo "Removed state for $name (" . ($k >> 4) . ":" . ($k & 0xf) . ")\n";
	}
}
foreach($new as $k => $name){
	if(!isset($old[$k])){
		echo "Added state for $name (" . ($k >> 4) . ":" . ($k & 0xf) . ")\n";
	}elseif($old[$k] !== $name){
		echo "Name changed (" . ($k >> 4) . ":" . ($k & 0xf) . "): " . $old[$k] . " -> " . $name . "\n";
	}
}
file_put_contents(__DIR__ . '/block_factory_consistency_check.json', json_encode(
	$new
));
