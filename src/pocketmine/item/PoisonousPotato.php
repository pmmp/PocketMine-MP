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

namespace pocketmine\item;

use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;

class PoisonousPotato extends Food{
    public function __construct(int $meta = 0){
        parent::__construct(self::POISONOUS_POTATO, $meta, "Poisonous Potato");
    }

    public function getFoodRestore() : int{
        return 2;
    }

    public function getSaturationRestore() : float{
        return 1.2;
    }

    public function getAdditionalEffects() : array{
        if(mt_rand(0, 100) > 40){
            return [
                new EffectInstance(Effect::getEffect(Effect::POISON), 100)
            ];
        }
        return [];
    }
}