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
namespace pocketmine\network\rcon;

use pocketmine\command\RemoteConsoleCommandSender;
use pocketmine\scheduler\CallbackTask;
use pocketmine\Server;
use pocketmine\utils\MainLogger;
use pocketmine\utils\TextFormat;


class RCON{
	private $socket;
	private $password;
	/** @var RCONInstance[] */
	private $workers;
	private $threads;
	private $clientsPerThread;
	private $rconSender;

	public function __construct($password, $port = 19132, $interface = "0.0.0.0", $threads = 1, $clientsPerThread = 50){
		$this->workers = [];
		$this->password = (string) $password;
		MainLogger::getLogger()->info("Starting remote control listener");
		if($this->password === ""){
			MainLogger::getLogger()->critical("RCON can't be started: Empty password");

			return;
		}
		$this->threads = (int) max(1, $threads);
		$this->clientsPerThread = (int) max(1, $clientsPerThread);
		$this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if($this->socket === false or !socket_bind($this->socket, $interface, (int) $port) or !socket_listen($this->socket)){
			MainLogger::getLogger()->critical("RCON can't be started: " . socket_strerror(socket_last_error()));

			return;
		}
		@socket_set_block($this->socket);

		for($n = 0; $n < $this->threads; ++$n){
			$this->workers[$n] = new RCONInstance($this->socket, $this->password, $this->clientsPerThread);
		}
		@socket_getsockname($this->socket, $addr, $port);
		MainLogger::getLogger()->info("RCON running on $addr:$port");
		Server::getInstance()->getScheduler()->scheduleRepeatingTask(new CallbackTask(array($this, "check")), 3);
	}

	public function stop(){
		for($n = 0; $n < $this->threads; ++$n){
			$this->workers[$n]->close();
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
			}elseif($this->workers[$n]->isWaiting()){
				if($this->workers[$n]->response !== ""){
					MainLogger::getLogger()->info($this->workers[$n]->response);
					$this->workers[$n]->synchronized(function($thread){
						$thread->notify();
					}, $this->workers[$n]);
				}else{
					Server::getInstance()->dispatchCommand($response = new RemoteConsoleCommandSender(), $this->workers[$n]->cmd);
					$this->workers[$n]->response = TextFormat::clean($response->getMessage());
					$this->workers[$n]->synchronized(function($thread){
						$thread->notify();
					}, $this->workers[$n]);
				}
			}
		}
	}

}
