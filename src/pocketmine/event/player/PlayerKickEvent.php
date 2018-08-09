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

namespace pocketmine\event\player;

use pocketmine\event\Cancellable;
use pocketmine\lang\TextContainer;
use pocketmine\Player;

/**
 * Called when a player leaves the server
 */
class PlayerKickEvent extends PlayerEvent implements Cancellable{
    /** @var TextContainer|string */
    protected $quitMessage;

    /** @var string */
    protected $reason;

    /**
     * PlayerKickEvent constructor.
     *
     * @param Player               $player
     * @param string               $reason
     * @param TextContainer|string $quitMessage
     */
    public function __construct(Player $player, string $reason, $quitMessage){
        $this->player = $player;
        $this->quitMessage = $quitMessage;
        $this->reason = $reason;
    }

    /**
     * @param string $reason
     */
    public function setReason(string $reason) : void{
        $this->reason = $reason;
    }

    public function getReason() : string{
        return $this->reason;
    }

    /**
     * @param TextContainer|string $quitMessage
     */
    public function setQuitMessage($quitMessage) : void{
        $this->quitMessage = $quitMessage;
    }

    /**
     * @return TextContainer|string
     */
    public function getQuitMessage(){
        return $this->quitMessage;
    }

}