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
	private $loop, $server, $event, $help, $cmds, $alias;
	function __construct(PocketMinecraftServer $server){
		$this->help = array();
		$this->cmds = array();
		$this->alias = array();
		$this->server = $server;
		$this->last = microtime(true);
	}

	public function init(){
		$this->event = $this->server->event("server.tick", array($this, "handle"));
		$this->loop = new ConsoleLoop;
		$this->loop->start();
		$this->register("help", "Show available commands", array($this, "defaultCommands"));
		$this->register("status", "Show server TPS and memory usage", array($this, "defaultCommands"));
		$this->alias("lag", "status");
		$this->register("difficulty", "Changes server difficulty", array($this, "defaultCommands"));
		$this->register("invisible", "Changes server visibility", array($this, "defaultCommands"));
		$this->register("say", "Broadcast a message", array($this, "defaultCommands"));
		$this->register("save-all", "Save pending changes to disk", array($this, "defaultCommands"));
		$this->register("stop", "Stops the server gracefully", array($this, "defaultCommands"));
	}

	function __destruct(){
		$this->server->deleteEvent($this->event);
		$this->loop->stop = true;
		$this->loop->notify();
		$this->loop->join();
	}

	public function defaultCommands($cmd, $params, $issuer, $alias){
			$output = "";
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
								$output .= "Server is invisible\n";
								$this->server->api->setProperty("invisible", true);
								break;
							case "off":
							case "false":
							case "0":
								$output .= "Server is visible\n";
								$this->server->api->setProperty("invisible", false);
								break;
							default:
								$output .= "Usage: /invisible <on | off>\n";
								break;
						}
						break;
					case "status":
					case "lag":
						if(!($issuer instanceof Player) and $issuer === "console"){
							$this->server->debugInfo(true);
						}
						$info = $this->server->debugInfo();
						$output .= "TPS: ".$info["tps"].", Memory usage: ".$info["memory_usage"]." (Peak ".$info["memory_peak_usage"].")\n";
						break;
					case "update-done":
						$this->server->api->setProperty("last-update", time());
						break;
					case "stop":
						$this->loop->stop = true;
						$output .= "Stopping the server\n";
						$this->server->close();
						break;
					case "difficulty":
						$s = trim(array_shift($params));
						if($s == "" or (((int) $s) !== 0 and ((int) $s) !== 1)){
							$output .= "Usage: /difficulty <0 | 1>\n";
							break;
						}
						$this->server->api->setProperty("difficulty", (int) $s);
						$output .= "Difficulty changed to ".$this->server->difficulty."\n";
						loadConfig(true);
						break;
					case "say":
						$s = implode(" ", $params);
						if(trim($s) == ""){
							$output .= "Usage: /say <message>\n";
							break;
						}
						$this->server->api->chat->broadcast("[Server] ".$s);
						break;
					case "save-all":
						$this->server->save();
						break;
						
					case "?":
						if($issuer !== "console"){
							break;
						}
					case "help":
						$max = ceil(count($this->help) / 5);
						$page = isset($params[0]) ? min($max - 1, max(0, intval($params[0]) - 1)):0;						
						$output .= "- Showing help page ". ($page + 1) ." of $max (/help <page>) -\n";
						$current = 0;
						foreach($this->help as $c => $h){
							$curpage = (int) ($current / 5);
							if($curpage === $page){
								$output .= "/$c: ".$h."\n";
							}elseif($curpage > $page){
								break;
							}							
							++$current;
						}
						break;
					default:
						$output .= "Command doesn't exist! Use /help\n";
						break;
				}
		return $output;
	}

	public function alias($alias, $cmd){
		$this->alias[strtolower(trim($alias))] = trim($cmd);
		return true;
	}

	public function register($cmd, $help, $callback){
		if(!is_callable($callback)){
			return false;
		}
		$cmd = strtolower(trim($cmd));
		$this->cmds[$cmd] = $callback;
		$this->help[$cmd] = $help;
		ksort($this->help, SORT_NATURAL | SORT_FLAG_CASE);
	}
	
	public function run($line = "", $issuer = false, $alias = false){
		if($line != ""){
			$params = explode(" ", $line);
			$cmd = strtolower(array_shift($params));
			if(isset($this->alias[$cmd])){
				$this->run($this->alias[$cmd] . " " .implode(" ", $params), $issuer, $cmd);
				return;
			}
			if($issuer instanceof Player){
				console("[INFO] \"".$issuer->username."\" issued server command: $alias /$cmd ".implode(" ", $params));
			}else{
				console("[INFO] Issued server command: $alias /$cmd ".implode(" ", $params));
			}
			if($this->server->api->dhandle("console.command.".$cmd, array("cmd" => $cmd, "parameters" => $params, "issuer" => $issuer, "alias" => $alias)) === false
			or $this->server->api->dhandle("console.command", array("cmd" => $cmd, "parameters" => $params, "issuer" => $issuer, "alias" => $alias)) === false){
				$output = "You don't have permissions\n";
			}else{
				if(isset($this->cmds[$cmd]) and is_callable($this->cmds[$cmd])){
					$output = @call_user_func($this->cmds[$cmd], $cmd, $params, $issuer, $alias);
				}elseif($this->server->api->dhandle("console.command.unknown", array("cmd" => $cmd, "params" => $params, "issuer" => $issuer, "alias" => $alias)) !== false){
					$output = $this->defaultCommands($cmd, $params, $issuer, $alias);
				}
			}
			if($output != "" and ($issuer instanceof Player)){
				$issuer->sendChat(trim($output));
			}elseif($output != "" and $issuer === "console"){
				$mes = explode("\n", trim($output));
				foreach($mes as $m){
					console("[CMD] ".$m);	
				}
				
			}
		}
	}

	public function handle($time){
		if($this->loop->line !== false){
			$line = trim($this->loop->line);
			$this->loop->line = false;
			$this->run($line, "console");
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