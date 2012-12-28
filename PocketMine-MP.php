<?php

/*

           -

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Lesser General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.


*/

require_once("src/common/dependencies.php");
require_once("classes/PocketMinecraftServer.class.php");
require_once("API/ServerAPI.php");

while(true){
	$server = new ServerAPI();
	//You can add simple things here

	if($server->start() !== true){
		break;
	}else{
		console("[INFO] Cleaning up...");
		hard_unset($server);
		$excludeList = array("GLOBALS", "_FILES", "_COOKIE", "_POST", "_GET", "excludeList");
		foreach(get_defined_vars() as $key => $value){
			if(!in_array($key, $excludeList)){
				$$key = null;
				unset($$key);
			}
		}
		$server = null;
		unset($server);
		console("[NOTICE] The server is restarting... (".gc_collect_cycles()." cycles collected)", true, true, 0);
	}
}
