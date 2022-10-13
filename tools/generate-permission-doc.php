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
use pocketmine\utils\Utils;
use pocketmine\VersionInfo;
use Symfony\Component\Filesystem\Path;
use function count;
use function dirname;
use function fclose;
use function fopen;
use function fwrite;
use function getcwd;
use function ksort;
use function str_repeat;
use function str_replace;
use function strlen;
use function strtolower;
use const SORT_STRING;
use const STDERR;

require dirname(__DIR__) . '/vendor/autoload.php';

if(count($argv) > 2){
	fwrite(STDERR, "Required arguments: md|rst\n");
	exit(1);
}
$format = $argv[1] ?? "md";
if($format !== "md" && $format !== "rst"){
	fwrite(STDERR, "Invalid format, expected either \"md\" or \"rst\"\n");
	exit(1);
}

function markdownify(string $name) : string{
	return str_replace(['.', '`', ' '], ['', '', '-'], strtolower($name));
}
DefaultPermissions::registerCorePermissions();

$cwd = Utils::assumeNotFalse(getcwd());
$output = Path::join($cwd, "core-permissions.$format");
echo "Writing output to $output\n";
$doc = fopen($output, "wb");
if($doc === false){
	fwrite(STDERR, "Failed to open output file\n");
	exit(1);
}

$permissions = PermissionManager::getInstance()->getPermissions();
ksort($permissions, SORT_STRING);

$title = "List of " . VersionInfo::NAME . " core permissions";
if($format === "md"){
	fwrite($doc, "# $title\n");
}else{
	fwrite($doc, ".. _corepermissions:\n\n");
	fwrite($doc, "$title\n");
	fwrite($doc, str_repeat("=", strlen($title)) . "\n\n");
}

fwrite($doc, "Generated from " . VersionInfo::NAME . " " . VersionInfo::VERSION()->getFullVersion() . "\n");
fwrite($doc, "\n");
if($format === "md"){
	fwrite($doc, "| Name | Description | Implied permissions |\n");
	fwrite($doc, "|:-----|:------------|:-------------------:|\n");
}else{
	fwrite($doc, ".. list-table::\n");
	fwrite($doc, "   :header-rows: 1\n\n");
	fwrite($doc, "   * - Name\n");
	fwrite($doc, "     - Description\n");
	fwrite($doc, "     - Implied permissions\n");
	fwrite($doc, "\n");
}
foreach($permissions as $permission){
	if($format === "md"){
		$link = count($permission->getChildren()) === 0 ? "N/A" : "[Jump](#" . markdownify("Permissions implied by `" . $permission->getName() . "`") . ")";
		fwrite($doc, "| `" . $permission->getName() . "` | " . $permission->getDescription() . " | $link |\n");
	}else{
		fwrite($doc, "   * - ``" . $permission->getName() . "``\n");
		fwrite($doc, "     - " . $permission->getDescription() . "\n");
		if(count($permission->getChildren()) === 0){
			fwrite($doc, "     - N/A\n");
		}else{
			fwrite($doc, "     - :ref:`Jump<permissions_implied_by_" . $permission->getName() . ">`\n");
		}
	}
}

fwrite($doc, "\n\n");

$title = "Implied permissions";
if($format === "md"){
	fwrite($doc, "## $title\n");
}else{
	fwrite($doc, "$title\n");
	fwrite($doc, str_repeat("-", strlen($title)) . "\n\n");
}
$newline = $format === "md" ? "<br>\n" : "\n\n";
$code = $format === "md" ? "`" : "``";
fwrite($doc, "Some permissions automatically grant (or deny) other permissions by default when granted. These are referred to as **implied permissions**.$newline");
fwrite($doc, "Permissions may imply permissions which in turn imply other permissions (e.g. {$code}pocketmine.group.operator{$code} implies {$code}pocketmine.group.user{$code}, which in turn implies {$code}pocketmine.command.help{$code}).$newline");
fwrite($doc, "Implied permissions can be overridden by explicit permissions from elsewhere.$newline");
fwrite($doc, "**Note:** When explicitly denied, implied permissions are inverted. This means that \"granted\" becomes \"denied\" and vice versa.$newline");
fwrite($doc, "\n\n");
foreach($permissions as $permission){
	if(count($permission->getChildren()) === 0){
		continue;
	}
	$title = "Permissions implied by " . $code . $permission->getName() . $code;
	if($format === "md"){
		fwrite($doc, "### $title\n");
	}else{
		fwrite($doc, ".. _permissions_implied_by_" . $permission->getName() . ":\n\n");
		fwrite($doc, "$title\n");
		fwrite($doc, str_repeat("~", strlen($title)) . "\n\n");
	}
	fwrite($doc, "Users granted this permission will also be granted/denied the following permissions implicitly:\n\n");

	if($format === "md"){
		fwrite($doc, "| Name | Type |\n");
		fwrite($doc, "|:-----|:----:|\n");
		$children = $permission->getChildren();
		ksort($children, SORT_STRING);
		foreach(Utils::stringifyKeys($children) as $childName => $isGranted){
			fwrite($doc, "| `$childName` | " . ($isGranted ? "Granted" : "Denied") . " |\n");
		}
	}else{
		fwrite($doc, ".. list-table::\n");
		fwrite($doc, "   :header-rows: 1\n\n");
		fwrite($doc, "   * - Name\n");
		fwrite($doc, "     - Type\n");
		$children = $permission->getChildren();
		ksort($children, SORT_STRING);
		foreach(Utils::stringifyKeys($children) as $childName => $isGranted){
			fwrite($doc, "   * - ``$childName``\n");
			fwrite($doc, "     - " . ($isGranted ? "Granted" : "Denied") . "\n");
		}
	}
	fwrite($doc, "\n");
}

fclose($doc);
echo "Done.\n";
