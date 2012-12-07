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
require_once("classes/PocketMinecraftServer.class.php");
file_put_contents("packets.log", "");

if(!file_exists(FILE_PATH."white-list.txt")){
	console("[WARNING] No white-list.txt found, creating blank file");
	file_put_contents(FILE_PATH."white-list.txt", "");
}

if(!file_exists(FILE_PATH."banned-ips.txt")){
	console("[WARNING] No banned-ips.txt found, creating blank file");
	file_put_contents(FILE_PATH."banned-ips.txt", "");
}

if(!file_exists(FILE_PATH."server.properties")){
	console("[WARNING] No server.properties found, using default settings");
	copy(FILE_PATH."common/default.properties", FILE_PATH."server.properties");
}

@mkdir(FILE_PATH."data/entities/", 0777, true);
@mkdir(FILE_PATH."data/players/", 0777);

$prop = file_get_contents(FILE_PATH."server.properties");
$prop = explode("\n", str_replace("\r", "", $prop));
$config = array();
foreach($prop as $line){
	if(trim($line) == "" or $line{0} == "#"){
		continue;
	}
	$d = explode("=", $line);
	$n = strtolower(array_shift($d));
	$v = implode("=", $d);
	switch($n){
		case "protocol":
			if(trim($v) == "CURRENT"){
				$v = CURRENT_PROTOCOL;
				break;
			}
		case "gamemode":
		case "max-players":
		case "port":
		case "debug":
		case "time-per-second":
			$v = (int) $v;
			break;
		case "seed":
		case "server-id":
			$v = trim($v);
			$v = $v == "false" ? false:(preg_match("/[^0-9\-]/", $v) > 0 ? Utils::readInt(substr(md5($v, true), 0, 4)):$v);
			break;
		case "spawn":
			$v = explode(";", $v);
			$v = array("x" => floatval($v[0]), "y" => floatval($v[1]), "z" => floatval($v[2]));
			break;
		case "white-list":
		case "regenerate-config":
			$v = trim($v) == "true" ? true:false;
			break;
	}
	$config[$n] = $v;
}
define("DEBUG", $config["debug"]);

$server = new PocketMinecraftServer($config["server-name"], $config["gamemode"], $config["seed"], $config["protocol"], $config["port"], $config["server-id"]);
$server->setType($config["server-type"]);
$server->timePerSecond = $config["time-per-second"];
$server->maxClients = $config["max-players"];
$server->description = $config["description"];
$server->motd = $config["motd"];
$server->spawn = $config["spawn"];
$server->whitelist = $config["white-list"];
$server->reloadConfig();

if($config["regenerate-config"] == true){
	$config["seed"] = $server->seed;
	$config["server-id"] = $server->serverID;
	$config["regenerate-config"] = "false";
	$config["white-list"] = $config["whitelist"] === true ? "true":"false";
	$config["spawn"] = implode(";", $config["spawn"]);
	$prop = "#Pocket Minecraft PHP server properties\r\n#".date("D M j H:i:s T Y")."\r\n";
	foreach($config as $n => $v){
		$prop .= $n."=".$v."\r\n";
	}
	file_put_contents(FILE_PATH."server.properties", $prop);
}

$server->start();