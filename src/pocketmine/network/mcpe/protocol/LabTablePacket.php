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

class LabTablePacket extends DataPacket{
    public const NETWORK_ID = ProtocolInfo::LAB_TABLE_PACKET;

    /** @var int */
    public $uselessByte; //0 for client -> server, 1 for server -> client. Seems useless.

    /** @var int */
    public $x;
    /** @var int */
    public $y;
    /** @var int */
    public $z;

    /** @var int */
    public $reactionType;

    protected function decodePayload() : void{
        $this->uselessByte = $this->getByte();
        $this->getSignedBlockPosition($this->x, $this->y, $this->z);
        $this->reactionType = $this->getByte();
    }

    protected function encodePayload() : void{
        $this->putByte($this->uselessByte);
        $this->putSignedBlockPosition($this->x, $this->y, $this->z);
        $this->putByte($this->reactionType);
    }

    public function handle(SessionHandler $handler) : bool{
        return $handler->handleLabTable($this);
    }
}