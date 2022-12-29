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

namespace pocketmine\tools\modernize_item_map;

use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Utils;
use Webmozart\PathUtil\Path;
use function defined;
use function dirname;
use function file_get_contents;
use function file_put_contents;
use function is_array;
use function json_decode;
use function json_encode;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use const PHP_BINARY;

require dirname(__DIR__) . '/vendor/autoload.php';

/**
 * @param string[] $argv
 */
function main(array $argv) : int{
	if(!isset($argv[1])){
		echo "Usage: " . PHP_BINARY . " " . __FILE__ . " <path to 'item_id_map.json' file>\n";
		return 1;
	}
	$file = $argv[1];
	$resource = Utils::assumeNotFalse(file_get_contents($file), "Missing required resource file");
	$contents = json_decode($resource, true, flags: JSON_THROW_ON_ERROR);
	if(!is_array($contents)){
		throw new AssumptionFailedError("Invalid format of ID map");
	}
	$newContents = [];

	foreach($contents as $itemName => $id) {
		$newContents[$itemName] = [
			"runtime_id" => $id,
			"component_based" => false
		];
	}

	$rootPath = Path::getDirectory($file);
	file_put_contents(Path::join($rootPath, "required_item_list.json"), json_encode($newContents, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

	return 0;
}

if(!defined('pocketmine\_PHPSTAN_ANALYSIS')){
	exit(main($argv));
}
