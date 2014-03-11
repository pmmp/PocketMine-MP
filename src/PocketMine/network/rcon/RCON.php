<?php

/*
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

/**
 * Implementation of the Source RCON Protocol to allow remote console commands
 * Source: https://developer.valvesoftware.com/wiki/Source_RCON_Protocol
 */
namespace PocketMine\Network\RCON;

use PocketMine;
use PocketMine\ServerAPI;


class RCON{
	private $socket, $password, $workers, $threads, $clientsPerThread;

	public function __construct($password, $port = 19132, $interface = "0.0.0.0", $threads = 1, $clientsPerThread = 50){
		$this->workers = array();
		$this->password = (string) $password;
		console("[INFO] Starting remote control listener");
		if($this->password === ""){
			console("[ERROR] RCON can't be started: Empty password");

			return;
		}
		$this->threads = (int) max(1, $threads);
		$this->clientsPerThread = (int) max(1, $clientsPerThread);
		$this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if($this->socket === false or !socket_bind($this->socket, $interface, (int) $port) or !socket_listen($this->socket)){
			console("[ERROR] RCON can't be started: " . socket_strerror(socket_last_error()));

			return;
		}
		@socket_set_block($this->socket);
		for($n = 0; $n < $this->threads; ++$n){
			$this->workers[$n] = new RCONInstance($this->socket, $this->password, $this->clientsPerThread);
		}
		@socket_getsockname($this->socket, $addr, $port);
		console("[INFO] RCON running on $addr:$port");
		ServerAPI::request()->schedule(2, array($this, "check"), array(), true);
	}

	public function stop(){
		for($n = 0; $n < $this->threads; ++$n){
			$this->workers[$n]->close();
			$this->workers[$n]->join();
			usleep(50000);
			$this->workers[$n]->kill();
		}
		@socket_close($this->socket);
		$this->threads = 0;
	}

	public function check(){
		for($n = 0; $n < $this->threads; ++$n){
			if($this->workers[$n]->isTerminated() === true){
				$this->workers[$n] = new RCONInstance($this->socket, $this->password, $this->clientsPerThread);
			} elseif($this->workers[$n]->isWaiting()){
				if($this->workers[$n]->response !== ""){
					console($this->workers[$n]->response);
					$this->workers[$n]->notify();
				} else{
					$this->workers[$n]->response = ServerAPI::request()->api->console->run($this->workers[$n]->cmd, "rcon");
					$this->workers[$n]->notify();
				}
			}
		}
	}

}
