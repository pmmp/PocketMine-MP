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

namespace pocketmine\build\check_gencode_file_collisions;

use Symfony\Component\Filesystem\Path;
use function count;
use function dirname;
use function file_exists;
use function fwrite;
use function realpath;
use function unlink;
use const PHP_EOL;
use const STDERR;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$fix = false;
$argv ??= [];
if(count($argv) === 2 && $argv[1] === "--fix"){
	echo "Will delete any colliding src/ files found" . PHP_EOL;
	$fix = true;
}elseif(count($argv) !== 1){
	fwrite(STDERR, "Usage: " . __FILE__ . " [--fix]" . PHP_EOL);
	exit(1);
}
$generatedDir = realpath(dirname(__DIR__, 2) . '/generated/');
if($generatedDir === false){
	fwrite(STDERR, "generated/ dir doesn't exist or isn't accessible" . PHP_EOL);
	exit(1);
}
$srcDir = realpath(dirname(__DIR__, 2) . '/src');
if($srcDir === false){
	fwrite(STDERR, "src/ dir doesn't exist or isn't accessible" . PHP_EOL);
	exit(1);
}

$iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($generatedDir, \RecursiveDirectoryIterator::SKIP_DOTS | \RecursiveDirectoryIterator::CURRENT_AS_FILEINFO));
$count = 0;
/**
 * @var \SplFileInfo $file
 */
foreach($iterator as $file) {
	$genFile = $file->getRealPath();

	$relative = $iterator->getSubPathname();
	$srcFile = Path::join($srcDir, $relative);
	if(file_exists($srcFile)){
		$count++;
		if($fix){
			echo "WARNING: Deleting file $srcFile in favour of $genFile" . PHP_EOL;
			unlink($srcFile);
		}else{
			fwrite(STDERR, "Collision detected between $genFile and $srcFile" . PHP_EOL);
		}
	}
}
if($count === 0){
	echo "No conflicts detected between $srcDir and $generatedDir" . PHP_EOL;
}else{
	if($fix){
		echo "Deleted $count conflicting files" . PHP_EOL;
	}else{
		fwrite(STDERR, "Found $count conflicting files" . PHP_EOL);
		exit(1);
	}
}
