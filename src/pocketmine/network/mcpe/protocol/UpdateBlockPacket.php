<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>


use pocketmine\network\mcpe\handler\SessionHandler;

class UpdateBlockPacket extends DataPacket{
    public const NETWORK_ID = ProtocolInfo::UPDATE_BLOCK_PACKET;

    public const FLAG_NONE      = 0b0000;
    public const FLAG_NEIGHBORS = 0b0001;
    public const FLAG_NETWORK   = 0b0010;
    public const FLAG_NOGRAPHIC = 0b0100;
    public const FLAG_PRIORITY  = 0b1000;

    public const FLAG_ALL = self::FLAG_NEIGHBORS | self::FLAG_NETWORK;
    public const FLAG_ALL_PRIORITY = self::FLAG_ALL | self::FLAG_PRIORITY;

    public const DATA_LAYER_NORMAL = 0;
    public const DATA_LAYER_LIQUID = 1;

    /** @var int */
    public $x;
    /** @var int */
    public $z;
    /** @var int */
    public $y;
    /** @var int */
    public $blockRuntimeId;
    /** @var int */
    public $flags;
    /** @var int */
    public $dataLayerId = self::DATA_LAYER_NORMAL;

    protected function decodePayload() : void{
        $this->getBlockPosition($this->x, $this->y, $this->z);
        $this->blockRuntimeId = $this->getUnsignedVarInt();
        $this->flags = $this->getUnsignedVarInt();
        $this->dataLayerId = $this->getUnsignedVarInt();
    }

    protected function encodePayload() : void{
        $this->putBlockPosition($this->x, $this->y, $this->z);
        $this->putUnsignedVarInt($this->blockRuntimeId);
        $this->putUnsignedVarInt($this->flags);
        $this->putUnsignedVarInt($this->dataLayerId);
    }

    public function handle(SessionHandler $handler) : bool{
        return $handler->handleUpdateBlock($this);
    }
}