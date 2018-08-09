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


use pocketmine\network\mcpe\handler\SessionHandler;
use pocketmine\network\mcpe\protocol\types\WindowTypes;

class UpdateTradePacket extends DataPacket{
    public const NETWORK_ID = ProtocolInfo::UPDATE_TRADE_PACKET;

    //TODO: find fields

    /** @var int */
    public $windowId;
    /** @var int */
    public $windowType = WindowTypes::TRADING; //Mojang hardcoded this -_-
    /** @var int */
    public $varint1;
    /** @var int */
    public $varint2;
    /** @var bool */
    public $isWilling;
    /** @var int */
    public $traderEid;
    /** @var int */
    public $playerEid;
    /** @var string */
    public $displayName;
    /** @var string */
    public $offers;

    protected function decodePayload() : void{
        $this->windowId = $this->getByte();
        $this->windowType = $this->getByte();
        $this->varint1 = $this->getVarInt();
        $this->varint2 = $this->getVarInt();
        $this->isWilling = $this->getBool();
        $this->traderEid = $this->getEntityUniqueId();
        $this->playerEid = $this->getEntityUniqueId();
        $this->displayName = $this->getString();
        $this->offers = $this->getRemaining();
    }

    protected function encodePayload() : void{
        $this->putByte($this->windowId);
        $this->putByte($this->windowType);
        $this->putVarInt($this->varint1);
        $this->putVarInt($this->varint2);
        $this->putBool($this->isWilling);
        $this->putEntityUniqueId($this->traderEid);
        $this->putEntityUniqueId($this->playerEid);
        $this->putString($this->displayName);
        $this->put($this->offers);
    }

    public function handle(SessionHandler $handler) : bool{
        return $handler->handleUpdateTrade($this);
    }
}