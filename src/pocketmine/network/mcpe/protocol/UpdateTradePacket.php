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
use pocketmine\network\mcpe\protocol\types\WindowTypes;

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
	public $isWilling;
	/** @var bool */
	public $isV2Trading;
	/** @var string */
	public $offers;

	protected function decodePayload() : void{
		$this->windowId = $this->getByte();
		$this->windowType = $this->getByte();
		$this->thisIsAlwaysZero = $this->getVarInt();
		$this->tradeTier = $this->getVarInt();
		$this->traderEid = $this->getEntityUniqueId();
		$this->playerEid = $this->getEntityUniqueId();
		$this->displayName = $this->getString();
		$this->isWilling = $this->getBool();
		$this->isV2Trading = $this->getBool();
		$this->offers = $this->getRemaining();
	}

	protected function encodePayload() : void{
		$this->putByte($this->windowId);
		$this->putByte($this->windowType);
		$this->putVarInt($this->thisIsAlwaysZero);
		$this->putVarInt($this->tradeTier);
		$this->putEntityUniqueId($this->traderEid);
		$this->putEntityUniqueId($this->playerEid);
		$this->putString($this->displayName);
		$this->putBool($this->isWilling);
		$this->putBool($this->isV2Trading);
		$this->put($this->offers);
	}

	public function handle(PacketHandler $handler) : bool{
		return $handler->handleUpdateTrade($this);
	}
}
