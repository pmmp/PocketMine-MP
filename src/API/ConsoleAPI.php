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
				case "crash": //Crashes the server to generate an report
					$this->callNotDefinedMethodCrash();
					$this->server->api->server; //Access a private property
					callNotExistingFunction();
					break;
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
					$this->server->api->chat->broadcast($s);
					break;
				case "save-all":
					$this->server->save();
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
		if(!isset($this->cmds[$cmd])){
			return false;
		}
		$this->cmds[strtolower(trim($alias))] = &$this->cmds[$cmd];
		return true;
	}

	public function register($cmd, $help, $callback){
		if(!is_callable($callback)){
			return false;
		}
		$cmd = strtolower(trim($cmd));
		$this->cmds[$cmd] = $callback;
		$this->help[$cmd] = $help;
	}
	
	public function run($line = ""){
		if($line != ""){
			$params = explode(" ", $line);
			$cmd = strtolower(array_shift($params));
			console("[INFO] Issued server command: /$cmd ".implode(" ", $params));
			if(isset($this->cmds[$cmd]) and is_callable($this->cmds[$cmd])){
				call_user_func($this->cmds[$cmd], $cmd, $params);
			}elseif($this->server->api->dhandle("api.console.command", array("cmd" => $cmd, "params" => $params)) !== false){
				$this->defaultCommands($cmd, $params);
			}
		}
	}

	public function handle($time){
		if($this->loop->line !== false){
			$line = trim($this->loop->line);
			$this->loop->line = false;
			$this->run($line);
		}else{
			$this->loop->notify();
		}
	}

}

class ConsoleLoop extends Thread{
	public $line, $stop;
	public function __construct(){
		$this->line = false;
		$this->stop = false;
	}
	
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