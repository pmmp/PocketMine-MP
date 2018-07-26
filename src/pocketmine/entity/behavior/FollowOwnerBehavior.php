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

use pocketmine\entity\Tamable;
use pocketmine\Player;

/**
 * Override necessary
 * @property Tamable $mob
 */
class FollowOwnerBehavior extends Behavior
{

    /** @var float */
    protected $speedMultiplier;
    /** @var int */
    protected $followDelay = 0;

    public function __construct(Tamable $mob, float $speedMultiplier)
    {
        parent::__construct($mob);

        $this->speedMultiplier = $speedMultiplier;
        $this->mutexBits = 3;
    }

    public function canStart(): bool
    {
        if (!$this->mob->isTamed()) return false;
        if ($this->mob->getOwningEntity() === null or $this->mob->isLeashed() or $this->mob->isSitting()) return false;

        return true;
    }

    public function onStart(): void
    {
        $this->mob->getNavigator()->tryMoveTo($this->mob->getOwningEntity(), $this->speedMultiplier);
    }

    public function onTick(): void
    {
        /** @var Player $owner */
        $owner = $this->mob->getOwningEntity();
        if ($owner == null) return;

        $distanceToPlayer = $this->mob->distanceSquared($owner);

        if ($distanceToPlayer < 1.75) {
            $this->mob->getNavigator()->clearPath();
            $this->mob->setLookPosition($owner);
            return;
        }

        $this->followDelay--;

        if ($this->followDelay < 0) {
            $this->followDelay = 10;
            $m = 2 - $distanceToPlayer;
            $m = ($m <= 0) ? 1 : $m / 2.0;
            $this->mob->getNavigator()->tryMoveTo($owner, $this->speedMultiplier * $m);
            if ($distanceToPlayer > 144) {
                $this->mob->setPosition($owner);
                $this->mob->getNavigator()->clearPath();
            }
        }

        $this->mob->setLookPosition($owner);
    }

    public function onEnd(): void
    {
        $this->mob->resetMotion();
        $this->mob->pitch = 0;
        $this->mob->getNavigator()->clearPath();
    }
}