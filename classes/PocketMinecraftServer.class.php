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

require_once("classes/Session.class.php");

class PocketMinecraftServer{
	var $seed, $protocol, $gamemode, $name, $maxClients, $clients, $eidCnt, $custom, $description, $motd;
	protected $interface, $entities, $player, $cnt, $events, $version, $serverType;
	function __construct($name, $gamemode = 1, $seed = false, $protocol = CURRENT_PROTOCOL, $port = 19132, $serverID = false, $version = CURRENT_VERSION){
		$this->gamemode = (int) $gamemode;
		$this->port = (int) $port;
		$this->version = (int) $version;
		$this->name = $name;
		$this->custom = array();
		$this->cnt = 1;
		$this->eidCnt = 1;
		$this->maxClients = 20;
		$this->description = "";
		$this->motd = "Welcome to ".$name;
		$this->serverID = $serverID === false ? Utils::readLong(Utils::getRandomBytes(8)):$serverID;
		$this->seed = $seed === false ? Utils::readInt(Utils::getRandomBytes(4)):((int) $seed);
		$this->events = array("disabled" => array());
		$this->actions = array();
		$this->clients = array();
		$this->protocol = (int) $protocol;
		$this->time = 0;
		//$this->event("onTick", "onTick", true);
		$this->event("onChat", "eventHandler", true);
		$this->action(1000000, '$this->time += 10;$this->trigger("onTimeChange", $this->time);');
		$this->action(1000000 * 60 * 10, '$this->custom = array();');
		$this->setType("normal");
		$this->interface = new MinecraftInterface("255.255.255.255", $this->protocol, $this->port, true, false);		
		console("[INFO] Starting Minecraft PE Server at *:".$this->port);
		$this->action(1000000 * 3 * 60, '$this->chat(false, "This server uses Pocket-Minecraft-PHP");');
		sleep(2);
		$this->action(1000000 * 3 * 60, '$this->chat(false, "Check it at http://bit.ly/RE7uaW");');
		console("[INFO] Server Name: ".$this->name);
		console("[INFO] Server GUID: ".$this->serverID);
		console("[INFO] Protocol Version: ".$this->protocol);
		console("[INFO] Seed: ".$this->seed);
		console("[INFO] Gamemode: ".($this->gamemode === 0 ? "survival":"creative"));
		console("[INFO] Max Clients: ".$this->maxClients);
		$this->stop = false;
	}
	
	public function close($reason = "stop"){	
		$this->chat(false, "Stopping server...");
		$this->stop = true;
		$this->trigger("onClose");
	}
	
	public function chat($owner, $text, $target = true){
		$message = "";
		if($owner !== false){
			$message = "<".$owner."> ";
		}
		$message .= $text;
		$this->trigger("onChat", $text);
	}
	
	public function setType($type = "demo"){
		switch($type){
			case "normal":
				$this->serverType = "MCCPP;Demo;";
				break;
			case "minecon":
				$this->serverType = "MCCPP;MINECON;";
				break;
		}
		
	}
	
	public function eventHandler($data, $event){
		switch($event){
			case "onChat":
				console("[CHAT] $data");
				break;
		}
	}
	
	public function action($microseconds, $code){
		$this->actions[] = array($microseconds / 1000000, microtime(true), $code);
		console("[INTERNAL] Attached to action ".$microseconds, true, true, 3);
	}
	
	public function start(){
		declare(ticks=15);
		register_tick_function(array($this, "tickerFunction"));
		$this->action(50000, '$this->trigger("onTick", $time);');
		$this->event("onReceivedPacket", "packetHandler", true);
		register_shutdown_function(array($this, "close"));
		$this->process();
	}
	
	public function tickerFunction(){
		//actions that repeat every x time will go here
		$time = microtime(true);
		foreach($this->actions as $id => $action){
			if($action[1] <= ($time - $action[0])){
				$this->actions[$id][1] = $time;
				eval($action[2]);
			}
		}
	}
	
	public function clientID($ip, $port){
		return md5($pi . $port, true);
	}
	
