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

require_once("common/dependencies.php");
require_once("classes/PocketMinecraftClient.class.php");
file_put_contents("packets.log", "");

$client = new PocketMinecraftClient("shoghicp");
$list = $client->getServerList();
foreach($list as $i => $info){
	console("[Server] #".$i." ".$info["ip"]." ".$info["username"]);
}
console("[Select Server] #", false, false);
$i = (int) trim(fgets(STDIN));
if(isset($list[$i])){
	$client->start($list[$i]["ip"]);
}else{
	console("[Error] Unknown ID");
}