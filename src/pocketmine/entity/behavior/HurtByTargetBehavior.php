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

namespace pocketmine\entity\behavior;

use pocketmine\Player;

class HurtByTargetBehavior extends FindAttackableTargetBehavior
{
    protected $mutexBits = 1;

    public function canStart(): bool
    {
        $player = $this->mob->getLastAttacker();
        return $player !== null and ($player instanceof Player ? $player->isSurvival() : true) and $this->mob->getOwningEntity() !== $player and $player->isAlive();
    }

    public function onStart(): void
    {
        $this->mob->setTargetEntity($this->mob->getLastAttacker());

        parent::onStart();
    }

    public function canContinue(): bool
    {
        $this->mob->setTargetEntity($this->mob->getLastAttacker());
        return $this->canStart() and parent::canContinue();
    }

    public function onEnd(): void
    {
        parent::onEnd();
        $this->mob->setLastAttacker(null);
    }
}