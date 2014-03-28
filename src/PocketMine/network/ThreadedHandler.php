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
 * Network-related classes
 */
namespace PocketMine\Network;

use PocketMine\Network\Query\QueryPacket;
use PocketMine\Network\RakNet\Info;
use PocketMine\Network\RakNet\Packet as RakNetPacket;

class ThreadedHandler extends \Thread{
	protected $bandwidthUp;
	protected $bandwidthDown;
	protected $bandwidthTime;
	private $socket;
	protected $packets;
	protected $queue;
	protected $stop;
	protected $server;
	protected $port;
	protected $serverip;

	function __construct($server, $port = 19132, $serverip = "0.0.0.0"){
		$this->server = $server;
		$this->port = $port;
		$this->serverip = $serverip;
		$this->bandwidthUp = 0;
		$this->bandwidthDown = 0;
		$this->bandwidthTime = microtime(true);
		$this->packets = new \Threaded();
		$this->queue = new \Threaded();
		$this->stop = false;

		//Load the classes so the Thread gets them
		Info::isValid(0);
		new Packet(0);
		new QueryPacket();
		new RakNetPacket(0);

		$this->start(PTHREADS_INHERIT_ALL);
	}

	public function close(){
		$this->synchronized(function (){
			$this->stop = true;
			socket_close($this->socket);
		});
	}

	/**
	 * Upload speed in bytes/s
	 *
	 * @return float
	 */
	public function getUploadSpeed(){
		return $this->synchronized(function (){
			return $this->bandwidthUp / max(1, microtime(true) - $this->bandwidthTime);
		});
	}

	/**
	 * Download speed in bytes/s
	 *
	 * @return float
	 */
	public function getDownloadSpeed(){
		return $this->synchronized(function (){
			return $this->bandwidthDown / max(1, microtime(true) - $this->bandwidthTime);
		});
	}


	/**
	 * @return Packet
	 */
	public function readPacket(){
		return $this->packets->synchronized(function (){
			//$this->notify();
			return $this->packets->shift();
		});
	}

	/**
	 * @param Packet $packet
	 *
	 * @return int
	 */
	public function writePacket(Packet $packet){
		return $this->queue->synchronized(function ($packet){
			$this->queue[] = $packet;

			//$this->notify();
			return strlen($packet->buffer);
		}, $packet);
	}

	public function run(){
		$this->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
		socket_set_option($this->socket, SOL_SOCKET, SO_BROADCAST, 1); //Allow sending broadcast messages
		if(@socket_bind($this->socket, $this->serverip, $this->port) === true){
			socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 0);
			@socket_set_option($this->socket, SOL_SOCKET, SO_SNDBUF, 1024 * 1024 * 2); //2MB
			@socket_set_option($this->socket, SOL_SOCKET, SO_RCVBUF, 1024 * 1024); //1MB
		}else{
			console("[SEVERE] **** FAILED TO BIND TO " . $this->serverip . ":" . $this->port . "!", true, true, 0);
			console("[SEVERE] Perhaps a server is already running on that port?", true, true, 0);
			exit(1);
		}
		socket_set_nonblock($this->socket);

		$count = 0;
		while($this->stop === false){
			if($this->getPacket() === false and $this->putPacket() === false){
				++$count;
			}else{
				$count = 0;
			}
			if($count > 128){
				$this->wait(100000);
			}
		}
	}

	private function putPacket(){
		if(($packet = $this->queue->synchronized(function (){
				return $this->queue->shift();
			})) instanceof Packet
		){
			if($packet instanceof RakNetPacket){
				$packet->encode();
			}
			$this->bandwidthUp += @socket_sendto($this->socket, $packet->buffer, strlen($packet->buffer), 0, $packet->ip, $packet->port);

			return true;
		}

		return false;
	}

	private function getPacket(){
		$buffer = null;
		$source = null;
		$port = null;
		$len = @socket_recvfrom($this->socket, $buffer, 65535, 0, $source, $port);
		if($len === false or $len == 0){
			return false;
		}
		$this->bandwidthDown += $len;
		$pid = ord($buffer{0});
		if(Info::isValid($pid)){
			$packet = new RakNetPacket($pid);
			$packet->buffer =& $buffer;
			$packet->ip = $source;
			$packet->port = $port;
			$packet->decode();
		}elseif($pid === 0xfe and $buffer{1} === "\xfd"){
			$packet = new QueryPacket;
			$packet->ip = $source;
			$packet->port = $port;
			$packet->buffer =& $buffer;
		}else{
			$packet = new Packet($pid);
			$packet->ip = $source;
			$packet->port = $port;
			$packet->buffer =& $buffer;
		}
		$this->packets->synchronized(function (Packet $packet){
			$this->packets[] = $packet;
		}, $packet);

		return true;
	}

}

?>