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


class Session{
	protected $server, $serverID;
	var $clientID, $ip, $port;
	function __construct($server, $clientID, $ip, $port){
		$this->server = $server;
		$this->clientID = $clientID;
		$this->ip = $ip;
		$this->port = $port;
		$this->serverID =& $this->server->serverID;
		console("[DEBUG] New Session started with ".$ip.":".$port, true, true, 2);
	}
	
	public function handle($pid, &$data){
		switch($pid){
			case 0x07:
				$this->send(0x08, array(
					MAGIC,
					$this->serverID,
					$this->port,
					$data[3],
					0,
				));
				break;
			case 0x84:
				$counter = $data[0];
				$this->send(0xc0, array(
					"\x00\x01\x01\x00\x00\x00",				
				));
				break;				
		}
	}
	
	public function send($pid, $data = array(), $raw = false){
		$this->server->send($pid, $data, $raw, $this->ip, $this->port);
	}

}