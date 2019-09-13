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
use pocketmine\block\Obsidian;
use pocketmine\entity\Entity;
use pocketmine\entity\object\EnderCrystal;
use pocketmine\math\Vector3;
use pocketmine\Player;

class EndCrystal extends Item{

	public function __construct(int $meta = 0){
		parent::__construct(self::END_CRYSTAL, $meta, "End Crystal");
	}

	public function onActivate(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector) : bool{
		if($player->level->getBlock($blockReplace->down()) instanceof Obsidian){
			$crystal = new EnderCrystal($player->level, Entity::createBaseNBT($blockReplace->add(0.5, 0.5, 0.5)));
			$crystal->spawnToAll();

			if($player->isSurvival()){
				$this->pop();
			}

			return true;
		}

		return false;
	}
}
