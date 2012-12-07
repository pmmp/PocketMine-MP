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
	console("[NOTICE] No white-list.txt found, creating blank file");
	file_put_contents(FILE_PATH."white-list.txt", "");
}

if(!file_exists(FILE_PATH."banned-ips.txt")){
	console("[NOTICE] No banned-ips.txt found, creating blank file");
	file_put_contents(FILE_PATH."banned-ips.txt", "");
}

if(!file_exists(FILE_PATH."server.properties")){
	console("[NOTICE] No server.properties found, using default settings");
	copy(FILE_PATH."common/default.properties", FILE_PATH."server.properties");
}

@mkdir(FILE_PATH."data/players/", 0777, true);
@mkdir(FILE_PATH."data/maps/", 0777);


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
		case "level-name":
			$v = trim($v);
			$v = $v == "false" ? false:$v;
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

if(file_exists(FILE_PATH."data/maps/level.dat")){
	console("[NOTICE] Detected unimported map data. Importing...");
	$nbt = new NBT();
	$level = parseNBTData($nbt->loadFile(FILE_PATH."data/maps/level.dat"));
	console("[DEBUG] Importing map \"".$level["LevelName"]."\" gamemode ".$level["GameType"]." with seed ".$level["RandomSeed"], true, true, 2);
	unset($level["Player"]);
	$lvName = $level["LevelName"]."/";
	@mkdir(FILE_PATH."data/maps/".$lvName, 0777);	
	file_put_contents(FILE_PATH."data/maps/".$lvName."level.dat", serialize($level));
	$entities = parseNBTData($nbt->loadFile(FILE_PATH."data/maps/entities.dat"));
	file_put_contents(FILE_PATH."data/maps/".$lvName."entities.dat", serialize($entities["Entities"]));
	if(!isset($entities["TileEntities"])){
		$entities["TileEntities"] = array();
	}
	file_put_contents(FILE_PATH."data/maps/".$lvName."tileEntities.dat", serialize($entities["TileEntities"]));
	console("[DEBUG] Imported ".count($entities["Entities"])." Entities and ".count($entities["TileEntities"])." TileEntities", true, true, 2);
	rename(FILE_PATH."data/maps/chunks.dat", FILE_PATH."data/maps/".$lvName."chunks.dat");
	unlink(FILE_PATH."data/maps/level.dat");
	@unlink(FILE_PATH."data/maps/level.dat_old");
	unlink(FILE_PATH."data/maps/entities.dat");
	if($config["level-name"] === false){
		console("[INFO] Setting default level to \"".$level["LevelName"]."\"");
		$config["level-name"] = $level["LevelName"];
		$config["gamemode"] = $level["GameType"];
		$server->gamemode = $config["gamemode"];
		$server->seed = $level["RandomSeed"];
		$config["spawn"] = array("x" => $level["SpawnX"], "y" => $level["SpawnY"], "z" => $level["SpawnZ"]);
		$config["regenerate-config"] = true;
	}
	console("[INFO] Map \"".$level["LevelName"]."\" importing done!");
	unset($level, $entities, $nbt);
}
$server->setType($config["server-type"]);
$server->timePerSecond = $config["time-per-second"];
$server->maxClients = $config["max-players"];
$server->description = $config["description"];
$server->motd = $config["motd"];
$server->spawn = $config["spawn"];
$server->whitelist = $config["white-list"];
$server->mapName = $config["level-name"];
$server->mapDir = FILE_PATH."data/maps/".$config["level-name"]."/";
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