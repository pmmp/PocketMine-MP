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

namespace raklib\protocol;

use pocketmine\utils\BinaryStream;
use raklib\utils\InternetAddress;
use function assert;
use function count;
use function explode;
use function inet_ntop;
use function inet_pton;
use function strlen;
use const AF_INET6;
#ifndef COMPILE
use pocketmine\utils\Binary;
#endif

#include <rules/RakLibPacket.h>

abstract class Packet extends BinaryStream{
	public static $ID = -1;

	/** @var float|null */
	public $sendTime;

	protected function getString() : string{
		return $this->get($this->getShort());
	}

	protected function getAddress() : InternetAddress{
		$version = $this->getByte();
		if($version === 4){
			$addr = ((~$this->getByte()) & 0xff) . "." . ((~$this->getByte()) & 0xff) . "." . ((~$this->getByte()) & 0xff) . "." . ((~$this->getByte()) & 0xff);
			$port = $this->getShort();
			return new InternetAddress($addr, $port, $version);
		}elseif($version === 6){
			//http://man7.org/linux/man-pages/man7/ipv6.7.html
			Binary::readLShort($this->get(2)); //Family, AF_INET6
			$port = $this->getShort();
			$this->getInt(); //flow info
			$addr = inet_ntop($this->get(16));
			$this->getInt(); //scope ID
			return new InternetAddress($addr, $port, $version);
		}else{
			throw new \UnexpectedValueException("Unknown IP address version $version");
		}
	}

	protected function putString(string $v) : void{
		$this->putShort(strlen($v));
		$this->put($v);
	}

	protected function putAddress(InternetAddress $address) : void{
		$this->putByte($address->version);
		if($address->version === 4){
			$parts = explode(".", $address->ip);
			assert(count($parts) === 4, "Wrong number of parts in IPv4 IP, expected 4, got " . count($parts));
			foreach($parts as $b){
				$this->putByte((~((int) $b)) & 0xff);
			}
			$this->putShort($address->port);
		}elseif($address->version === 6){
			$this->put(Binary::writeLShort(AF_INET6));
			$this->putShort($address->port);
			$this->putInt(0);
			$this->put(inet_pton($address->ip));
			$this->putInt(0);
		}else{
			throw new \InvalidArgumentException("IP version $address->version is not supported");
		}
	}

	public function encode() : void{
		$this->reset();
		$this->encodeHeader();
		$this->encodePayload();
	}

	protected function encodeHeader() : void{
		$this->putByte(static::$ID);
	}

	abstract protected function encodePayload() : void;

	public function decode() : void{
		$this->offset = 0;
		$this->decodeHeader();
		$this->decodePayload();
	}

	protected function decodeHeader() : void{
		$this->getByte(); //PID
	}

	abstract protected function decodePayload() : void;

	public function clean(){
		$this->buffer = null;
		$this->offset = 0;
		$this->sendTime = null;

		return $this;
	}
}
