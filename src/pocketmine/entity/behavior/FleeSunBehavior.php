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

use pocketmine\block\Block;
use pocketmine\block\Grass;
use pocketmine\entity\Animal;
use pocketmine\entity\Entity;
use pocketmine\entity\Mob;
use pocketmine\math\Vector3;
use pocketmine\block\Water;

class FleeSunBehavior extends Behavior
{

    /** @var float */
    protected $speedMultiplier = 1.0;
    /** @var Vector3 */
    protected $shelter;

    public function __construct(Mob $mob, float $speedMultiplier = 1.0)
    {
        parent::__construct($mob);

        $this->speedMultiplier = $speedMultiplier;
        $this->mutexBits = 1;
    }

    public function canStart(): bool
    {
        if ($this->mob->isOnFire() and $this->mob->level->canSeeSky($this->mob->floor()) and $this->mob->level->isDayTime()) {
            $pos = $this->findPossibleShelter($this->mob);
            if ($pos === null) return false;

            $this->shelter = $pos;

            return true;
        }

        return false;
    }

    public function onStart(): void
    {
        $this->mob->getNavigator()->tryMoveTo($this->shelter, $this->speedMultiplier);
    }

    public function canContinue(): bool
    {
        return $this->mob->getNavigator()->havePath();
    }

    public function onEnd(): void
    {
        $this->mob->setMotion($this->mob->getMotion()->multiply(0, 1.0, 0.0));
        $this->mob->getNavigator()->clearPath();
    }

    public function findPossibleShelter(Entity $entity): ?Block
    {
        for ($i = 0; $i < 10; $i++) {
            $block = $this->mob->level->getBlock($this->mob->add($this->random->nextBoundedInt(20) - 10, $this->random->nextBoundedInt(6) - 3, $this->random->nextBoundedInt(20) - 10));
            $canSeeSky = $entity->level->getHighestBlockAt($block->x, $block->z) <= $block->y;
            if (!$block->isSolid() and ($block instanceof Water or !$canSeeSky)) {
                return $block;
            }
        }

        return null;
    }

    public function calculateBlockWeight(Entity $entity, Block $block, Block $blockDown): int
    {
        $vec = [$block->getX(), $block->getY(), $block->getZ()];
        if ($block instanceof Water) return -1;
        if ($entity instanceof Animal) {
            if ($blockDown instanceof Grass) return 20;

            return (int)(max($entity->level->getBlockLightAt(...$vec), $entity->level->getBlockSkyLightAt(...$vec)) - 0.5);
        } else {
            return (int)0.5 - max($entity->level->getBlockLightAt(...$vec), $entity->level->getBlockSkyLightAt(...$vec));
        }
    }
}