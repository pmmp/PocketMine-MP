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

use pocketmine\entity\Entity;
use pocketmine\entity\Mob;

class CreeperSwellBehavior extends Behavior
{

    // TODO : Mob Change to Creeper
    public function __construct(Mob $mob)
    {
        parent::__construct($mob);
        $this->mutexBits = 1;
    }

    public function canStart(): bool
    {
        $target = $this->mob->getTargetEntity();
        return $target === null ? false : ($this->mob->getGenericFlag(Entity::DATA_FLAG_IGNITED) || $this->mob->distance($target) < 3);
    }

    public function onTick(): void
    {
        $target = $this->mob->getTargetEntity();
        if ($target == null or $this->mob->distance($target) > 7 or !$this->mob->canSeeEntity($target)) {
            $this->mob->prime(false);
        } else {
            $this->mob->prime(true);
        }
    }

    public function onEnd(): void
    {
        $this->mob->setTargetEntity(null);
    }
}
