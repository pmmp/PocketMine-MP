<?php

/*
 * RakLib network library
 *
 *
 * This project is not affiliated with Jenkins Software LLC nor RakNet.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 */

declare(strict_types=1);

namespace raklib\server;

use pocketmine\utils\Binary;
use raklib\protocol\EncapsulatedPacket;
use raklib\RakLib;
use function chr;
use function ord;
use function strlen;
use function substr;

class ServerHandler{

	/** @var RakLibServer */
	protected $server;
	/** @var ServerInstance */
	protected $instance;

	public function __construct(RakLibServer $server, ServerInstance $instance){
		$this->server = $server;
		$this->instance = $instance;
	}

	public function sendEncapsulated(string $identifier, EncapsulatedPacket $packet, int $flags = RakLib::PRIORITY_NORMAL) : void{
		$buffer = chr(RakLib::PACKET_ENCAPSULATED) . chr(strlen($identifier)) . $identifier . chr($flags) . $packet->toInternalBinary();
		$this->server->pushMainToThreadPacket($buffer);
	}

	public function sendRaw(string $address, int $port, string $payload) : void{
		$buffer = chr(RakLib::PACKET_RAW) . chr(strlen($address)) . $address . Binary::writeShort($port) . $payload;
		$this->server->pushMainToThreadPacket($buffer);
	}

	public function closeSession(string $identifier, string $reason) : void{
		$buffer = chr(RakLib::PACKET_CLOSE_SESSION) . chr(strlen($identifier)) . $identifier . chr(strlen($reason)) . $reason;
		$this->server->pushMainToThreadPacket($buffer);
	}

	/**
	 * @param string $name
	 * @param mixed  $value Must be castable to string
	 */
	public function sendOption(string $name, $value) : void{
		$buffer = chr(RakLib::PACKET_SET_OPTION) . chr(strlen($name)) . $name . $value;
		$this->server->pushMainToThreadPacket($buffer);
	}

	public function blockAddress(string $address, int $timeout) : void{
		$buffer = chr(RakLib::PACKET_BLOCK_ADDRESS) . chr(strlen($address)) . $address . Binary::writeInt($timeout);
		$this->server->pushMainToThreadPacket($buffer);
	}

	public function unblockAddress(string $address) : void{
		$buffer = chr(RakLib::PACKET_UNBLOCK_ADDRESS) . chr(strlen($address)) . $address;
		$this->server->pushMainToThreadPacket($buffer);
	}

	public function shutdown() : void{
		$buffer = chr(RakLib::PACKET_SHUTDOWN);
		$this->server->pushMainToThreadPacket($buffer);
		$this->server->shutdown();
		$this->server->join();
	}

	public function emergencyShutdown() : void{
		$this->server->shutdown();
		$this->server->pushMainToThreadPacket(chr(RakLib::PACKET_EMERGENCY_SHUTDOWN));
	}

	/**
	 * @return bool
	 */
	public function handlePacket() : bool{
		if(($packet = $this->server->readThreadToMainPacket()) !== null){
			$id = ord($packet{0});
			$offset = 1;
			if($id === RakLib::PACKET_ENCAPSULATED){
				$len = ord($packet{$offset++});
				$identifier = substr($packet, $offset, $len);
				$offset += $len;
				$flags = ord($packet{$offset++});
				$buffer = substr($packet, $offset);
				$this->instance->handleEncapsulated($identifier, EncapsulatedPacket::fromInternalBinary($buffer), $flags);
			}elseif($id === RakLib::PACKET_RAW){
				$len = ord($packet{$offset++});
				$address = substr($packet, $offset, $len);
				$offset += $len;
				$port = Binary::readShort(substr($packet, $offset, 2));
				$offset += 2;
				$payload = substr($packet, $offset);
				$this->instance->handleRaw($address, $port, $payload);
			}elseif($id === RakLib::PACKET_SET_OPTION){
				$len = ord($packet{$offset++});
				$name = substr($packet, $offset, $len);
				$offset += $len;
				$value = substr($packet, $offset);
				$this->instance->handleOption($name, $value);
			}elseif($id === RakLib::PACKET_OPEN_SESSION){
				$len = ord($packet{$offset++});
				$identifier = substr($packet, $offset, $len);
				$offset += $len;
				$len = ord($packet{$offset++});
				$address = substr($packet, $offset, $len);
				$offset += $len;
				$port = Binary::readShort(substr($packet, $offset, 2));
				$offset += 2;
				$clientID = Binary::readLong(substr($packet, $offset, 8));
				$this->instance->openSession($identifier, $address, $port, $clientID);
			}elseif($id === RakLib::PACKET_CLOSE_SESSION){
				$len = ord($packet{$offset++});
				$identifier = substr($packet, $offset, $len);
				$offset += $len;
				$len = ord($packet{$offset++});
				$reason = substr($packet, $offset, $len);
				$this->instance->closeSession($identifier, $reason);
			}elseif($id === RakLib::PACKET_INVALID_SESSION){
				$len = ord($packet{$offset++});
				$identifier = substr($packet, $offset, $len);
				$this->instance->closeSession($identifier, "Invalid session");
			}elseif($id === RakLib::PACKET_ACK_NOTIFICATION){
				$len = ord($packet{$offset++});
				$identifier = substr($packet, $offset, $len);
				$offset += $len;
				$identifierACK = Binary::readInt(substr($packet, $offset, 4));
				$this->instance->notifyACK($identifier, $identifierACK);
			}elseif($id === RakLib::PACKET_REPORT_PING){
				$len = ord($packet{$offset++});
				$identifier = substr($packet, $offset, $len);
				$offset += $len;
				$pingMS = Binary::readInt(substr($packet, $offset, 4));
				$this->instance->updatePing($identifier, $pingMS);
			}

			return true;
		}

		return false;
	}
}
