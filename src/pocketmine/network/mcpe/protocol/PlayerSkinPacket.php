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

use pocketmine\entity\Skin;
use pocketmine\network\mcpe\handler\SessionHandler;
use pocketmine\utils\UUID;

class PlayerSkinPacket extends DataPacket{
    public const NETWORK_ID = ProtocolInfo::PLAYER_SKIN_PACKET;

    /** @var UUID */
    public $uuid;
    /** @var string */
    public $oldSkinName = "";
    /** @var string */
    public $newSkinName = "";
    /** @var Skin */
    public $skin;

    protected function decodePayload() : void{
        $this->uuid = $this->getUUID();

        $skinId = $this->getString();
        $this->newSkinName = $this->getString();
        $this->oldSkinName = $this->getString();
        $skinData = $this->getString();
        $capeData = $this->getString();
        $geometryModel = $this->getString();
        $geometryData = $this->getString();

        $this->skin = new Skin($skinId, $skinData, $capeData, $geometryModel, $geometryData);
    }

    protected function encodePayload() : void{
        $this->putUUID($this->uuid);

        $this->putString($this->skin->getSkinId());
        $this->putString($this->newSkinName);
        $this->putString($this->oldSkinName);
        $this->putString($this->skin->getSkinData());
        $this->putString($this->skin->getCapeData());
        $this->putString($this->skin->getGeometryName());
        $this->putString($this->skin->getGeometryData());
    }

    public function handle(SessionHandler $handler) : bool{
        return $handler->handlePlayerSkin($this);
    }
}