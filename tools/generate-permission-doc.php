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

namespace pocketmine\generate_permission_doc;

use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\PermissionManager;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\VersionInfo;
use Webmozart\PathUtil\Path;
use function count;
use function dirname;
use function fclose;
use function fopen;
use function fwrite;
use function getcwd;
use function ksort;
use function str_replace;
use function strtolower;
use const SORT_STRING;
use const STDERR;

require dirname(__DIR__) . '/vendor/autoload.php';

function markdownify(string $name) : string{
	return str_replace(['.', '`', ' '], ['', '', '-'], strtolower($name));
}
DefaultPermissions::registerCorePermissions();

$cwd = getcwd();
if($cwd === false){
	throw new AssumptionFailedError("getcwd() returned false");
}
$output = Path::join($cwd, "core-permissions.md");
echo "Writing output to $output\n";
$doc = fopen($output, "wb");
if($doc === false){
	fwrite(STDERR, "Failed to open output file\n");
	exit(1);
}

$permissions = PermissionManager::getInstance()->getPermissions();
ksort($permissions, SORT_STRING);

fwrite($doc, "# PocketMine-MP Core Permissions\n");
fwrite($doc, "Generated from PocketMine-MP " . VersionInfo::VERSION()->getFullVersion() . "\n");
fwrite($doc, "\n");
fwrite($doc, "| Name | Description | Implied permissions |\n");
fwrite($doc, "|:-----|:------------|:-------------------:|\n");
foreach($permissions as $permission){
	$link = count($permission->getChildren()) === 0 ? "N/A" : "[Jump](#" . markdownify("Permissions implied by `" . $permission->getName() . "`") . ")";
	fwrite($doc, "| `" . $permission->getName() . "` | " . $permission->getDescription() . " | $link |\n");
}

fwrite($doc, "\n\n");
fwrite($doc, "## Implied permissions\n");
fwrite($doc, "Some permissions automatically grant (or deny) other permissions by default when granted. These are referred to as **implied permissions**.<br>\n");
fwrite($doc, "Permissions may imply permissions which in turn imply other permissions (e.g. `pocketmine.group.operator` implies `pocketmine.group.user`, which in turn implies `pocketmine.command.help`).<br>\n");
fwrite($doc, "Implied permissions can be overridden by explicit permissions from elsewhere.<br>\n");
fwrite($doc, "**Note:** When explicitly denied, implied permissions are inverted. This means that \"granted\" becomes \"denied\" and vice versa.\n");
fwrite($doc, "\n\n");
foreach($permissions as $permission){
	if(count($permission->getChildren()) === 0){
		continue;
	}
	fwrite($doc, "### Permissions implied by `" . $permission->getName() . "`\n");
	fwrite($doc, "Users granted this permission will also be granted/denied the following permissions implicitly:\n\n");

	fwrite($doc, "| Name | Type |\n");
	fwrite($doc, "|:-----|:----:|\n");
	$children = $permission->getChildren();
	ksort($children, SORT_STRING);
	foreach($children as $childName => $isGranted){
		fwrite($doc, "| `$childName` | " . ($isGranted ? "Granted" : "Denied") . " |\n");
	}
	fwrite($doc, "\n");
}

fclose($doc);
echo "Done.\n";
