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

namespace pocketmine\tools\compact_regions;

use pocketmine\world\format\io\exception\CorruptedChunkException;
use pocketmine\world\format\io\region\CorruptedRegionException;
use pocketmine\world\format\io\region\RegionLoader;
use function array_sum;
use function arsort;
use function clearstatcache;
use function count;
use function defined;
use function dirname;
use function file_exists;
use function filesize;
use function in_array;
use function is_dir;
use function is_file;
use function number_format;
use function pathinfo;
use function rename;
use function round;
use function scandir;
use function unlink;
use function zlib_decode;
use function zlib_encode;

require dirname(__DIR__) . '/vendor/autoload.php';

const SUPPORTED_EXTENSIONS = [
	"mcr",
	"mca",
	"mcapm"
];

/**
 * @param int[] $files
 * @phpstan-param array<string, int> $files
 */
function find_regions_recursive(string $dir, array &$files) : void{
	foreach(scandir($dir, SCANDIR_SORT_NONE) as $file){
		if($file === "." or $file === ".."){
			continue;
		}
		$fullPath = $dir . "/" . $file;
		if(
			in_array(pathinfo($fullPath, PATHINFO_EXTENSION), SUPPORTED_EXTENSIONS, true) and
			is_file($fullPath)
		){
			$files[$fullPath] = filesize($fullPath);
		}elseif(is_dir($fullPath)){
			find_regions_recursive($fullPath, $files);
		}
	}
}

/**
 * @param string[] $argv
 */
function main(array $argv) : int{
	if(!isset($argv[1])){
		echo "Usage: " . PHP_BINARY . " " . __FILE__ . " <path to region folder or file>\n";
		return 1;
	}

	$logger = \GlobalLogger::get();

	/** @phpstan-var array<string, int> $files */
	$files = [];
	if(is_file($argv[1])){
		$files[$argv[1]] = filesize($argv[1]);
	}elseif(is_dir($argv[1])){
		find_regions_recursive($argv[1], $files);
	}
	if(count($files) === 0){
		echo "No supported files found\n";
		return 1;
	}

	arsort($files, SORT_NUMERIC);
	$currentSize = array_sum($files);
	$logger->info("Discovered " . count($files) . " files totalling " . number_format($currentSize) . " bytes");
	$logger->warning("Please DO NOT forcibly kill the compactor, or your files may be damaged.");

	$corruptedFiles = [];
	$doneCount = 0;
	$totalCount = count($files);
	foreach($files as $file => $size){
		$oldRegion = new RegionLoader($file);
		try{
			$oldRegion->open();
		}catch(CorruptedRegionException $e){
			$logger->error("Damaged region in file $file (" . $e->getMessage() . "), skipping");
			$corruptedFiles[] = $file;
			$doneCount++;
			continue;
		}

		$newFile = $file . ".compacted";
		$newRegion = new RegionLoader($newFile);
		$newRegion->open();

		$emptyRegion = true;
		$corruption = false;
		for($x = 0; $x < 32; $x++){
			for($z = 0; $z < 32; $z++){
				try{
					$data = $oldRegion->readChunk($x, $z);
				}catch(CorruptedChunkException $e){
					$logger->error("Damaged chunk $x $z in file $file (" . $e->getMessage() . "), skipping");
					$corruption = true;
					continue;
				}
				if($data !== null){
					$emptyRegion = false;
					$newRegion->writeChunk($x, $z, $data);
				}
			}
		}

		$oldRegion->close();
		$newRegion->close();
		if(!$corruption){
			unlink($file);
		}else{
			rename($file, $file . ".bak");
			$corruptedFiles[] = $file . ".bak";
		}
		if(!$emptyRegion){
			rename($newFile, $file);
		}else{
			unlink($newFile);
		}
		$doneCount++;
		$logger->info("Compacted region $file ($doneCount/$totalCount, " . round(($doneCount / $totalCount) * 100, 2) . "%)");
	}

	clearstatcache();
	$newSize = 0;
	foreach($files as $file => $oldSize){
		$newSize += file_exists($file) ? filesize($file) : 0;
	}
	$diff = $currentSize - $newSize;
	$logger->info("Finished compaction of " . count($files) . " files. Freed " . number_format($diff) . " bytes of space (" . round(($diff / $currentSize) * 100, 2) . "% reduction).");
	if(count($corruptedFiles) > 0){
		$logger->error("The following backup files were not removed due to corruption detected:");
		foreach($corruptedFiles as $file){
			echo $file . "\n";
		}
		return 1;
	}
	return 0;
}

if(!defined('pocketmine\_PHPSTAN_ANALYSIS')){
	exit(main($argv));
}
