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

class PlayerInputPacket extends DataPacket{
    public const NETWORK_ID = ProtocolInfo::PLAYER_INPUT_PACKET;

    /** @var float */
    public $motionX;
    /** @var float */
    public $motionY;
    /** @var bool */
    public $jumping;
    /** @var bool */
    public $sneaking;

    protected function decodePayload() : void{
        $this->motionX = $this->getLFloat();
        $this->motionY = $this->getLFloat();
        $this->jumping = $this->getBool();
        $this->sneaking = $this->getBool();
    }

    protected function encodePayload() : void{
        $this->putLFloat($this->motionX);
        $this->putLFloat($this->motionY);
        $this->putBool($this->jumping);
        $this->putBool($this->sneaking);
    }

    public function handle(SessionHandler $handler) : bool{
        return $handler->handlePlayerInput($this);
    }
}