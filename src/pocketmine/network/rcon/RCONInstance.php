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

use pocketmine\Thread;
use pocketmine\utils\Binary;

class RCONInstance extends Thread{
	public $stop;
	public $cmd;
	public $response;
	/** @var resource */
	private $socket;
	private $password;
	private $maxClients;
	private $waiting;

	public function isWaiting(){
		return $this->waiting === true;
	}

	/**
	 * @param resource $socket
	 * @param string   $password
	 * @param int      $maxClients
	 */
	public function __construct($socket, string $password, int $maxClients = 50){
		$this->stop = false;
		$this->cmd = "";
		$this->response = "";
		$this->socket = $socket;
		$this->password = $password;
		$this->maxClients = $maxClients;
		for($n = 0; $n < $this->maxClients; ++$n){
			$this->{"client" . $n} = null;
			$this->{"status" . $n} = 0;
			$this->{"timeout" . $n} = 0;
		}

		$this->start();
	}

	private function writePacket($client, $requestID, $packetType, $payload){
		$pk = Binary::writeLInt((int) $requestID)
			. Binary::writeLInt((int) $packetType)
			. $payload
			. "\x00\x00"; //Terminate payload and packet
		return socket_write($client, Binary::writeLInt(strlen($pk)) . $pk);
	}

	private function readPacket($client, &$size, &$requestID, &$packetType, &$payload){
		socket_set_nonblock($client);
		$d = socket_read($client, 4);
		if($this->stop === true){
			return false;
		}elseif($d === false){
			return null;
		}elseif($d === "" or strlen($d) < 4){
			return false;
		}
		socket_set_block($client);
		$size = Binary::readLInt($d);
		if($size < 0 or $size > 65535){
			return false;
		}
		$requestID = Binary::readLInt(socket_read($client, 4));
		$packetType = Binary::readLInt(socket_read($client, 4));
		$payload = rtrim(socket_read($client, $size + 2)); //Strip two null bytes
		return true;
	}

	public function close(){
		$this->stop = true;
	}

	public function run(){

		while($this->stop !== true){
			$this->synchronized(function(){
				$this->wait(2000);
			});
			$r = [$socket = $this->socket];
			$w = null;
			$e = null;
			if(socket_select($r, $w, $e, 0) === 1){
				if(($client = socket_accept($this->socket)) !== false){
					socket_set_block($client);
					socket_set_option($client, SOL_SOCKET, SO_KEEPALIVE, 1);
					$done = false;
					for($n = 0; $n < $this->maxClients; ++$n){
						if($this->{"client" . $n} === null){
							$this->{"client" . $n} = $client;
							$this->{"status" . $n} = 0;
							$this->{"timeout" . $n} = microtime(true) + 5;
							$done = true;
							break;
						}
					}
					if($done === false){
						@socket_close($client);
					}
				}
			}

			for($n = 0; $n < $this->maxClients; ++$n){
				$client = &$this->{"client" . $n};
				if($client !== null){
					if($this->{"status" . $n} !== -1 and $this->stop !== true){
						if($this->{"status" . $n} === 0 and $this->{"timeout" . $n} < microtime(true)){ //Timeout
							$this->{"status" . $n} = -1;
							continue;
						}
						$p = $this->readPacket($client, $size, $requestID, $packetType, $payload);
						if($p === false){
							$this->{"status" . $n} = -1;
							continue;
						}elseif($p === null){
							continue;
						}

						switch($packetType){
							case 3: //Login
								if($this->{"status" . $n} !== 0){
									$this->{"status" . $n} = -1;
									continue;
								}
								if($payload === $this->password){
									socket_getpeername($client, $addr, $port);
									$this->response = "[INFO] Successful Rcon connection from: /$addr:$port";
									$this->synchronized(function(){
										$this->waiting = true;
										$this->wait();
									});
									$this->waiting = false;
									$this->response = "";
									$this->writePacket($client, $requestID, 2, "");
									$this->{"status" . $n} = 1;
								}else{
									$this->{"status" . $n} = -1;
									$this->writePacket($client, -1, 2, "");
									continue;
								}
								break;
							case 2: //Command
								if($this->{"status" . $n} !== 1){
									$this->{"status" . $n} = -1;
									continue;
								}
								if(strlen($payload) > 0){
									$this->cmd = ltrim($payload);
									$this->synchronized(function(){
										$this->waiting = true;
										$this->wait();
									});
									$this->waiting = false;
									$this->writePacket($client, $requestID, 0, str_replace("\n", "\r\n", trim($this->response)));
									$this->response = "";
									$this->cmd = "";
								}
								break;
						}

					}else{
						@socket_set_option($client, SOL_SOCKET, SO_LINGER, ["l_onoff" => 1, "l_linger" => 1]);
						@socket_shutdown($client, 2);
						@socket_set_block($client);
						@socket_read($client, 1);
						@socket_close($client);
						$this->{"status" . $n} = 0;
						$this->{"client" . $n} = null;
					}
				}
			}
		}
		unset($this->socket, $this->cmd, $this->response, $this->stop);
		exit(0);
	}

	public function getThreadName() : string{
		return "RCON";
	}
}
