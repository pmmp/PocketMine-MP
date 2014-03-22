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

use PocketMine;
use PocketMine\Event\Event;
use PocketMine\Event\EventHandler;
use PocketMine\Event\Server\PacketReceiveEvent;
use PocketMine\Event\Server\PacketSendEvent;
use PocketMine\Network\Query\QueryHandler;
use PocketMine\Network\Query\QueryPacket;
use PocketMine\Network\RakNet\Info;
use PocketMine\Network\RakNet\Packet;
use PocketMine\ServerAPI;

class Handler{
	public $bandwidth;
	private $socket;
	private $packets;

	function __construct($server, $port = 25565, $serverip = "0.0.0.0"){
		$this->socket = new UDPSocket($server, $port, true, $serverip);
		if($this->socket->connected === false){
			console("[SEVERE] Couldn't bind to $serverip:" . $port, true, true, 0);
			exit(1);
		}
		$this->bandwidth = array(0, 0, microtime(true));
		$this->packets = array();
	}

	public function close(){
		$this->socket->close(false);
	}

	public function readPacket(){
		$buf = null;
		$source = null;
		$port = null;
		$len = $this->socket->read($buffer, $source, $port);
		if($len === false or $len === 0){
			return false;
		}
		$this->bandwidth[0] += $len;

		$pid = ord($buffer{0});

		if(Info::isValid($pid)){
			$packet = new Packet($pid);
			$packet->buffer =& $buffer;
			$packet->ip = $source;
			$packet->port = $port;
			$packet->decode();
			if(EventHandler::callEvent(new PacketReceiveEvent($packet)) === Event::DENY){
				return false;
			}

			return $packet;
		}elseif($pid === 0xfe and $buffer{1} === "\xfd" and ServerAPI::request()->api->query instanceof QueryHandler){
			$packet = new QueryPacket;
			$packet->ip = $source;
			$packet->port = $port;
			$packet->buffer =& $buffer;
			if(EventHandler::callEvent(new PacketReceiveEvent($packet)) === Event::DENY){
				return false;
			}
			ServerAPI::request()->api->query->handle($packet);
		}else{
			$packet = new Packet($pid);
			$packet->ip = $source;
			$packet->port = $port;
			$packet->buffer =& $buffer;
			EventHandler::callEvent(new PacketReceiveEvent($packet));

			return false;
		}

		return true;
	}

	public function writePacket(Packet $packet){
		if(EventHandler::callEvent(new PacketSendEvent($packet)) === Event::DENY){
			return 0;
		}elseif($packet instanceof Packet){
			$packet->encode();
		}
		$write = $this->socket->write($packet->buffer, $packet->ip, $packet->port);
		$this->bandwidth[1] += $write;

		return $write;
	}

}

?>