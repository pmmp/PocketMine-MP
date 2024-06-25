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

use pocketmine\utils\Filesystem;
use pocketmine\utils\Utils;
use pocketmine\utils\VersionString;
use pocketmine\VersionInfo;
use function array_keys;
use function array_map;
use function dirname;
use function fgets;
use function file_put_contents;
use function fwrite;
use function getopt;
use function is_string;
use function max;
use function preg_match;
use function preg_replace;
use function sprintf;
use function str_pad;
use function strlen;
use function strtolower;
use function system;
use const STDERR;
use const STDIN;
use const STDOUT;
use const STR_PAD_LEFT;

require_once dirname(__DIR__) . '/vendor/autoload.php';

function replaceVersion(string $versionInfoPath, string $newVersion, bool $isDev, string $channel) : void{
	$versionInfo = Filesystem::fileGetContents($versionInfoPath);
	$versionInfo = preg_replace(
		$pattern = '/^([\t ]*public )?const BASE_VERSION = "(\d+)\.(\d+)\.(\d+)(?:-(.*))?";$/m',
		'$1const BASE_VERSION = "' . $newVersion . '";',
		$versionInfo
	);
	$versionInfo = preg_replace(
		'/^([\t ]*public )?const IS_DEVELOPMENT_BUILD = (?:true|false);$/m',
		'$1const IS_DEVELOPMENT_BUILD = ' . ($isDev ? 'true' : 'false') . ';',
		$versionInfo
	);
	$versionInfo = preg_replace(
		'/^([\t ]*public )?const BUILD_CHANNEL = ".*";$/m',
		'$1const BUILD_CHANNEL = "' . $channel . '";',
		$versionInfo
	);
	file_put_contents($versionInfoPath, $versionInfo);
}

const ACCEPTED_OPTS = [
	"current" => "Version to insert and tag",
	"next" => "Version to put in the file after tagging",
	"channel" => "Release channel to post this build into"
];

function systemWrapper(string $command, string $errorMessage) : void{
	system($command, $result);
	if($result !== 0){
		echo "error: $errorMessage; aborting\n";
		exit(1);
	}
}

function main() : void{
	$filteredOpts = [];
	foreach(Utils::stringifyKeys(getopt("", ["current:", "next:", "channel:", "help"])) as $optName => $optValue){
		if($optName === "help"){
			fwrite(STDOUT, "Options:\n");

			$maxLength = max(array_map(fn(string $str) => strlen($str), array_keys(ACCEPTED_OPTS)));
			foreach(ACCEPTED_OPTS as $acceptedName => $description){
				fwrite(STDOUT, str_pad("--$acceptedName", $maxLength + 4, " ", STR_PAD_LEFT) . ": $description\n");
			}
			exit(0);
		}
		if(!is_string($optValue)){
			fwrite(STDERR, "--$optName expects exactly 1 value\n");
			exit(1);
		}
		$filteredOpts[$optName] = $optValue;
	}

	$channel = $filteredOpts["channel"] ?? null;
	if(isset($filteredOpts["current"])){
		$currentVer = new VersionString($filteredOpts["current"]);
	}else{
		$currentVer = new VersionString(VersionInfo::BASE_VERSION);
	}

	$nextVer = isset($filteredOpts["next"]) ? new VersionString($filteredOpts["next"]) : null;

	$suffix = $currentVer->getSuffix();
	if($suffix !== ""){
		if($channel === "stable"){
			fwrite(STDERR, "error: cannot release a suffixed build into the stable channel\n");
			exit(1);
		}
		if(preg_match('/^([A-Za-z]+)(\d+)$/', $suffix, $matches) !== 1){
			echo "error: invalid current version suffix \"$suffix\"; aborting\n";
			exit(1);
		}
		$nextVer ??= new VersionString(sprintf(
			"%u.%u.%u-%s%u",
			$currentVer->getMajor(),
			$currentVer->getMinor(),
			$currentVer->getPatch(),
			$matches[1],
			((int) $matches[2]) + 1
		));
		$channel ??= strtolower($matches[1]);
	}else{
		$nextVer ??= new VersionString(sprintf(
			"%u.%u.%u",
			$currentVer->getMajor(),
			$currentVer->getMinor(),
			$currentVer->getPatch() + 1
		));
		$channel ??= "stable";
	}

	echo "About to tag version $currentVer. Next version will be $nextVer.\n";
	echo "$currentVer will be published on release channel \"$channel\".\n";
	echo "please add appropriate notes to the changelog and press enter...";
	fgets(STDIN);
	systemWrapper('git add "' . dirname(__DIR__) . '/changelogs"', "failed to stage changelog changes");
	system('git diff --cached --quiet "' . dirname(__DIR__) . '/changelogs"', $result);
	if($result === 0){
		echo "error: no changelog changes detected; aborting\n";
		exit(1);
	}
	$versionInfoPath = dirname(__DIR__) . '/src/VersionInfo.php';
	replaceVersion($versionInfoPath, $currentVer->getBaseVersion(), false, $channel);
	systemWrapper('git commit -m "Release ' . $currentVer->getBaseVersion() . '" --include "' . $versionInfoPath . '"', "failed to create release commit");
	systemWrapper('git tag ' . $currentVer->getBaseVersion(), "failed to create release tag");

	replaceVersion($versionInfoPath, $nextVer->getBaseVersion(), true, $channel);
	systemWrapper('git add "' . $versionInfoPath . '"', "failed to stage changes for post-release commit");
	systemWrapper('git commit -m "' . $nextVer->getBaseVersion() . ' is next" --include "' . $versionInfoPath . '"', "failed to create post-release commit");
}

main();
