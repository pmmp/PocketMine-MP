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

use pocketmine\entity\Animal;

class MateBehavior extends Behavior
{
    /** @var float */
    protected $speedMultiplier;
    /** @var int */
    protected $spawnBabyDelay = 0;
    /** @var Animal */
    protected $targetMate;

    public function __construct(Animal $mob, float $speedMultiplier)
    {
        parent::__construct($mob);

        $this->speedMultiplier = $speedMultiplier;
        $this->mutexBits = 3;
    }

    public function canStart(): bool
    {
        if ($this->mob->isInLove()) {
            $this->targetMate = $this->getNearbyMate();
            return $this->targetMate !== null;
        }

        return false;
    }

    public function canContinue(): bool
    {
        return $this->targetMate->isAlive() and $this->targetMate->isInLove() and $this->spawnBabyDelay < 60;
    }

    public function onTick(): void
    {
        $this->mob->getNavigator()->tryMoveTo($this->targetMate, $this->speedMultiplier);

        $this->spawnBabyDelay++;

        if ($this->spawnBabyDelay >= 60 and $this->mob->distance($this->targetMate) < 9) {
            $this->spawnBaby();
        }
    }

    public function onEnd(): void
    {
        $this->targetMate = null;
        $this->spawnBabyDelay = 0;
    }

    public function getNearbyMate(): ?Animal
    {
        $list = $this->mob->level->getNearbyEntities($this->mob->getBoundingBox()->expandedCopy(8, 8, 8), $this->mob);
        $dist = PHP_INT_MAX;
        $animal = null;

        foreach ($list as $entity) {
            if ($entity instanceof Animal and $entity->isInLove() and $entity->distance($this->mob) < $dist and $entity->getSaveId() === $this->mob->getSaveId()) {
                $dist = $entity->distance($this->mob);
                $animal = $entity;
            }
        }

        return $animal;
    }

    private function spawnBaby(): void
    {
        // TODO: Spawn baby
    }

}