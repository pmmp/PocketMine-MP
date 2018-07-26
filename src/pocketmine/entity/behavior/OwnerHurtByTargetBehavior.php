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

use pocketmine\entity\Living;
use pocketmine\entity\Mob;

class OwnerHurtByTargetBehavior extends Behavior
{

    protected $mutexBits = 1;

    public function canStart(): bool
    {
        $owner = $this->mob->getOwningEntity();

        /** @var Living $owner */
        if ($owner !== null) {
            $attacker = $owner->getLastAttacker();
            if ($attacker instanceof Mob) {
                $this->mob->setTargetEntity($attacker);
                return true;
            }
        }

        return false;
    }

    public function canContinue(): bool
    {
        return false;
    }
}