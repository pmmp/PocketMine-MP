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
use pocketmine\Player;

class FindAttackableTargetBehavior extends Behavior
{

    /** @var float */
    protected $targetDistance = 16.0;
    /** @var int */
    protected $targetUnseenTicks = 0;
    /** @var string */
    protected $targetClass;

    public function __construct(Mob $mob, string $targetClass = Mob::class)
    {
        parent::__construct($mob);

        $this->targetDistance = $mob->getFollowRange() ** 2;
        $this->targetClass = $targetClass;
        $this->mutexBits = 1;
    }

    public function canStart(): bool
    {
        if ($this->random->nextBoundedInt(10) === 0) {
            /** @var Entity[] $targets */
            $targets = array_filter($this->mob->level->getEntities(), function (Entity $e) {
                return get_class($e) === $this->targetClass and $e->isAlive();
            });
            $target = null;
            $lastDist = $this->targetDistance;
            foreach ($targets as $t) {
                if ($d = $t->distanceSquared($this->mob) < $lastDist and $t !== $this->mob) {
                    if ($t instanceof Player and !$t->isSurvival()) continue;
                    $target = $t;
                    $lastDist = $d;
                }
            }

            if ($target === null) return false;

            $this->mob->setTargetEntity($target);

            return true;
        }

        return false;
    }

    public function getTargetDistance(Player $p)
    {
        $dist = $this->targetDistance;
        if ($p->isSneaking())
            $dist *= 0.8;

        return $dist;
    }

    public function onStart(): void
    {
        $this->targetUnseenTicks = 0;
    }

    public function canContinue(): bool
    {
        $target = $this->mob->getTargetEntity();

        if ($target === null or !$target->isAlive() or ($target instanceof Player and !$target->isSurvival(true))) return false;

        if ($target instanceof Player) {
            if ($this->mob->distanceSquared($target) > $this->getTargetDistance($target)) return false;

            if ($this->mob->canSeeEntity($target)) {
                $this->targetUnseenTicks = 0;
            } elseif ($this->targetUnseenTicks++ > 60) {
                return false;
            }
        } else {
            if ($this->mob->distanceSquared($target) > $this->targetDistance) {
                return false;
            }
        }

        return true;
    }

    public function onEnd(): void
    {
        $this->mob->setTargetEntity(null);
    }
}