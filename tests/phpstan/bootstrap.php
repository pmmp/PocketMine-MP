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

if(!defined('LEVELDB_ZLIB_RAW_COMPRESSION')){
	//leveldb might not be loaded
	define('LEVELDB_ZLIB_RAW_COMPRESSION', 4);
}

//TODO: these need to be defined properly or removed
define('pocketmine\COMPOSER_AUTOLOADER_PATH', dirname(__DIR__, 2) . '/vendor/autoload.php');
define('pocketmine\DATA', '');
define('pocketmine\GIT_COMMIT', str_repeat('00', 20));
define('pocketmine\PLUGIN_PATH', '');
define('pocketmine\START_TIME', microtime(true));
define('pocketmine\VERSION', '9.9.9');
