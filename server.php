<?php

/*

           -

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Lesser General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.


*/

require_once("common/dependencies.php");
require_once("classes/PocketMinecraftServer.class.php");
require_once("classes/API/ServerAPI.php");

while(true){
	$server = new ServerAPI();
	//You can add simple things here

	if($server->start() !== true){
		break;
	}else{
		$server = null;
		console("[NOTICE] The server is restarting... (".gc_collect_cycles()." cycles collected)", true, true, 0);
	}
}
