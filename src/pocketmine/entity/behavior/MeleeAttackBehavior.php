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
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\Vector3;

class MeleeAttackBehavior extends Behavior
{

    /** @var float */
    protected $speedMultiplier = 0;

    /** @var int */
    protected $attackCooldown = 0;
    /** @var int */
    protected $delay = 0;
    /** @var Vector3 */
    protected $lastPlayerPos;

    protected $path;

    public function __construct(Mob $mob, float $speedMultiplier)
    {
        parent::__construct($mob);

        $this->speedMultiplier = $speedMultiplier;
        $this->mutexBits = 3;
    }

    public function canStart(): bool
    {
        $target = $this->mob->getTargetEntity();
        if ($target === null) return false;

        $this->lastPlayerPos = $target->asVector3();

        $this->path = $this->mob->getNavigator()->findPath($target);
        return $this->path->havePath();
    }

    public function onStart(): void
    {
        $this->delay = 0;
        $this->mob->getNavigator()->setPath($this->path);
        $this->mob->getNavigator()->setSpeedMultiplier($this->speedMultiplier);
    }

    public function canContinue(): bool
    {
        return $this->mob->getTargetEntityId() !== null;
    }

    public function onTick(): void
    {
        $target = $this->mob->getTargetEntity();
        if ($target == null) return;

        $distanceToPlayer = $this->mob->distanceSquared($target);

        --$this->delay;

        $deltaDistance = $this->lastPlayerPos->distanceSquared($target);

        if ($this->delay <= 0 and $this->mob->canSeeEntity($target) and ($deltaDistance > 1 or $this->random->nextFloat() < 0.05)) {
            $this->lastPlayerPos = $target->asVector3();

            $this->delay = 4 + $this->random->nextBoundedInt(7);

            if ($distanceToPlayer > 1024) {
                $this->delay += 10;
            } elseif ($distanceToPlayer > 256) {
                $this->delay += 5;
            }

            if (!$this->mob->getNavigator()->tryMoveTo($target, $this->speedMultiplier)) {
                $this->delay += 15;
            }
        }

        $this->mob->setLookPosition($target);

        $this->attackCooldown = max($this->attackCooldown - 1, 0);
        if ($this->attackCooldown <= 0 && $distanceToPlayer < $this->getAttackReach()) {
            $damage = $this->mob->getAttackDamage();
            $target->attack(new EntityDamageByEntityEvent($this->mob, $target, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $damage));
            $this->attackCooldown = 20;
        }
    }

    public function getAttackReach(): float
    {
        return $this->mob->width * 2.0 + $this->mob->getTargetEntity()->width;
    }

    public function onEnd(): void
    {
        $this->mob->resetMotion();
        $this->mob->pitch = 0;
        $this->attackCooldown = $this->delay = 0;
        $this->path = null;
        $this->mob->getNavigator()->clearPath();
    }

}