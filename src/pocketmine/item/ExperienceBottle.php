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

class ExperienceBottle extends ProjectileItem{
    public function __construct(int $meta = 0){
        parent::__construct(self::EXPERIENCE_BOTTLE, $meta, "Bottle o' Enchanting");
    }

    public function getProjectileEntityType() : string{
        return "ThrownExpBottle";
    }

    public function getThrowForce() : float{
        return 0.7;
    }
}