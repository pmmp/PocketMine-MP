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
	protected $interface, $protocol, $entities, $player, $cnt, $events, $username, $version, $clients;
	function __construct($username, $protocol = CURRENT_PROTOCOL, $version = CURRENT_VERSION){
		//$this->player = new Player($username);
		$this->version = (int) $version;
		$this->username = $username;
		$this->cnt = 1;
		$this->serverID = substr(Utils::generateKey(), 0, 8);
		$this->events = array("disabled" => array());
		$this->actions = array();
		$this->clients = array();
		$this->protocol = (int) $protocol;
		$this->interface = new MinecraftInterface("255.255.255.255", $this->protocol, 19132, true, false);		
		console("[INFO] Creating Minecraft Server");
		console("[INFO] Username: ".$this->username);
		console("[INFO] Protocol: ".$this->protocol);
		$this->stop = false;
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
		}
		switch($packet["pid"]){
			case 0x02:
				$this->send(0x1c, array(
					$data[0],
					$this->serverID,
					MAGIC,
					"MCCPP;Demo;". $this->username,
				), false, $packet["ip"], $packet["port"]);
				break;
			case 0x05:
				$version = $data[1];
				$size = strlen($data[2]);
				if($version != 5){
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
				$port = $data[2];
				$MTU = $data[3];
				$clientID = $data[4];
				$this->clients[$CID] = new Session($this, $clientID, $packet["ip"], $packet["port"]);
				$this->clients[$CID]->handle(0x07, $data);
				break;
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
			foreach($this->events[$event] as $eid => $ev){
				if(isset($ev[1]) and ($ev[1] === true or is_object($ev[1]))){
					$this->responses[$eid] = call_user_func(array(($ev[1] === true ? $this:$ev[1]), $ev[0]), $data, $event, $this);
				}else{
					$this->responses[$eid] = call_user_func($ev[0], $data, $event, $this);
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