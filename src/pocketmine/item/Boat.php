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

use pocketmine\block\Block;
use pocketmine\entity\vehicle\Boat as EntityBoat;
use pocketmine\math\Vector3;
use pocketmine\Player;

class Boat extends Item{
	public function __construct(int $meta = 0){
		parent::__construct(self::BOAT, $meta, "Boat");
	}

	public function getFuelTime() : int{
		return 1200; //400 in PC
	}

	public function getMaxStackSize(): int {
        return 1;
    }

    public function onActivate(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector) : bool{
		$nbt = EntityBoat::createBaseNBT($blockReplace->add(0.5, 0, 0.5));
		$nbt->setInt("Variant", $this->getDamage());

		$entity = EntityBoat::createEntity("Boat", $player->level, $nbt);
		$entity->spawnToAll();

		$this->count--;

		return true;
	}
}