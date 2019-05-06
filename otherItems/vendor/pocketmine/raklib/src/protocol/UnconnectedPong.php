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

class UnconnectedPong extends OfflineMessage{
	public static $ID = MessageIdentifiers::ID_UNCONNECTED_PONG;

	/** @var int */
	public $pingID;
	/** @var int */
	public $serverID;
	/** @var string */
	public $serverName;

	protected function encodePayload() : void{
		$this->putLong($this->pingID);
		$this->putLong($this->serverID);
		$this->writeMagic();
		$this->putString($this->serverName);
	}

	protected function decodePayload() : void{
		$this->pingID = $this->getLong();
		$this->serverID = $this->getLong();
		$this->readMagic();
		$this->serverName = $this->getString();
	}
}
