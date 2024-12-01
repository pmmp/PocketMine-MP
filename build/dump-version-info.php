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

use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\VersionInfo;

require dirname(__DIR__) . '/vendor/autoload.php';

/*
 * Dumps version info in a machine-readable format for use in GitHub Actions workflows
 */

/**
 * @var string[]|\Closure[] $options
 * @phpstan-var array<string, string|\Closure() : string> $options
 */
$options = [
	"base_version" => VersionInfo::BASE_VERSION,
	"major_version" => fn() => explode(".", VersionInfo::BASE_VERSION)[0],
	"mcpe_version" => ProtocolInfo::MINECRAFT_VERSION_NETWORK,
	"is_dev" => VersionInfo::IS_DEVELOPMENT_BUILD,
	"changelog_file_name" => function() : string{
		$version = VersionInfo::VERSION();
		$result = $version->getMajor() . "." . $version->getMinor();
		$suffix = $version->getSuffix();
		if($suffix !== ""){
			if(preg_match('/^([A-Za-z]+)(\d+)$/', $suffix, $matches) !== 1){
				fwrite(STDERR, "error: invalid current version suffix \"$suffix\"; aborting" . PHP_EOL);
				exit(1);
			}
			$baseSuffix = $matches[1];
			$result .= "-" . strtolower($baseSuffix);
		}
		return $result . ".md";
	},
	"changelog_md_header" => fn() : string => str_replace(".", "", VersionInfo::BASE_VERSION),
	"prerelease" => fn() : bool => VersionInfo::VERSION()->getSuffix() !== "",
	"channel" => VersionInfo::BUILD_CHANNEL,
	"suffix_valid" => function() : bool{
		//TODO: maybe this should be put into its own script?
		$suffix = VersionInfo::VERSION()->getSuffix();
		if(VersionInfo::BUILD_CHANNEL === "stable"){
			//stable builds may not have suffixes
			return $suffix === "";
		}
		if(VersionInfo::BUILD_CHANNEL === "alpha" || VersionInfo::BUILD_CHANNEL === "beta"){
			$upperChannel = strtoupper(VersionInfo::BUILD_CHANNEL);
			$upperSuffix = strtoupper($suffix);
			return str_starts_with($upperSuffix, $upperChannel) && is_numeric(substr($upperSuffix, strlen($upperChannel)));
		}
		return true;
	}
];
if(count($argv) !== 2 || !isset($options[$argv[1]])){
	fwrite(STDERR, "Please provide an option (one of: " . implode(", ", array_keys($options)) . PHP_EOL);
	exit(1);
}

$result = $options[$argv[1]];
if($result instanceof Closure){
	$result = $result();
}
if(is_bool($result)){
	echo $result ? "true" : "false";
}else{
	echo $result;
}
