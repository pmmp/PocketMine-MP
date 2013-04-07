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

class ThreadedUDPSocket extends Thread{
	private $socket;
	private $sendQueue;
	private $receiveQueue;
	public $connected;
	public $d;
	public $port;
	public $server;

	public function __construct($server, $port, $listen = false, $serverip = "0.0.0.0"){
		$this->server = $server;
		$this->port = (int) $port;
		$this->d = array($listen, $serverip);
	}
	
	public function isConnected(){
		return (bool) $this->connected;
	}
	
	public function run(){
		$this->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
		socket_set_option($this->socket, SOL_SOCKET, SO_BROADCAST, 1); //Allow sending broadcast messages
		if($this->d[0] !== true){
			$this->connected = true;
			$this->unblock();
		}else{
			if(socket_bind($this->socket, $this->d[1], $this->port) === true){
				$this->unblock();
				$this->connected = true;
			}else{
				$this->connected = false;
			}
		}
		$this->sendQueue = array();
		$this->receiveQueue = array();
		$this->wait();
		while($this->connected === true){
			if(count($this->receiveQueue) < 1024){
				$buf = "";
				$source = false;
				$port = 1;
				$len = @socket_recvfrom($this->socket, $buf, 65535, 0, $source, $port);
				if($len !== false){
					$this->receiveQueue[] = array($buf, $source, $port, $len);
				}
			}
			if(count($this->sendQueue) > 0){
				$item = array_shift($this->sendQueue);
				@socket_sendto($this->socket, $item[0], strlen($item[0]), 0, ($item[1] === false ? $this->server:$item[1]), ($item[2] === false ? $this->port:$item[2]));
			}
			usleep(1);
		}
		exit(0);
	}

	public function close($error = 125){
		$this->connected = false;
		return @socket_close($this->socket);
	}

	public function block(){
		socket_set_block($this->socket);
	}

	public function unblock(){
		socket_set_nonblock($this->socket);
	}

	public function read(){
		if($this->connected === false or count($this->receiveQueue) <= 0){
			return false;
		}
		return array_shift($this->receiveQueue);
	}

	public function write($data, $dest = false, $port = false){
		if($this->connected === false){
			return false;
		}
		$this->sendQueue[] = array($data, $dest, $port);
		return true;
	}

}