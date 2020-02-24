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

use pocketmine\network\mcpe\handler\PacketHandler;
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;

class UpdateTradePacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::UPDATE_TRADE_PACKET;

	//TODO: find fields

	/** @var int */
	public $windowId;
	/** @var int */
	public $windowType = WindowTypes::TRADING; //Mojang hardcoded this -_-
	/** @var int */
	public $thisIsAlwaysZero = 0; //hardcoded to 0
	/** @var int */
	public $tradeTier;
	/** @var int */
	public $traderEid;
	/** @var int */
	public $playerEid;
	/** @var string */
	public $displayName;
	/** @var bool */
	public $isV2Trading;
	/** @var bool */
	public $isWilling;
	/** @var string */
	public $offers;

	protected function decodePayload() : void{
		$this->windowId = $this->buf->getByte();
		$this->windowType = $this->buf->getByte();
		$this->thisIsAlwaysZero = $this->buf->getVarInt();
		$this->tradeTier = $this->buf->getVarInt();
		$this->traderEid = $this->buf->getEntityUniqueId();
		$this->playerEid = $this->buf->getEntityUniqueId();
		$this->displayName = $this->buf->getString();
		$this->isV2Trading = $this->buf->getBool();
		$this->isWilling = $this->buf->getBool();
		$this->offers = $this->buf->getRemaining();
	}

	protected function encodePayload() : void{
		$this->buf->putByte($this->windowId);
		$this->buf->putByte($this->windowType);
		$this->buf->putVarInt($this->thisIsAlwaysZero);
		$this->buf->putVarInt($this->tradeTier);
		$this->buf->putEntityUniqueId($this->traderEid);
		$this->buf->putEntityUniqueId($this->playerEid);
		$this->buf->putString($this->displayName);
		$this->buf->putBool($this->isV2Trading);
		$this->buf->putBool($this->isWilling);
		$this->buf->put($this->offers);
	}

	public function handle(PacketHandler $handler) : bool{
		return $handler->handleUpdateTrade($this);
	}
}