	public function packetHandler($packet, $event){
		$data =& $packet["data"];
		$CID = $this->clientID($packet["ip"], $packet["port"]);
		if(isset($this->clients[$CID])){
			$this->clients[$CID]->handle($packet["pid"], $data);
		}else{
			switch($packet["pid"]){
				case 0x02:
					if(!isset($this->custom["times_".$CID])){
						$this->custom["times_".$CID] = 0;
					}
					$ln = 15;
					$txt = substr($this->description, $this->custom["times_".$CID], $ln);
					$txt .= substr($this->descriptiont, 0, $ln - strlen($txt));
					$this->send(0x1c, array(
						$data[0],
						$this->serverID,
						MAGIC,
						$this->serverType. $this->name . " [".($this->gamemode === 1 ? "C":"S")." ".count($this->clients)."/".$this->maxClients."] ".$txt,
					), false, $packet["ip"], $packet["port"]);
					$this->custom["times_".$CID] = ($this->custom["times_".$CID] + 1) % strlen($this->description);
					break;
				case 0x05:
					if(count($this->clients) >= $this->maxClients){
						break;
					}
					$version = $data[1];
					$size = strlen($data[2]);
					if($version !== $this->protocol){
						$this->send(0x1a, array(
							5,
							MAGIC,
							$this->serverID,
						), false, $packet["ip"], $packet["port"]);
					}else{
						$this->send(0x06, array(
							MAGIC,
							$this->serverID,
							0,
							strlen($packet["raw"]),
						), false, $packet["ip"], $packet["port"]);
					}
					break;
				case 0x07:
					if(count($this->clients) >= $this->maxClients){
						break;
					}
					$port = $data[2];
					$MTU = $data[3];
					$clientID = $data[4];
					$EID = $this->eidCnt++;
					$this->clients[$CID] = new Session($this, $clientID, $EID, $packet["ip"], $packet["port"]);
					$entities[$EID] = &$this->clients[$CID];
					$this->clients[$CID]->handle(0x07, $data);
					break;
			}
		}
	}
	
	public function send($pid, $data = array(), $raw = false, $dest = false, $port = false){
		$this->trigger($pid, $data);
		$this->trigger("onSentPacket", $data);
		$this->interface->writePacket($pid, $data, $raw, $dest, $port);
	}
	
	public function process(){
		while($this->stop === false){
			$packet = $this->interface->readPacket();
			if($packet !== false){
				$this->trigger("onReceivedPacket", $packet);
				$this->trigger($packet["pid"], $packet);
			}else{
				usleep(10000);
			}			
		}
	}
	
	public function trigger($event, $data = ""){
		console("[INTERNAL] Event ". $event, true, true, 3);
		if(isset($this->events[$event]) and !isset($this->events["disabled"][$event])){
			foreach($this->events[$event] as $evid => $ev){
				if(isset($ev[1]) and ($ev[1] === true or is_object($ev[1]))){
					$this->responses[$evid] = call_user_func(array(($ev[1] === true ? $this:$ev[1]), $ev[0]), $data, $event, $this);
				}else{
					$this->responses[$evid] = call_user_func($ev[0], $data, $event, $this);
				}
			}
		}	
	}
	public function toggleEvent($event){
		if(isset($this->events["disabled"][$event])){
			unset($this->events["disabled"][$event]);
			console("[INTERNAL] Enabled event ".$event, true, true, 3);
		}else{
			$this->events["disabled"][$event] = false;
			console("[INTERNAL] Disabled event ".$event, true, true, 3);
		}	
	}
	
	public function event($event, $func, $in = false){
		++$this->cnt;
		if(!isset($this->events[$event])){
			$this->events[$event] = array();
		}
		$this->events[$event][$this->cnt] = array($func, $in);
		console("[INTERNAL] Attached to event ".$event, true, true, 3);
		return $this->cnt;
	}
	
	public function deleteEvent($event, $id = -1){
		if($id === -1){
			unset($this->events[$event]);
		}else{
			unset($this->events[$event][$id]);
			if(isset($this->events[$event]) and count($this->events[$event]) === 0){
				unset($this->events[$event]);
			}
		}
	}
	
}