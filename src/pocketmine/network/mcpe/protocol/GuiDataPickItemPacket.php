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

class GuiDataPickItemPacket extends DataPacket{
    public const NETWORK_ID = ProtocolInfo::GUI_DATA_PICK_ITEM_PACKET;

    /** @var int */
    public $hotbarSlot;

    protected function decodePayload() : void{
        $this->hotbarSlot = $this->getLInt();
    }

    protected function encodePayload() : void{
        $this->putLInt($this->hotbarSlot);
    }

    public function handle(SessionHandler $handler) : bool{
        return $handler->handleGuiDataPickItem($this);
    }
}