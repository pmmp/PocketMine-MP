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

namespace pocketmine\entity;

abstract class Tamable extends Animal{

    public function isTamed() : bool{
        return $this->getGenericFlag(self::DATA_FLAG_TAMED);
    }

    public function setTamed(bool $tamed = true) : void{
        $this->setGenericFlag(self::DATA_FLAG_TAMED, $tamed);
    }

    public function isLeashed() : bool{
        return $this->getGenericFlag(self::DATA_FLAG_LEASHED);
    }

    public function setLeashed(bool $leashed = true) : void{
        $this->setGenericFlag(self::DATA_FLAG_LEASHED, $leashed);
    }

    public function isSitting() : bool{
        return $this->getGenericFlag(self::DATA_FLAG_SITTING);
    }

    public function setSitting(bool $sitting = true) : void{
        $this->setGenericFlag(self::DATA_FLAG_SITTING, $sitting);
    }

}