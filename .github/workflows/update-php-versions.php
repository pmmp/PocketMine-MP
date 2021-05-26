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

const VERSIONS = [
	"7.3",
	"7.4",
	"8.0"
];

$workflowFile = file_get_contents(__DIR__ . '/main.yml');
$newWorkflowFile = $workflowFile;
foreach(VERSIONS as $v){
	$releaseInfo = file_get_contents("https://secure.php.net/releases?json&version=$v");
	if($releaseInfo === false){
		throw new \RuntimeException("Failed to contact php.net API");
	}
	$data = json_decode($releaseInfo, true);
	if(!is_array($data) || !isset($data["version"]) || !is_string($data["version"]) || preg_match('/^\d+\.\d+\.\d+(-[A-Za-z\d]+)?$/', $data["version"]) === 0){
		throw new \RuntimeException("Invalid data returned by API");
	}
	$updated = preg_replace("/$v\.\d+/", $data["version"], $newWorkflowFile);
	if($updated !== $newWorkflowFile){
		echo "Updated $v revision to " . $data["version"] . "\n";
	}
	$newWorkflowFile = $updated;
}

if($workflowFile !== $newWorkflowFile){
	echo "Writing modified workflow file\n";
	file_put_contents(__DIR__ . '/main.yml', $newWorkflowFile);
}
