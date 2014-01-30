<?php

/**
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

/***REM_START***/
require_once(dirname(__FILE__)."/config.php");
require_once(FILE_PATH."/src/utils/TextFormat.php");
require_once(FILE_PATH."/src/functions.php");
/***REM_END***/
define("DATA_PATH", realpath(arg("data-path", FILE_PATH))."/");

if(arg("enable-ansi", strpos(strtoupper(php_uname("s")), "WIN") === 0 ? false:true) === true and arg("disable-ansi", false) !== true){
	define("ENABLE_ANSI", true);
}else{
	define("ENABLE_ANSI", false);
}

set_error_handler("error_handler", E_ALL);

$errors = 0;

if(version_compare("5.4.0", PHP_VERSION) > 0){
	console("[ERROR] Use PHP >= 5.4.0", true, true, 0);
	++$errors;
}

if(php_sapi_name() !== "cli"){
	console("[ERROR] You must run PocketMine-MP using the CLI.", true, true, 0);
	++$errors;
}

if(!extension_loaded("sockets") and @dl((PHP_SHLIB_SUFFIX === "dll" ? "php_":"") . "sockets." . PHP_SHLIB_SUFFIX) === false){
	console("[ERROR] Unable to find the Socket extension.", true, true, 0);
	++$errors;
}

if(!extension_loaded("pthreads") and @dl((PHP_SHLIB_SUFFIX === "dll" ? "php_":"") . "pthreads." . PHP_SHLIB_SUFFIX) === false){
	console("[ERROR] Unable to find the pthreads extension.", true, true, 0);
	++$errors;
}else{
	$pthreads_version = phpversion("pthreads");
	if(substr_count($pthreads_version, ".") < 2){
		$pthreads_version = "0.$pthreads_version";
	}
	if(version_compare($pthreads_version, "0.1.0") < 0){
		console("[ERROR] pthreads >= 0.1.0 is required, while you have $pthreads_version.", true, true, 0);
		++$errors;
	}	
}

if(!extension_loaded("curl") and @dl((PHP_SHLIB_SUFFIX === "dll" ? "php_":"") . "curl." . PHP_SHLIB_SUFFIX) === false){
	console("[ERROR] Unable to find the cURL extension.", true, true, 0);
	++$errors;
}

if(!extension_loaded("sqlite3") and @dl((PHP_SHLIB_SUFFIX === "dll" ? "php_":"") . "sqlite3." . PHP_SHLIB_SUFFIX) === false){
	console("[ERROR] Unable to find the SQLite3 extension.", true, true, 0);
	++$errors;
}

if(!extension_loaded("yaml") and @dl((PHP_SHLIB_SUFFIX === "dll" ? "php_":"") . "yaml." . PHP_SHLIB_SUFFIX) === false){
	console("[ERROR] Unable to find the YAML extension.", true, true, 0);
	++$errors;
}

if(!extension_loaded("zlib") and @dl((PHP_SHLIB_SUFFIX === "dll" ? "php_":"") . "zlib." . PHP_SHLIB_SUFFIX) === false){
	console("[ERROR] Unable to find the Zlib extension.", true, true, 0);
	++$errors;
}

if($errors > 0){
	console("[ERROR] Please use the installer provided on the homepage, or recompile PHP again.", true, true, 0);
	exit(1); //Exit with error
}

$sha1sum = "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0";
/***REM_START***/
require_once(FILE_PATH."/src/math/Vector3.php");
require_once(FILE_PATH."/src/world/Position.php");
require_once(FILE_PATH."/src/pmf/PMF.php");

require_all(FILE_PATH . "src/");

$inc = get_included_files();
$inc[] = array_shift($inc);
$srcdir = realpath(FILE_PATH."src/");
foreach($inc as $s){
	if(strpos(realpath(dirname($s)), $srcdir) === false and strtolower(basename($s)) !== "pocketmine-mp.php"){
		continue;
	}
	$sha1sum ^= sha1_file($s, true);
}
/***REM_END***/
define("SOURCE_SHA1SUM", bin2hex($sha1sum));

/***REM_START***/
if(!file_exists(DATA_PATH."server.properties") and arg("no-wizard", false) != true){
	$installer = new Installer();
}
/***REM_END***/