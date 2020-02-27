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
use pocketmine\network\mcpe\serializer\NetworkBinaryStream;

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

	protected function decodePayload(NetworkBinaryStream $in) : void{
		$this->windowId = $in->getByte();
		$this->windowType = $in->getByte();
		$this->thisIsAlwaysZero = $in->getVarInt();
		$this->tradeTier = $in->getVarInt();
		$this->traderEid = $in->getEntityUniqueId();
		$this->playerEid = $in->getEntityUniqueId();
		$this->displayName = $in->getString();
		$this->isV2Trading = $in->getBool();
		$this->isWilling = $in->getBool();
		$this->offers = $in->getRemaining();
	}

	protected function encodePayload(NetworkBinaryStream $out) : void{
		$out->putByte($this->windowId);
		$out->putByte($this->windowType);
		$out->putVarInt($this->thisIsAlwaysZero);
		$out->putVarInt($this->tradeTier);
		$out->putEntityUniqueId($this->traderEid);
		$out->putEntityUniqueId($this->playerEid);
		$out->putString($this->displayName);
		$out->putBool($this->isV2Trading);
		$out->putBool($this->isWilling);
		$out->put($this->offers);
	}

	public function handle(PacketHandler $handler) : bool{
		return $handler->handleUpdateTrade($this);
	}
}
