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


use raklib\RakLib;
use function str_pad;
use function strlen;

class OpenConnectionRequest1 extends OfflineMessage{
	public static $ID = MessageIdentifiers::ID_OPEN_CONNECTION_REQUEST_1;

	/** @var int */
	public $protocol = RakLib::DEFAULT_PROTOCOL_VERSION;
	/** @var int */
	public $mtuSize;

	protected function encodePayload() : void{
		$this->writeMagic();
		$this->putByte($this->protocol);
		$this->buffer = str_pad($this->buffer, $this->mtuSize, "\x00");
	}

	protected function decodePayload() : void{
		$this->readMagic();
		$this->protocol = $this->getByte();
		$this->mtuSize = strlen($this->buffer);
	}
}
