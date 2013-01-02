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

class ConsoleAPI{
	private $loop, $server, $event, $help, $cmds;
	function __construct(PocketMinecraftServer $server){
		$this->help = array();
		$this->cmds = array();
		$this->server = $server;
		$this->last = microtime(true);
	}
	
	public function init(){
		$this->event = $this->server->event("server.tick", array($this, "handle"));
		$this->loop = new ConsoleLoop;
		$this->loop->start();
	}
	
	function __destruct(){
		$this->server->deleteEvent($this->event);
		$this->loop->stop = true;
		$this->loop->notify();
		$this->loop->join();
	}
	
	public function defaultCommands($cmd, $params){
			switch($cmd){
				case "invisible":
					$p = strtolower(array_shift($params));
					switch($p){
						case "on":
						case "true":
						case "1":
							console("[INFO] Server is invisible");
							$this->server->api->setProperty("invisible", true);
							break;
						case "off":
						case "false":
						case "0":
							console("[INFO] Server is visible");
							$this->server->api->setProperty("invisible", false);
							break;
						default:
							console("[INFO] Usage: /invisible <on | off>");
							break;
					}
					break;
				case "status":
				case "lag":
					$info = $this->server->debugInfo();
					console("[INFO] TPS: ".$info["tps"].", Memory usage: ".$info["memory_usage"]." (Peak ".$info["memory_peak_usage"].")");
					break;
				case "update-done":
					$this->server->api->setProperty("last-update", time());
					break;
				case "stop":
					$this->loop->stop = true;
					console("[INFO] Stopping the server...");					
					$this->server->close();
					break;
				/*case "restart":
					console("[INFO] Restarting the server...");
					$this->server->api->restart = true;
					$this->server->close();
					break;*/
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
							console("[INFO] Usage: /whitelist <on | off | add | remove | reload | list> [username]");
							break;
					}
					break;
				case "save-all":
					$this->server->save();
					break;
				case "block":
					foreach($this->server->clients as $client){
						$b = $this->server->map->getBlock(round($client->entity->position["x"] - 0.5), round($client->entity->position["y"] - 1), round($client->entity->position["z"] - 0.5));
						console("[INFO] EID ".$client->eid." is over block ".$b[0].":".$b[1]);
					}
					break;
				case "help":
				case "?":
					console("[INFO] /help: Show available commands");
					console("[INFO] /status: Show server TPS and memory usage");
					console("[INFO] /gamemode: Changes default gamemode");
					console("[INFO] /difficulty: Changes difficulty");
					console("[INFO] /invisible: Manages server visibility");
					console("[INFO] /say: Broadcasts mesages");
					console("[INFO] /save-all: Saves pending changes");
					console("[INFO] /whitelist: Manages whitelisting");
					console("[INFO] /banip: Manages IP ban");
					console("[INFO] /stop: Stops the server");
					//console("[INFO] /restart: Restarts the server");
					foreach($this->help as $c => $h){
						console("[INFO] /$c: ".$h);
					}
					break;
				default:
					console("[ERROR] Command doesn't exist! Use /help");
					break;
			}
	}
	
	public function alias($alias, $cmd){
		$this->cmds[strtolower(trim($alias))] = &$this->cmds[$cmd];
	}
	
	public function register($cmd, $help, $callback){
		if(!is_callable($callback)){
			return false;
		}
		$cmd = strtolower(trim($cmd));
		$this->cmds[$cmd] = $callback;
		$this->help[$cmd] = $help;
	}
	
	public function handle($time){
		if($this->loop->line !== false){
			$line = trim($this->loop->line);
			$this->loop->line = false;
			if($line !== ""){
				$params = explode(" ", $line);
				$cmd = strtolower(array_shift($params));
				console("[INFO] Issued server command: /$cmd ".implode(" ", $params));
				if(isset($this->cmds[$cmd]) and is_callable($this->cmds[$cmd])){
					call_user_func($this->cmds[$cmd], $cmd, $params);
				}elseif($this->server->trigger("api.console.command", array("cmd" => $cmd, "params" => $params)) !== false){
					$this->defaultCommands($cmd, $params);
				}
			}
		}else{
			$this->loop->notify();
		}
	}

}

class ConsoleLoop extends Thread{
	var $line = false, $stop = false;
	public function run(){
		$fp = fopen("php://stdin", "r");
		while($this->stop === false and ($line = fgets($fp)) !== false){
			$this->line = $line;
			$this->wait();
			$this->line = false;
		}
		exit(0);
	}
}