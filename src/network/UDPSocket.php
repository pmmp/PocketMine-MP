<?php

/**
 *
 *  ____            _        _   __  __ _                  __  __ ____  
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \ 
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/ 
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_| 
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 * 
 *
*/



class UDPSocket{
	public $connected, $sock, $server;
	function __construct($server, $port, $listen = false, $serverip = "0.0.0.0"){
		$this->server = $server;
		$this->port = $port;
		$this->sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
		socket_set_option($this->sock, SOL_SOCKET, SO_BROADCAST, 1); //Allow sending broadcast messages
		if($listen !== true){
			$this->connected = true;
			$this->unblock();
		}else{
			if(socket_bind($this->sock, $serverip, $port) === true){
				socket_set_option($this->sock, SOL_SOCKET, SO_REUSEADDR, 0);
				socket_set_option($this->sock, SOL_SOCKET, SO_SNDBUF, 1024 * 1024 * 2); //2MB
				socket_set_option($this->sock, SOL_SOCKET, SO_RCVBUF, 1024 * 1024); //1MB
				$this->unblock();
				$this->connected = true;
			}else{
				$this->connected = false;
			}
		}
	}

	public function close($error = 125){
		$this->connected = false;
		return @socket_close($this->sock);
	}

	public function block(){
		socket_set_block($this->sock);
	}

	public function unblock(){
		socket_set_nonblock($this->sock);
	}

	public function read(&$buf, &$source, &$port){
		if($this->connected === false){
			return false;
		}
		return @socket_recvfrom($this->sock, $buf, 65535, 0, $source, $port);
	}

	public function write($data, $dest, $port){
		if($this->connected === false){
			return false;
		}
		return @socket_sendto($this->sock, $data, strlen($data), 0, $dest, $port);
	}

}

?>