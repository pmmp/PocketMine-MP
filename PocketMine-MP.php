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


/***REM_START***/
require_once(dirname(__FILE__)."/src/config.php");
require_once(FILE_PATH."/src/functions.php");
require_once(FILE_PATH."/src/dependencies.php");
/***REM_END***/

$server = new ServerAPI();
$server->run();


kill(getmypid()); //Fix for segfault
