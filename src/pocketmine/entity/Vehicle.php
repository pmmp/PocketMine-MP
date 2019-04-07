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

use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Player;

abstract class Vehicle extends Entity implements Rideable {

    public function onFirstInteract(Player $player, Item $item, Vector3 $clickPos): bool{
        return $player->mountEntity($this);
    }

    public function kill(): void{
        parent::kill();
        $this->onDeath();
    }

    protected function onDeath(): void{
        foreach($this->getDrops() as $item){
            $this->getLevel()->dropItem($this, $item);
        }
    }

    public function getDrops(): array{
        return [];
    }
}