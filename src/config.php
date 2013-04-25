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

set_time_limit(0);
date_default_timezone_set(@date_default_timezone_get());
gc_enable();
error_reporting(E_ALL ^ E_NOTICE);
ini_set("allow_url_fopen", 1);
ini_set("display_errors", 1);
ini_set('default_charset', 'utf-8');
if(defined("POCKETMINE_COMPILE") and POCKETMINE_COMPILE === true){
	define("FILE_PATH", realpath(dirname(__FILE__))."/");
}else{
	define("FILE_PATH", realpath(dirname(__FILE__)."/../")."/");
}
set_include_path(get_include_path() . PATH_SEPARATOR . FILE_PATH);

ini_set("memory_limit", "256M"); //Default
define("LOG", true);
define("START_TIME", microtime(true));
define("TEST_MD5", "1e0d28177b73dfd09f922502fe767bec");
define("MAJOR_VERSION", "Alpha_1.3dev");
define("CURRENT_STRUCTURE", 5);
define("CURRENT_PROTOCOL", 9);
define("CURRENT_MINECRAFT_VERSION", "0.6.1 alpha");
define("CURRENT_API_VERSION", 6);
define("CURRENT_PHP_VERSION", "5.5");
$gitsha1 = false;
if(file_exists(FILE_PATH.".git/refs/heads/master")){ //Found Git information!
	define(GIT_COMMIT, strtolower(trim(file_get_contents(FILE_PATH.".git/refs/heads/master"))));
}else{ //Unknown :(
	define(GIT_COMMIT, str_repeat("00", 20));
}