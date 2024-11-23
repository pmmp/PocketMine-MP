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

use pocketmine\block\BlockTest;
use pocketmine\block\RuntimeBlockStateRegistry;

require dirname(__DIR__, 3) . '/vendor/autoload.php';

/* This script needs to be re-run after any intentional blockfactory change (adding or removing a block state). */

$newTable = BlockTest::computeConsistencyCheckTable(RuntimeBlockStateRegistry::getInstance());

$oldTablePath = __DIR__ . '/block_factory_consistency_check.json';
if(file_exists($oldTablePath)){
	$errors = BlockTest::computeConsistencyCheckDiff($oldTablePath, $newTable);

	if(count($errors) > 0){
		echo count($errors) . " changes detected:\n";
		foreach($errors as $error){
			echo $error . "\n";
		}
	}else{
		echo "No changes detected\n";
	}
}else{
	echo "WARNING: Unable to calculate diff, no previous consistency check file found\n";
}

ksort($newTable, SORT_STRING);

file_put_contents($oldTablePath, json_encode($newTable, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
