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

namespace pocketmine\network\mcpe\handler;

use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;
use pocketmine\network\mcpe\protocol\RespawnPacket;
use pocketmine\Player;

class DeathSessionHandler extends SessionHandler{

    /** @var Player */
    private $player;
    /** @var NetworkSession */
    private $session;

    public function __construct(Player $player, NetworkSession $session){
        $this->player = $player;
        $this->session = $session;
    }

    public function setUp() : void{
        $pk = new RespawnPacket();
        $pk->position = $this->player->getOffsetPosition($this->player->getSpawn());
        $this->session->sendDataPacket($pk);
    }

    public function handlePlayerAction(PlayerActionPacket $packet) : bool{
        switch($packet->action){
            case PlayerActionPacket::ACTION_RESPAWN:
                $this->player->respawn();
                return true;
            case PlayerActionPacket::ACTION_DIMENSION_CHANGE_REQUEST:
                $this->player->teleport($this->player->getServer()->getDefaultLevel()->getSpawnLocation());
				$this->player->respawn();
				return true;
        }

        return false;
    }
}
