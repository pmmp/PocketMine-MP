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

namespace pocketmine\build\generate_known_translation_keys;

use Webmozart\PathUtil\Path;
use function dirname;

require dirname(__DIR__) . '/vendor/autoload.php';

function constantify(string $permissionName) : string{
	return strtoupper(str_replace([".", "-"], "_", $permissionName));
}
ob_start();

echo <<<'HEADER'
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

namespace pocketmine\lang;

/**
 * This class contains constants for all the translations known to PocketMine-MP as per the used version of pmmp/Language.
 * This class is generated automatically, do NOT modify it by hand.
 */
final class KnownTranslationKeys{

HEADER;

$perms = [];

$lang = parse_ini_file(Path::join(\pocketmine\RESOURCE_PATH, "locale", "eng.ini"), false, INI_SCANNER_RAW);
if($lang === false){
	fwrite(STDERR, "Missing language files!\n");
	exit(1);
}

foreach($lang as $k => $v){
		$perms[] = $k;
}

sort($perms, SORT_STRING);
foreach($perms as $perm){
		echo "\tpublic const ";
		echo constantify($perm);
		echo " = \"" . $perm . "\";\n";
}

echo "}\n";

file_put_contents(dirname(__DIR__) . '/src/lang/KnownTranslationKeys.php', ob_get_clean());

echo "Done.\n";
