<?php

/*

           -
         /   \
      /         \
   /    POCKET     \
/    MINECRAFT PHP    \
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

$fp = fopen(dirname(__FILE__)."/console.in","wb");
while(true){
	$l = fgets(STDIN);
	fwrite($fp, $l);
	if(strtolower(trim($l)) === "stop" and isset($argv[1]) and trim($argv[1]) == "1"){
		sleep(5);die();
	}
}