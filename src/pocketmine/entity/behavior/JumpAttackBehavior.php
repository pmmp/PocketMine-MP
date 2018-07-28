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

use pocketmine\entity\passive\Wolf;

class JumpAttackBehavior extends Behavior{

    /** @var float */
    protected $leapHeight;

    public function __construct(Wolf $mob, float $leapHeight){
        parent::__construct($mob);

        $this->leapHeight = $leapHeight;
        $this->mutexBits = 5;
    }

    public function canStart() : bool{
        $target = $this->mob->getTargetEntity();
        if($target == null) return false;
        if(!$target->isAlive()) return false;

        $distance = $this->mob->distance($target);

        return $distance >= 4 && $distance <= 16 && $this->mob->isOnGround() && $this->random->nextBoundedInt(5) == 0;
    }

    public function canContinue() : bool{
        return $this->mob->isOnGround();
    }

    public function onTick() : void{
        $target = $this->mob->getTargetEntity();
        $direction = $target->subtract($this->mob);
        $distance = $this->mob->distance($target);

        $velocity = $this->mob->getMotion();
        $x = $direction->x / $distance * 0.5 * 0.8 + $velocity->x * 0.2;
        $z = $direction->z / $distance * 0.5 * 0.8 + $velocity->z * 0.2;
        $y = $this->leapHeight;
        $velocity->add($x, $y, $z);

        $this->mob->setMotion($velocity);
    }
}