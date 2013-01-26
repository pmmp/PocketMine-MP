<?php

/*

           -
         /   \
      /         \
   /   PocketMine  \
/          MP         \
|\     @shoghicp     /|
|.   \           /   .|
| ..     \   /     .. |
|    ..    |    ..    |
|       .. | ..       |
\          |          /
   \       |       /
      \    |    /
         \ | /

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Lesser General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.


*/

require_once(dirname(__FILE__)."/config.php");
require_once("common/functions.php");
if(strpos(strtoupper(php_uname("s")), "WIN") === false or arg("enable-ansi", false) === true){
	define("ENABLE_ANSI", true);
}else{
	define("ENABLE_ANSI", false);
}
set_error_handler("fatal_handler", E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_RECOVERABLE_ERROR | E_DEPRECATED);

$errors = 0;

if(version_compare("5.3.3", PHP_VERSION) > 0){
	console("[ERROR] Use PHP >= 5.3.3", true, true, 0);
	++$errors;
}elseif(version_compare("5.5.0", PHP_VERSION) <= 0){
	console("[NOTICE] PocketMine-MP hasn't been tested with PHP >= 5.5", true, true, 0);
	++$errors;
}

if(version_compare("5.4.0", PHP_VERSION) > 0){
	console("[NOTICE] Use PHP >= 5.4.0 to increase performance", true, true, 0);
	define("HEX2BIN", false);
}else{
	define("HEX2BIN", true);
}

if(php_sapi_name() !== "cli"){
	console("[ERROR] Use PHP-CLI to execute the library or create your own", true, true, 0);
	++$errors;
}

if(!extension_loaded("sockets") and @dl((PHP_SHLIB_SUFFIX === "dll" ? "php_":"") . "sockets." . PHP_SHLIB_SUFFIX) === false){
	console("[ERROR] Unable to find Socket extension", true, true, 0);
	++$errors;
}

if(!extension_loaded("pthreads") and @dl((PHP_SHLIB_SUFFIX === "dll" ? "php_":"") . "pthreads." . PHP_SHLIB_SUFFIX) === false){
	console("[ERROR] Unable to find pthreads extension. Use the Installer available in the Homepage", true, true, 0);
	++$errors;
}

if(!extension_loaded("curl") and @dl((PHP_SHLIB_SUFFIX === "dll" ? "php_":"") . "curl." . PHP_SHLIB_SUFFIX) === false){
	console("[ERROR] Unable to find cURL extension", true, true, 0);
	++$errors;
}

if(!extension_loaded("sqlite3") and @dl((PHP_SHLIB_SUFFIX === "dll" ? "php_":"") . "sqlite3." . PHP_SHLIB_SUFFIX) === false){
	console("[ERROR] Unable to find SQLite3 extension", true, true, 0);
	++$errors;
}

if(!extension_loaded("zlib") and @dl((PHP_SHLIB_SUFFIX === "dll" ? "php_":"") . "zlib." . PHP_SHLIB_SUFFIX) === false){
	console("[ERROR] Unable to find Zlib extension", true, true, 0);
	++$errors;
}

if($errors > 0){
	die();
}

require_all(FILE_PATH . "src/classes/");

?>