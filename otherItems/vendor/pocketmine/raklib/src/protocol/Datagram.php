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

#include <rules/RakLibPacket.h>

use function strlen;
use function substr;

class Datagram extends Packet{
	public const BITFLAG_VALID = 0x80;
	public const BITFLAG_ACK = 0x40;
	public const BITFLAG_NAK = 0x20; // hasBAndAS for ACKs

	/*
	 * These flags can be set on regular datagrams, but they are useless as per the public version of RakNet
	 * (the receiving client will not use them or pay any attention to them).
	 */
	public const BITFLAG_PACKET_PAIR = 0x10;
	public const BITFLAG_CONTINUOUS_SEND = 0x08;
	public const BITFLAG_NEEDS_B_AND_AS = 0x04;

	/** @var int */
	public $headerFlags = 0;

	/** @var EncapsulatedPacket[] */
	public $packets = [];

	/** @var int */
	public $seqNumber;

	protected function encodeHeader() : void{
		$this->putByte(self::BITFLAG_VALID | $this->headerFlags);
	}

	protected function encodePayload() : void{
		$this->putLTriad($this->seqNumber);
		foreach($this->packets as $packet){
			$this->put($packet instanceof EncapsulatedPacket ? $packet->toBinary() : (string) $packet);
		}
	}

	public function length(){
		$length = 4;
		foreach($this->packets as $packet){
			$length += $packet instanceof EncapsulatedPacket ? $packet->getTotalLength() : strlen($packet);
		}

		return $length;
	}

	protected function decodeHeader() : void{
		$this->headerFlags = $this->getByte();
	}

	protected function decodePayload() : void{
		$this->seqNumber = $this->getLTriad();

		while(!$this->feof()){
			$offset = 0;
			$data = substr($this->buffer, $this->offset);
			$packet = EncapsulatedPacket::fromBinary($data, $offset);
			$this->offset += $offset;
			if($packet->buffer === ''){
				break;
			}
			$this->packets[] = $packet;
		}
	}

	public function clean(){
		$this->packets = [];
		$this->seqNumber = null;

		return parent::clean();
	}
}
