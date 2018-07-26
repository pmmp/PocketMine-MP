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

class PanicBehavior extends WanderBehavior
{

    public function canStart(): bool
    {
        if($this->mob->getLastAttacker() !== null or $this->mob->isOnFire()){
            $this->targetPos = $this->findRandomTargetBlock($this->mob, 5, 4);
            $this->followRange = $this->mob->distanceSquared($this->targetPos) + 2;

            return true;
        }
        return false;
    }

    public function onEnd(): void
    {
        $this->mob->setLastAttacker(null);
        parent::onEnd();
    }
}