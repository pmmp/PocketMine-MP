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

use pocketmine\entity\Mob;

class FloatBehavior extends Behavior
{

    public function __construct(Mob $mob)
    {
        parent::__construct($mob);
        $this->mutexBits = 4;
    }

    public function canStart(): bool
    {
        return $this->mob->isUnderWater();
    }

    public function onStart(): void
    {
        $this->mob->setGenericFlag(Mob::DATA_FLAG_SWIMMER, true);
    }

    public function onEnd(): void
    {
        $this->mob->setGenericFlag(Mob::DATA_FLAG_SWIMMER, false);
    }

    public function onTick(): void
    {
        if ($this->mob->isUnderWater()) {
            if ($this->random->nextFloat() < 0.8) {
                $this->mob->jump();
            }
        }
    }
}