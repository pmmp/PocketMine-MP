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
use pocketmine\block\BlockFactory;
use pocketmine\block\Grass;
use pocketmine\block\TallGrass;
use pocketmine\entity\Mob;
use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\math\Vector3;

class HorseEatBlockBehavior extends Behavior
{

    /** @var int */
    protected $duration;
    /** @var int */
    protected $timeLeft;

    // TODO : MOB CHANGE TO HORSE
    public function __construct(Mob $mob, int $duration)
    {
        parent::__construct($mob);
        $this->mutexBits = 7;

        $this->duration = $this->timeLeft = max(40, $duration);
    }

    public function canStart(): bool
    {
        if ($this->random->nextBoundedInt(1000) != 0) return false;

        $direction = $this->mob->getDirectionVector()->normalize();

        $coord = $this->mob->add($direction->x, 0, $direction->z);;

        $canStart = $this->mob->level->getBlock($coord->getSide(Vector3::SIDE_DOWN)) instanceof Grass || $this->mob->level->getBlock($coord) instanceof TallGrass;
        if (!$canStart) return false;

        $this->duration = 40;

        $this->mob->setMotion($this->mob->getMotion()->multiply(0, 1.0, 0.0));
        $this->mob->setEating(true);

        return true;
    }

    public function canContinue(): bool
    {
        return $this->duration-- > 0;
    }

    public function onEnd(): void
    {
        $direction = $this->mob->getDirectionVector()->normalize();

        $coord = $this->mob->add($direction->x, 0, $direction->z);

        $broken = $this->mob->level->getBlock($coord);
        if ($broken instanceof TallGrass) {
            $this->mob->level->setBlock($coord, BlockFactory::get(Block::AIR));
        } else {
            $coord = $coord->getSide(Vector3::SIDE_DOWN);
            $this->mob->level->setBlock($coord, BlockFactory::get(Block::DIRT));
        }

        $this->mob->level->addParticle(new DestroyBlockParticle($this->mob, $broken));
        $this->mob->setEating(false);
    }

}