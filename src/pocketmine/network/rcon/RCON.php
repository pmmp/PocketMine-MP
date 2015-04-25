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
use pocketmine\event\server\RemoteServerCommandEvent;
use pocketmine\Server;
use pocketmine\utils\TextFormat;


class RCON{
	/** @var Server */
	private $server;
	private $socket;
	private $password;
	/** @var RCONInstance[] */
	private $workers = [];
	private $clientsPerThread;

	public function __construct(Server $server, $password, $port = 19132, $interface = "0.0.0.0", $threads = 1, $clientsPerThread = 50){
		$this->server = $server;
		$this->workers = [];
		$this->password = (string) $password;
		$this->server->getLogger()->info("Starting remote control listener");
		if($this->password === ""){
			$this->server->getLogger()->critical("RCON can't be started: Empty password");

			return;
		}
		$this->threads = (int) max(1, $threads);
		$this->clientsPerThread = (int) max(1, $clientsPerThread);
		$this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if($this->socket === false or !socket_bind($this->socket, $interface, (int) $port) or !socket_listen($this->socket)){
			$this->server->getLogger()->critical("RCON can't be started: " . socket_strerror(socket_last_error()));
			$this->threads = 0;
			return;
		}
		socket_set_block($this->socket);

		for($n = 0; $n < $this->threads; ++$n){
			$this->workers[$n] = new RCONInstance($this->socket, $this->password, $this->clientsPerThread);
		}
		socket_getsockname($this->socket, $addr, $port);
		$this->server->getLogger()->info("RCON running on $addr:$port");
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
					$this->server->getLogger()->info($this->workers[$n]->response);
					$this->workers[$n]->synchronized(function (RCONInstance $thread){
						$thread->notify();
					}, $this->workers[$n]);
				}else{

					$response = new RemoteConsoleCommandSender();
					$command = $this->workers[$n]->cmd;

					$this->server->getPluginManager()->callEvent($ev = new RemoteServerCommandEvent($response, $command));

					if(!$ev->isCancelled()){
						$this->server->dispatchCommand($ev->getSender(), $ev->getCommand());
					}

					$this->workers[$n]->response = TextFormat::clean($response->getMessage());
					$this->workers[$n]->synchronized(function (RCONInstance $thread){
						$thread->notify();
					}, $this->workers[$n]);
				}
			}
		}
	}

}
