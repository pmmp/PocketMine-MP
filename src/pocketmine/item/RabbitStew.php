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

class RabbitStew extends Food{
    public function __construct(int $meta = 0){
        parent::__construct(self::RABBIT_STEW, $meta, "Rabbit Stew");
    }

    public function getMaxStackSize() : int{
        return 1;
    }

    public function getFoodRestore() : int{
        return 10;
    }

    public function getSaturationRestore() : float{
        return 12;
    }

    public function getResidue(){
        return ItemFactory::get(Item::BOWL);
    }
}