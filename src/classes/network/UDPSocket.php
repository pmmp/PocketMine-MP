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



class UDPSocket{
	private $encrypt;
	var $buffer, $connected, $errors, $sock, $server;
	function __construct($server, $port, $listen = false){
		$this->errors = array_fill(88,(125 - 88) + 1, true);
		$this->server = $server;
		$this->port = $port;
		$this->sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
		socket_set_option($this->sock, SOL_SOCKET, SO_BROADCAST, 1); //Allow sending broadcast messages
		if($listen !== true){
			$this->connected = true;
			$this->buffer = array();
			$this->unblock();
		}else{
			if(socket_bind($this->sock, "0.0.0.0", $port) === true){
				$this->unblock();
			}else{
				console("[ERROR] Couldn't bind to 0.0.0.0:".$port, true, true, 0);
				die();
			}
		}
	}

	public function listenSocket(){
		$sock = @socket_accept($this->sock);
		if($sock !== false){
			$sock = new Socket(false, false, false, $sock);
			$sock->unblock();
			return $sock;
		}
		return false;
	}

	public function close($error = 125){
		$this->connected = false;
		if($error !== false){
			console("[ERROR] [Socket] Socket closed, Error $error: ".socket_strerror($error));
		}
		return @socket_close($this->sock);
	}

	public function block(){
		socket_set_block($this->sock);
	}

	public function unblock(){
		socket_set_nonblock($this->sock);
	}

	public function read(){
		$source = false;
		$port = 1;
		$len = @socket_recvfrom($this->sock, $buf, 65536, 0, $source, $port);
		return array($buf, $source, $port, $len);
	}

	public function write($data, $dest = false, $port = false){
		return @socket_sendto($this->sock, $data, strlen($data), 0, ($dest === false ? $this->server:$dest), ($port === false ? $this->port:$port));
	}

}

?>