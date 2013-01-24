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

class BanAPI{
	private $server;
	private $whitelist;
	private $banned;
	private $bannedIPs;
	function __construct(PocketMinecraftServer $server){
		$this->server = $server;
	}
	
	public function init(){
		console("[INFO] Loading authentication lists...");
		$this->whitelist = new Config(FILE_PATH."white-list.txt", CONFIG_LIST);
		$this->bannedIPs = new Config(FILE_PATH."banned-ips.txt", CONFIG_LIST);
		$this->banned = new Config(FILE_PATH."banned.txt", CONFIG_LIST);
		$this->server->api->console->register("banip", "Manages IP Banning", array($this, "commandHandler"));
		$this->server->api->console->register("ban", "Manages Bannning", array($this, "commandHandler"));
		$this->server->api->console->register("kick", "Kicks a player", array($this, "commandHandler"));
		$this->server->api->console->register("whitelist", "Manages White-listing", array($this, "commandHandler"));
	}
	
	public function commandHandler($cmd, $params){
		switch($cmd){
			case "kick":
				if(!isset($params[0])){
					console("[INFO] Usage: /kick <playername> [reason]");
				}else{
					$name = array_shift($params);
					$player = $this->server->api->player->get($name);
					if($player === false){
						console("[ERROR] Player \"".$name."\" does not exist");
					}else{
						$reason = implode(" ", $params);
						$reason = $reason == "" ? "No reason":$reason;
						$player->close("You have been kicked: ".$reason);
						console("[INFO] Player \"".$player->username."\" has been kicked: $reason");
					}
				}
				break;
			case "whitelist":
				$p = strtolower(array_shift($params));
				switch($p){
					case "remove":
						$user = trim(implode(" ", $params));
						$this->whitelist->remove($user);
						$this->whitelist->save();
						console("[INFO] Player \"$user\" removed from white-list");
						break;
					case "add":
						$user = trim(implode(" ", $params));
						$this->whitelist->set($user);
						$this->whitelist->save();
						console("[INFO] Player \"$user\" added to white-list");
						break;
					case "reload":
						$this->whitelist = $this->load(FILE_PATH."white-list.txt", CONFIG_LIST);
						break;
					case "list":
						console("[INFO] White-list: ".implode(", ", $this->whitelist->getAll(true)));
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
			case "banip":
				$p = strtolower(array_shift($params));
				switch($p){
					case "pardon":
					case "remove":
						$ip = trim(implode($params));
						$this->bannedIPs->remove($ip);
						$this->bannedIPs->save();
						console("[INFO] IP \"$ip\" removed from ban list");
						break;
					case "add":
					case "ban":
						$ip = trim(implode($params));
						$this->bannedIPs->set($ip);
						$this->bannedIPs->save();
						console("[INFO] IP \"$ip\" added to ban list");
						break;
					case "reload":
						$this->bannedIPs = new Config(FILE_PATH."banned-ips.txt", CONFIG_LIST);
						break;
					case "list":
						console("[INFO] IP ban list: ".implode(", ", $this->bannedIPs->getAll(true)));
						break;
					default:
						console("[INFO] Usage: /banip <add | remove | list | reload> [IP]");
						break;
				}
				break;
			case "ban":
				$p = strtolower(array_shift($params));
				switch($p){
					case "pardon":
					case "remove":
						$user = trim(implode($params));
						$this->banned->remove($user);
						$this->banned->save();
						console("[INFO] Player \"$user\" removed from ban list");
						break;
					case "add":
					case "ban":
						$user = trim(implode($params));
						$this->banned->set($user);
						$this->banned->save();
						$player = $this->server->api->player->get($user);
						if($player !== false){
							$player->close("You have been banned");
						}
						$this->server->api->chat->broadcast("$user has been banned");
						console("[INFO] Player \"$user\" added to ban list");
						break;
					case "reload":
						$this->banned = new Config(FILE_PATH."banned.txt", CONFIG_LIST);
						break;
					case "list":
						console("[INFO] Ban list: ".implode(", ", $this->banned->getAll(true)));
						break;
					default:
						console("[INFO] Usage: /ban <add | remove | list | reload> [player]");
						break;
				}
				break;
		}
	}
	
	public function ban($username){
		$this->commandHandler("ban", array("add", $username));
	}
	
	public function pardon($username){
		$this->commandHandler("ban", array("pardon", $username));
	}
	
	public function banIP($ip){
		$this->commandHandler("banip", array("add", $ip));
	}
	
	public function pardonIP($ip){
		$this->commandHandler("banip", array("pardon", $ip));
	}
	
	public function kick($username, $reason){
		$this->commandHandler("kick", array($username, $reason));
	}
	
	public function reload(){
		$this->commandHandler("ban", array("reload"));
		$this->commandHandler("banip", array("reload"));
		$this->commandHandler("whitelist", array("reload"));
	}
	
	public function isIPBanned($ip){
		if($this->server->api->dhandle("api.ban.ip.check", $ip) === false){
			return true;
		}elseif($this->bannedIPs->exists($ip)){
			return true;
		}
		return false;
	}
	
	public function isBanned($username){
		if($this->server->api->dhandle("api.ban.check", $username) === false){
			return true;
		}elseif($this->banned->exists($username)){
			return true;
		}
		return false;	
	}
	
	public function inWhitelist($username){
		if($this->server->api->dhandle("api.ban.whitelist.check", $ip) === false){
			return true;
		}elseif($this->whitelist->exists($username)){
			return true;
		}
		return false;	
	}
}