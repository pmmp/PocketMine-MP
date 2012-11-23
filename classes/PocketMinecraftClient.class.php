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

class PocketMinecraftClient{
	protected $interface, $protocol, $entities, $player, $cnt, $events, $username, $version, $clientID, $connected, $serverID, $start;
	var $serverList = array(), $counter;
	function __construct($username, $protocol = CURRENT_PROTOCOL, $version = CURRENT_VERSION){
		//$this->player = new Player($username);
		$this->start = microtime(true);
		$this->version = (int) $version;
		$this->username = $username;
		$this->connected = false;
		$this->cnt = 1;
		$this->clientID = substr(Utils::generateKey(), 0, 8);
		$this->events = array("disabled" => array());
		$this->actions = array();
		$this->interface = new MinecraftInterface("255.255.255.255", $protocol, 19132);		
		console("[INFO] Creating Minecraft Client");
		console("[INFO] Username: ".$this->username);
		console("[INFO] Version: ".$this->version);
		$this->event("onReceivedPacket", "packetHandler", true);
		$this->stop = false;
		$this->counter = array(0,0);
		declare(ticks=15);
		register_tick_function(array($this, "tickerFunction"));
	}

	public function action($microseconds, $code){
		$this->actions[] = array($microseconds / 1000000, microtime(true), $code);
		console("[INTERNAL] Attached to action ".$microseconds, true, true, 3);
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
	
	public function start($ip){
		$this->stop = false;
		$this->action(50000, '$this->trigger("onTick", $time);');
		$this->interface = new MinecraftInterface($ip, $this->protocol, 19132);	
		console("[INFO] Connecting to Server ".$ip);
		$this->send(0x05, array(
			MAGIC,
			$this->version,
			str_repeat("\x00", 1447),
		));
		$this->process();
	}
	
	public function getServerList(){
		$this->action(1000000, '$this->send(0x02, array(((microtime(true) - $this->start) * 1000)));');
		$this->action(5000000, '$this->actions = array();$this->stop = true;');
		$this->process();
		$list = array();
		foreach($this->serverList as $ip => $info){
			$info["ip"] = $ip;
			$list[] = $info;
		}
		return $list;
	}
	
	public function packetHandler($packet, $event){
		$data =& $packet["data"];
		switch($packet["pid"]){
			case 0x1c:
				$pingID = $data[0];
				$this->serverID = $data[1];
				$info = explode(";", $data[3]);
				$this->serverList[$packet["ip"]] = array("serverID" => $serverID, "username" => array_pop($info));
				break;
			case 0x06:
				$serverID = $data[1];
				$length = $data[3];
				$this->send(0x07, array(
					MAGIC,
					"\x04\x3f\x57\xfe\xfd",
					19132,
					$length,
					$this->clientID,
				));
				break;
			case 0x08:
				$serverID = $data[1];
				$this->send(0x84, array(
					$this->counter[0],
					0x40,
					array(
						"id" => 0x09,
						"clientID" => $this->clientID,
						"session" => "\x00\x0c\x98\x00",
					),
				));
				++$this->counter[0];
				break;
			case 0x84:
				if(isset($data[0])){
					$this->counter[1] = $data[0];
					$this->send(0xc0, array(1, true, $data[0]));
				}
				switch($data["id"]){
					case 0x10:
						$this->send(0x84, array(
							$this->counter[0],
							0x40,
							array(
								"id" => 0x13,
								"port" => 19132,
								"dataArray" => $data["dataArray"],
								"session" => $data["session"],
							),
						));
						++$this->counter[0];
						$this->send(0x84, array(
							$this->counter[0],
							0x40,
							array(
								"id" => 0x82,
								"username" => $this->username,
							),
						));
						++$this->counter[0];
						break;
					case 0x86:
						console("[DEBUG] Time: ".$data["time"], true, true, 2);
						break;
					}
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