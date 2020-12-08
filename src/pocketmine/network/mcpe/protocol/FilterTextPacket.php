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

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>

use pocketmine\network\mcpe\NetworkSession;

class FilterTextPacket extends DataPacket/* implements ClientboundPacket, ServerboundPacket*/{
	public const NETWORK_ID = ProtocolInfo::FILTER_TEXT_PACKET;

	/** @var string */
	private $text;
	/** @var bool */
	private $fromServer;

	public static function create(string $text, bool $server) : self{
		$result = new self;
		$result->text = $text;
		$result->fromServer = $server;
		return $result;
	}

	public function getText() : string{ return $this->text; }

	public function isFromServer() : bool{ return $this->fromServer; }

	protected function decodePayload() : void{
		$this->text = $this->getString();
		$this->fromServer = $this->getBool();
	}

	protected function encodePayload() : void{
		$this->putString($this->text);
		$this->putBool($this->fromServer);
	}

	public function handle(NetworkSession $handler) : bool{
		return $handler->handleFilterText($this);
	}
}
