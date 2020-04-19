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

namespace pocketmine\build\make_release;

use pocketmine\utils\VersionString;
use function dirname;
use function fgets;
use function file_get_contents;
use function file_put_contents;
use function preg_replace;
use function sleep;
use function sprintf;
use function system;
use const pocketmine\BASE_VERSION;
use const STDIN;

require_once dirname(__DIR__) . '/vendor/autoload.php';


function replaceVersion(string $versionInfoPath, string $newVersion, bool $isDev) : void{
	$versionInfo = file_get_contents($versionInfoPath);
	$versionInfo = preg_replace(
		$pattern = '/^const BASE_VERSION = "(\d+)\.(\d+)\.(\d+)(?:-(.*))?";$/m',
		'const BASE_VERSION = "' . $newVersion . '";',
		$versionInfo
	);
	$versionInfo = preg_replace(
		'/^const IS_DEVELOPMENT_BUILD = (?:true|false);$/m',
		'const IS_DEVELOPMENT_BUILD = ' . ($isDev ? 'true' : 'false') . ';',
		$versionInfo
	);
	file_put_contents($versionInfoPath, $versionInfo);
}

/**
 * @param string[] $argv
 * @phpstan-param list<string> $argv
 */
function main(array $argv) : void{
	if(isset($argv[1])){
		$currentVer = new VersionString($argv[1]);
	}else{
		$currentVer = new VersionString(BASE_VERSION);
	}
	$nextVer = new VersionString(sprintf(
		"%u.%u.%u",
		$currentVer->getMajor(),
		$currentVer->getMinor(),
		$currentVer->getPatch() + 1
	));

	$versionInfoPath = dirname(__DIR__) . '/src/VersionInfo.php';
	replaceVersion($versionInfoPath, $currentVer->getBaseVersion(), false);
	
	echo "please add appropriate notes to the changelog and press enter...";
	fgets(STDIN);
	system('git add "' . dirname(__DIR__) . '/changelogs"');
	system('git commit -m "Release ' . $currentVer->getBaseVersion() . '" --include "' . $versionInfoPath . '"');
	system('git tag ' . $currentVer->getBaseVersion());
	replaceVersion($versionInfoPath, $nextVer->getBaseVersion(), true);
	system('git add "' . $versionInfoPath . '"');
	system('git commit -m "' . $nextVer->getBaseVersion() . ' is next" --include "' . $versionInfoPath . '"');
	echo "pushing changes in 5 seconds\n";
	sleep(5);
	system('git push origin HEAD ' . $currentVer->getBaseVersion());
}

if(!defined('pocketmine\_PHPSTAN_ANALYSIS')){
	main($argv);
}
