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

class WanderBehavior extends Behavior
{

    /** @var float */
    protected $speedMultiplier = 1.0, $followRange = 16.0;
    /** @var int */
    protected $chance = 120;

    protected $targetPos;

    public function __construct(Mob $mob, float $speedMultiplier = 1.0, int $chance = 120)
    {
        parent::__construct($mob);

        $this->speedMultiplier = $speedMultiplier;
        $this->chance = $chance;
        $this->mutexBits = 1;
    }

    public function canStart(): bool
    {
        if ($this->random->nextBoundedInt($this->chance) === 0) {
            $pos = $this->findRandomTargetBlock($this->mob, 10, 7);

            if ($pos === null) return false;

            $this->followRange = $this->mob->distanceSquared($pos) + 2;

            $this->targetPos = $pos;

            return true;
        }

        return false;
    }

    public function canContinue(): bool
    {
        return $this->mob->getNavigator()->havePath();
    }

    public function onStart(): void
    {
        $this->mob->getNavigator()->tryMoveTo($this->targetPos, $this->speedMultiplier, $this->followRange);
    }

    public function onEnd(): void
    {
        $this->targetPos = null;
        $this->mob->getNavigator()->clearPath();
    }

    public function findRandomTargetBlock(Entity $entity, int $dxz, int $dy): ?Block
    {
        $currentWeight = PHP_INT_MIN;
        $currentBlock = null;
        for ($i = 0; $i < 10; $i++) {
            $x = $this->random->nextBoundedInt(2 * $dxz + 1) - $dxz;
            $y = $this->random->nextBoundedInt(2 * $dy + 1) - $dy;
            $z = $this->random->nextBoundedInt(2 * $dxz + 1) - $dxz;

            $blockCoords = new Vector3($x, $y, $z);
            $block = $entity->level->getBlock($this->mob->asVector3()->add($blockCoords));
            $blockDown = $block->getSide(0);
            $weight = $this->calculateBlockWeight($entity, $block, $blockDown);
            if ($weight > $currentWeight) {
                $currentWeight = $weight;
                $currentBlock = $block;
            }
        }

        return $currentBlock;
    }

    public function calculateBlockWeight(Entity $entity, Block $block, Block $blockDown): int
    {
        $vec = [$block->getX(), $block->getY(), $block->getZ()];
        if ($entity instanceof Animal) {
            if ($blockDown instanceof Grass) return 20;

            return (int)(max($entity->level->getBlockLightAt(...$vec), $entity->level->getBlockSkyLightAt(...$vec)) - 0.5);
        } else {
            return (int)0.5 - max($entity->level->getBlockLightAt(...$vec), $entity->level->getBlockSkyLightAt(...$vec));
        }
    }
}