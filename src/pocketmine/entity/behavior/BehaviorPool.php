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

class BehaviorPool
{

    /** @var Behavior[] */
    protected $behaviors = [];
    /** @var Behavior[] */
    protected $workingBehaviors = [];
    /** @var int */
    protected $tickRate = 3;

    public function __construct(array $behaviors = [])
    {
        $this->behaviors = $behaviors;
    }

    public function setBehavior(int $priority, Behavior $behavior): void
    {
        $this->behaviors[spl_object_hash($behavior)] = [$priority, $behavior];
    }

    public function removeBehavior(Behavior $behavior): void
    {
        unset($this->behaviors[spl_object_hash($behavior)]);
    }

    /**
     * Updates behaviors
     * @param int $tick
     */
    public function onUpdate(int $tick): void
    {
        if ($tick % $this->tickRate === 0) {
            foreach ($this->workingBehaviors as $hash => $bh) {
                if (isset($this->behaviors[$hash])) {
                    if (!$this->canUse($this->behaviors[$hash])) {
                        $bh->onEnd();
                        unset($this->workingBehaviors[$hash]);
                    }
                }
            }
            /** @var \pocketmine\entity\behavior\Behavior[] $data */
            foreach ($this->behaviors as $i => $data) {
                if (!isset($this->workingBehaviors[$i]) and $data[1]->canStart() and $this->canUse($data)) {
                    $this->workingBehaviors[$i] = $data[1];
                    $data[1]->onStart();
                }
            }
        } else {
            foreach ($this->workingBehaviors as $hash => $b) {
                if (!$b->canContinue()) {
                    $b->onEnd();
                    unset($this->workingBehaviors[$hash]);
                }
            }
        }

        foreach($this->workingBehaviors as $behavior){
            $behavior->onTick();
        }
    }

    public function canUse(array $data): bool
    {
        $priority = $data[0];
        foreach ($this->behaviors as $h => $b) {
            if ($b[1] === $data[1]) continue;
            if ($priority >= $b[0]) {
                if (!$this->theyCanWorkCompatible($data[1], $b[1]) and isset($this->workingBehaviors[$h])) {
                    return false;
                }
            } elseif (!$b[1]->isMutable() and isset($this->workingBehaviors[$h])) {
                return false;
            }
        }
        return true;
    }

    public function theyCanWorkCompatible(Behavior $b1, Behavior $b2): bool
    {
        return ($b1->getMutexBits() & $b2->getMutexBits()) === 0;
    }
}