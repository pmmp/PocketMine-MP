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

class ConsoleAPI{
	private $input, $server, $event;
	function __construct($server){
		$this->server = $server;
		$this->input = fopen(FILE_PATH."console.in", "w+b");
		$this->event = $this->server->event("onTick", array($this, "handle"));
	}
	
	function __destroy(){
		$this->server->deleteEvent("onTick", $this->event);
		fclose($this->input);
	}
	
	public function handle(){
		while(($line = fgets($this->input)) !== false){
			$line = trim($line);
			if($line === ""){
				continue;
			}
			$params = explode(" ", $line);
			$cmd = strtolower(array_shift($params));
			console("[INFO] Issued server command: /$cmd ".implode(" ", $params));
			switch($cmd){
				case "stop":
					console("[INFO] Stopping server...");
					$this->server->close();
					break;
				case "banip":
					$p = strtolower(array_shift($params));
					switch($p){
						case "pardon":
						case "remove":
							$ip = trim(implode($params));
							$new = array();					
							foreach(explode("\n", str_replace(array("\r","\t"), "", file_get_contents(FILE_PATH."banned-ips.txt"))) as $i){
								if($i == $ip){
									console("[INFO] IP \"$ip\" removed from ban list");
									continue;
								}
								$new[$i] = $i;
							}
							file_put_contents(FILE_PATH."banned-ips.txt", implode("\r\n", $new));
							$this->server->reloadConfig();
							break;
						case "add":
						case "ban":
							$ip = trim(implode($params));
							file_put_contents(FILE_PATH."banned-ips.txt", "\r\n".$ip, FILE_APPEND);
							console("[INFO] IP \"$ip\" added to ban list");
							$this->server->reloadConfig();
							break;
						case "reload":
							$this->server->reloadConfig();
							break;
						case "list":
							console("[INFO] IP ban list: ".implode(", ", explode("\n", str_replace(array("\t","\r"), "", file_get_contents(FILE_PATH."banned-ips.txt")))));
							break;
						default:
							console("[INFO] Usage: /banip <add | remove | list | reload> [IP]");
							break;
					}
					break;
				case "gamemode":
					$s = trim(array_shift($params));
					if($s == "" or (((int) $s) !== 0 and ((int) $s) !== 1)){
						console("[INFO] Usage: /gamemode <0 | 1>");
						break;
					}
					$this->server->api->setProperty("gamemode", (int) $s);
					console("[INFO] Gamemode changed to ".$this->server->gamemode);
					break;
				case "difficulty":
					$s = trim(array_shift($params));
					if($s == "" or (((int) $s) !== 0 and ((int) $s) !== 1)){
						console("[INFO] Usage: /difficulty <0 | 1>");
						break;
					}
					$this->server->api->setProperty("difficulty", (int) $s);
					console("[INFO] Difficulty changed to ".$this->server->difficulty);
					loadConfig(true);
					break;
				case "say":
					$s = implode(" ", $params);
					if(trim($s) == ""){
						console("[INFO] Usage: /say <message>");
						break;
					}
					$this->server->chat(false, $s);
					break;
				case "time":
					$p = strtolower(array_shift($params));
					switch($p){
						case "check":
							$time = abs($this->server->time) % 19200;
							$hour = str_pad(strval((floor($time /800) + 6) % 24), 2, "0", STR_PAD_LEFT).":".str_pad(strval(floor(($time % 800) / 13.33)), 2, "0", STR_PAD_LEFT);
							if($time < 9500){
								$time = "daytime";
							}elseif($time < 10900){
								$time = "sunset";
							}elseif($time < 17800){
								$time = "night";
							}else{
								$time = "sunrise";
							}
							console("[INFO] Time: $hour, $time (".$this->server->time.")");
							break;
						case "add":
							$t = (int) array_shift($params);
							$this->server->time += $t;
							break;
						case "set":
							$t = (int) array_shift($params);
							$this->server->time = $t;					
							break;
						case "sunrise":
							$this->server->time = 17800;
							break;
						case "day":
							$this->server->time = 0;
							break;
						case "sunset":
							$this->server->time = 9500;
							break;
						case "night":
							$this->server->time = 10900;
							break;
						default:
							console("[INFO] Usage: /time <check | set | add | sunrise | day | sunset | night> [time]");
							break;
					}
					break;
				case "whitelist":
					$p = strtolower(array_shift($params));
					switch($p){
						case "remove":
							$user = trim(implode(" ", $params));
							$new = array();					
							foreach(explode("\n", str_replace(array("\r","\t"), "", file_get_contents(FILE_PATH."white-list.txt"))) as $u){
								if($u == $user){
									console("[INFO] Player \"$user\" removed from white-list");
									continue;
								}
								$new[$u] = $u;
							}
							file_put_contents(FILE_PATH."white-list.txt", implode("\r\n", $new));
							$this->server->reloadConfig();
							break;
						case "add":
							$user = trim(implode(" ", $params));
							file_put_contents(FILE_PATH."white-list.txt", "\r\n".$user, FILE_APPEND);
							console("[INFO] Player \"$user\" added to white-list");
							$this->server->reloadConfig();
							break;
						case "reload":
							$this->server->reloadConfig();
							break;
						case "list":
							console("[INFO] White-list: ".implode(", ", explode("\n", str_replace(array("\t","\r"), "", file_get_contents(FILE_PATH."white-list.txt")))));
							break;
						case "on":
						case "true":
						case "1":
							console("[INFO] White-list turned on");
							$this->server->api->setProperty("white-list", true);
							break;
						case "off":
						case "false":
						case "0":
							console("[INFO] White-list turned off");
							$this->server->api->setProperty("white-list", false);
							break;
						default:
							console("[INFO] Usage: /whitelist <on | off | add | reload | list> [username]");
							break;
					}
					break;
				case "save-all":
					$this->server->save();
					break;
				case "block":
					foreach($this->server->clients as $client){
						$b = $this->server->map->getBlock($client->entity->position["x"], $client->entity->position["y"] - 2, $client->entity->position["z"]);
						console("[INFO] EID ".$client->eid." is over block ".$b[0].":".$b[1]);
					}
					break;
				case "list":
					console("[INFO] Player list:");
					foreach($this->server->clients as $c){
						console("[INFO] ".$c->username." (".$c->ip.":".$c->port."), ClientID ".$c->clientID);
					}
					break;
				case "help":
				case "?":
					console("[INFO] /help: Show available commands");
					console("[INFO] /gamemode: Changes default gamemode");
					console("[INFO] /difficulty: Changes difficulty");
					console("[INFO] /say: Broadcasts mesages");
					console("[INFO] /time: Manages time");
					console("[INFO] /list: Lists online users");
					console("[INFO] /save-all: Saves pending changes");
					console("[INFO] /whitelist: Manages whitelisting");
					console("[INFO] /banip: Manages IP ban");
					console("[INFO] /stop: Stops the server");
					break;
				default:
					console("[ERROR] Command doesn't exist! Use /help");
					break;
			}
		}
		ftruncate($this->input, 0);
		fseek($this->input, 0);		
	}

}