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

declare(strict_types=1);

namespace pocketmine\network\rcon;

use pocketmine\snooze\SleeperNotifier;
use pocketmine\Thread;
use pocketmine\utils\Binary;

class RCONInstance extends Thread{

	/** @var string */
	public $cmd;
	/** @var string */
	public $response;

	/** @var bool */
	private $stop;
	/** @var resource */
	private $socket;
	/** @var string */
	private $password;
	/** @var int */
	private $maxClients;
	/** @var \ThreadedLogger */
	private $logger;
	/** @var resource */
	private $ipcSocket;
	/** @var SleeperNotifier|null */
	private $notifier;

	/**
	 * @param resource             $socket
	 * @param string               $password
	 * @param int                  $maxClients
	 * @param \ThreadedLogger      $logger
	 * @param resource             $ipcSocket
	 * @param null|SleeperNotifier $notifier
	 */
	public function __construct($socket, string $password, int $maxClients = 50, \ThreadedLogger $logger, $ipcSocket, ?SleeperNotifier $notifier){
		$this->stop = false;
		$this->cmd = "";
		$this->response = "";
		$this->socket = $socket;
		$this->password = $password;
		$this->maxClients = $maxClients;
		$this->logger = $logger;
		$this->ipcSocket = $ipcSocket;
		$this->notifier = $notifier;

		$this->start(PTHREADS_INHERIT_NONE);
	}

	private function writePacket($client, int $requestID, int $packetType, string $payload){
		$pk = Binary::writeLInt($requestID)
			. Binary::writeLInt($packetType)
			. $payload
			. "\x00\x00"; //Terminate payload and packet
		return socket_write($client, Binary::writeLInt(strlen($pk)) . $pk);
	}

	private function readPacket($client, ?int &$requestID, ?int &$packetType, ?string &$payload){
		$d = @socket_read($client, 4);
		if($this->stop){
			return false;
		}elseif($d === false){
			if(socket_last_error($client) === SOCKET_ECONNRESET){ //client crashed, terminate connection
				return false;
			}
			return null;
		}elseif($d === "" or strlen($d) < 4){
			return false;
		}

		$size = Binary::readLInt($d);
		if($size < 0 or $size > 65535){
			return false;
		}
		$requestID = Binary::readLInt(socket_read($client, 4));
		$packetType = Binary::readLInt(socket_read($client, 4));
		$payload = rtrim(socket_read($client, $size + 2)); //Strip two null bytes
		return true;
	}

	public function close() : void{
		$this->stop = true;
	}

	public function run() : void{
		$this->registerClassLoader();

		/** @var resource[] $clients */
		$clients = [];
		/** @var int[] $authenticated */
		$authenticated = [];
		/** @var float[] $timeouts */
		$timeouts = [];

		/** @var int $nextClientId */
		$nextClientId = 0;

		while(!$this->stop){
			$r = $clients;
			$r["main"] = $this->socket; //this is ugly, but we need to be able to mass-select()
			$r["ipc"] = $this->ipcSocket;
			$w = null;
			$e = null;

			$disconnect = [];

			if(socket_select($r, $w, $e, 5, 0) > 0){
				foreach($r as $id => $sock){
					if($sock === $this->socket){
						if(($client = socket_accept($this->socket)) !== false){
							if(count($clients) >= $this->maxClients){
								@socket_close($client);
							}else{
								socket_set_block($client);
								socket_set_option($client, SOL_SOCKET, SO_KEEPALIVE, 1);

								$id = $nextClientId++;
								$clients[$id] = $client;
								$authenticated[$id] = false;
								$timeouts[$id] = microtime(true) + 5;
							}
						}
					}elseif($sock === $this->ipcSocket){
						//read dummy data
						socket_read($sock, 65535);
					}else{
						$p = $this->readPacket($sock, $requestID, $packetType, $payload);
						if($p === false){
							$disconnect[$id] = $sock;
							continue;
						}elseif($p === null){
							continue;
						}

						switch($packetType){
							case 3: //Login
								if($authenticated[$id]){
									$disconnect[$id] = $sock;
									break;
								}
								if($payload === $this->password){
									socket_getpeername($sock, $addr, $port);
									$this->logger->info("Successful Rcon connection from: /$addr:$port");
									$this->writePacket($sock, $requestID, 2, "");
									$authenticated[$id] = true;
								}else{
									$disconnect[$id] = $sock;
									$this->writePacket($sock, -1, 2, "");
								}
								break;
							case 2: //Command
								if(!$authenticated[$id]){
									$disconnect[$id] = $sock;
									break;
								}
								if($payload !== ""){
									$this->cmd = ltrim($payload);
									$this->synchronized(function(){
										$this->notifier->wakeupSleeper();
										$this->wait();
									});
									$this->writePacket($sock, $requestID, 0, str_replace("\n", "\r\n", trim($this->response)));
									$this->response = "";
									$this->cmd = "";
								}
								break;
						}
					}
				}
			}

			foreach($authenticated as $id => $status){
				if(!isset($disconnect[$id]) and !$authenticated[$id] and $timeouts[$id] < microtime(true)){ //Timeout
					$disconnect[$id] = $clients[$id];
				}
			}

			foreach($disconnect as $id => $client){
				$this->disconnectClient($client);
				unset($clients[$id], $authenticated[$id], $timeouts[$id]);
			}
		}

		foreach($clients as $client){
			$this->disconnectClient($client);
		}
	}

	private function disconnectClient($client) : void{
		@socket_set_option($client, SOL_SOCKET, SO_LINGER, ["l_onoff" => 1, "l_linger" => 1]);
		@socket_shutdown($client, 2);
		@socket_set_block($client);
		@socket_read($client, 1);
		@socket_close($client);
	}

	public function getThreadName() : string{
		return "RCON";
	}
}
