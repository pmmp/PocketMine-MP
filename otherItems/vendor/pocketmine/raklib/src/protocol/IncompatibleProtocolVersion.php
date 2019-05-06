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

class IncompatibleProtocolVersion extends OfflineMessage{
	public static $ID = MessageIdentifiers::ID_INCOMPATIBLE_PROTOCOL_VERSION;

	/** @var int */
	public $protocolVersion;
	/** @var int */
	public $serverId;

	protected function encodePayload() : void{
		$this->putByte($this->protocolVersion);
		$this->writeMagic();
		$this->putLong($this->serverId);
	}

	protected function decodePayload() : void{
		$this->protocolVersion = $this->getByte();
		$this->readMagic();
		$this->serverId = $this->getLong();
	}
}
