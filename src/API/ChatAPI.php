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

class ChatAPI{
	private $server;
	function __construct(){
		$this->server = ServerAPI::request();
	}
	
	public function init(){
		$this->server->api->console->register("tell", "Sends a private message to a player", array($this, "commandHandler"));
		$this->server->api->console->register("me", "Broadcast an action", array($this, "commandHandler"));
	}
	
	public function commandHandler($cmd, $params, $issuer, $alias){
		$output = "";
		switch($cmd){
			case "me":
				if(!($issuer instanceof Player)){
					$sender = "Console";
				}else{
					$sender = $issuer->username;
				}
				$this->broadcast("* $sender ".implode(" ", $params));
				break;
			case "tell":
				if(!isset($params[0]) or !isset($params[1])){
					$output .= "Usage: /$cmd <player> <message>\n";
					break;
				}
				if(!($issuer instanceof Player)){
					$sender = "Console";
				}else{
					$sender = $issuer->username;
				}
				$n = array_shift($params);
				$target = $this->server->api->player->get($n);
				if($target instanceof Player){
					$target = $target->username;
				}else{
					$target = strtolower($n);
					if($target === "server" or $target === "console"){
						$target = "Console";
					}else{
						$output .= "Usage: /$cmd <player> <message>\n";
						break;
					}
				}
				$mes = implode(" ", $params);
				$output .= "[me -> ".$target."] ".$mes."\n";
				if($target !== "Console"){
					$this->sendTo(false, "[".$sender." -> me] ".$mes, $target);
				}
				console("[CHAT] [".$sender." -> ".$target."] ".$mes);
				break;
		}
		return $output;
	}
	
	public function broadcast($message){
		$this->send(false, $message);
		console("[CHAT] ".$message);
	}
	
	public function sendTo($owner, $text, $player){
		$this->send($owner, $text, array($player));
	}
	
	public function send($owner, $text, $whitelist = false, $blacklist = false){
		$message = "";
		if($owner !== false){
			if($owner instanceof Player){
				$message = "<".$owner->username."> ";
			}else{
				$message = "<".$owner."> ";
			}
		}
		$message .= $text;
		$this->server->handle("server.chat", new Container($message, $whitelist, $blacklist));
	}
}