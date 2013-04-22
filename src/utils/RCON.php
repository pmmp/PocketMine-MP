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



class RCON{
	private $socket, $password, $workers, $threads;
	
	public function __construct($password, $port = 19132, $threads = 4){
		$this->workers = array();
		$this->password = (string) $password;
		console("[INFO] Starting remote control listener");
		if($this->password === ""){
			console("[ERROR] RCON can't be started: Empty password");
			return;
		}
		$this->threads = (int) max(1, $threads);
		$this->socket = socket_create_listen((int) $port);
		if($this->socket === false){
			console("[ERROR] RCON can't be started: ".socket_strerror(socket_last_error()));
			return;
		}		
		@socket_set_nonblock($this->socket);
		for($n = 0; $n < $this->threads; ++$n){
			$this->workers[$n] = new RCONInstance($this->socket, $this->password);
		}
		@socket_getsockname($this->socket, $addr, $port);
		console("[INFO] RCON running on $addr:$port");
		ServerAPI::request()->schedule(2, array($this, "check"), array(), true);
	}
	
	public function stop(){
		for($n = 0; $n < $this->threads; ++$n){
			$this->workers[$n]->close();
			$this->workers[$n]->join();
		}
		@socket_close($this->socket);
		$this->threads = 0;
	}
	
	public function check(){
		for($n = 0; $n < $this->threads; ++$n){
			if($this->workers[$n]->isTerminated() === true){
				$this->workers[$n] = new RCONInstance($this->socket, $this->password);
			}elseif($this->workers[$n]->isWaiting()){
				if($this->workers[$n]->response !== ""){
					console($this->workers[$n]->response);
					$this->workers[$n]->notify();
				}else{
					$this->workers[$n]->response = ServerAPI::request()->api->console->run($this->workers[$n]->cmd, "console");
					$this->workers[$n]->notify();
				}
			}
		}
	}

}

class RCONInstance extends Thread{
	public $stop;
	public $cmd;
	public $response;
	private $socket;
	private $password;
	private $status;
	private $client;

	public function __construct($socket, $password){
		$this->stop = false;
		$this->cmd = "";
		$this->response = "";
		$this->socket = $socket;
		$this->password = $password;
		$this->status = 0;
		$this->start();
	}
	
	private function writePacket($requestID, $packetType, $payload){
		return socket_write($this->client, Utils::writeLInt(strlen($payload)).Utils::writeLInt((int) $requestID).Utils::writeLInt((int) $packetType).($payload === "" ? "\x00":$payload)."\x00");
	}
	
	private function readPacket(&$size, &$requestID, &$packetType, &$payload){
		@socket_set_nonblock($this->client);
		while(true){
			usleep(1);
			$d = socket_read($this->client, 4);
			if($this->stop === true){
				return false;
			}elseif($d === false){
				continue;
			}elseif($d === ""){
				return false;
			}
			break;
		}
		@socket_set_block($this->client);
		$size = Utils::readLInt($d);
		if($size < 0){
			return false;
		}
		$requestID = Utils::readLInt(socket_read($this->client, 4));
		$packetType = Utils::readLInt(socket_read($this->client, 4));
		$payload = rtrim(socket_read($this->client, $size + 2)); //Strip two null bytes
		return true;
	}
	
	public function close(){		
		$this->stop = true;
		$this->status = -1;
	}
	
	public function run(){
		while($this->stop !== true){
			usleep(1000);
			if(($this->client = socket_accept($this->socket)) !== false){
				socket_set_block($this->client);
				socket_set_option($this->client, SOL_SOCKET, SO_KEEPALIVE, 1);
				while($this->status !== -1 and $this->stop !== true){
					if($this->readPacket($size, $requestID, $packetType, $payload) === false){
						$this->status = -1;
						break;
					}
					switch($packetType){
						case 3: //Login
							if($this->status !== 0){
								$this->status = -1;
								continue;
							}
							if($payload === $this->password){
								@socket_getpeername($this->client, $addr, $port);
								$this->response = "[INFO] Successful Rcon connection from: /$addr:$port";
								$this->wait();
								$this->response = "";
								$this->writePacket($requestID, 2, "");
								$this->status = 1;
							}else{
								$this->status = -1;
								$this->writePacket(-1, 2, "");
								continue;
							}
							break;
						case 2: //Command
							if($this->status !== 1){
								$this->status = -1;
								continue;
							}
							if(strlen($payload) > 0){
								$this->cmd = ltrim($payload);
								$this->wait();
								$this->writePacket($requestID, 0, str_replace("\n", "\r\n", trim($this->response)));
								$this->response = "";
								$this->cmd = "";
							}
							break;
					}
					usleep(1);
				}
				@socket_set_option($this->client, SOL_SOCKET, SO_LINGER, array("l_onoff" => 1, "l_linger" => 1));
				@socket_shutdown($this->client, 2);
				@socket_set_block($this->client);
				@socket_read($this->client, 1);
				@socket_close($this->client);
				$this->status = 0;
			}
		}
		unset($this->client, $this->socket, $this->cmd, $this->response, $this->stop, $this->status);
		exit(0);
	}
}