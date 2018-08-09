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

class InteractPacket extends DataPacket{
    public const NETWORK_ID = ProtocolInfo::INTERACT_PACKET;

    public const ACTION_LEAVE_VEHICLE = 3;
    public const ACTION_MOUSEOVER = 4;

    public const ACTION_OPEN_INVENTORY = 6;

    /** @var int */
    public $action;
    /** @var int */
    public $target;

    /** @var float */
    public $x;
    /** @var float */
    public $y;
    /** @var float */
    public $z;

    protected function decodePayload() : void{
        $this->action = $this->getByte();
        $this->target = $this->getEntityRuntimeId();

        if($this->action === self::ACTION_MOUSEOVER){
            //TODO: should this be a vector3?
            $this->x = $this->getLFloat();
            $this->y = $this->getLFloat();
            $this->z = $this->getLFloat();
        }
    }

    protected function encodePayload() : void{
        $this->putByte($this->action);
        $this->putEntityRuntimeId($this->target);

        if($this->action === self::ACTION_MOUSEOVER){
            $this->putLFloat($this->x);
            $this->putLFloat($this->y);
            $this->putLFloat($this->z);
        }
    }

    public function handle(SessionHandler $handler) : bool{
        return $handler->handleInteract($this);
    }
}