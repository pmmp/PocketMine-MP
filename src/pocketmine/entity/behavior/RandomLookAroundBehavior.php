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

class RandomLookAroundBehavior extends Behavior
{

    /** @var int */
    protected $rotation = 0;
    /** @var int */
    protected $duration = 0;

    public function __construct(Mob $mob)
    {
        parent::__construct($mob);
        $this->mutexBits = 3;
    }

    public function canStart(): bool
    {
        if ($this->random->nextFloat() < 0.02) {
            $this->rotation = $this->random->nextRange(-180, 180);
            $this->duration = 20 + $this->random->nextBoundedInt(20);

            return true;
        }
        return false;
    }

    public function canContinue(): bool
    {
        return $this->duration-- > 0 and abs($this->rotation) > 0;
    }

    public function onTick(): void
    {
        $this->mob->yaw += $this->signRotation($this->rotation) * 10;
        $this->mob->headYaw = $this->mob->yaw;
        $this->rotation -= 10;
    }

    public function signRotation(int $value)
    {
        if ($value > 0) return 1;
        if ($value < 0) return -1;

        return 0;
    }
}