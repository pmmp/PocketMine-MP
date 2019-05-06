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

use pocketmine\entity\Entity;
use pocketmine\entity\passive\Pig;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;

class Saddle extends Item{
	public function __construct(int $meta = 0){
		parent::__construct(self::SADDLE, $meta, "Saddle");
	}

	public function getMaxStackSize() : int{
		return 1;
	}

	public function onInteractWithEntity(Player $player, Entity $entity) : bool{
		if($entity instanceof Pig){
			if(!$entity->isSaddled() and !$entity->isBaby()){
				$entity->setSaddled(true);
				$entity->level->broadcastLevelSoundEvent($entity, LevelSoundEventPacket::SOUND_SADDLE);
				$this->pop();
			}

			return true;
		}
		return false;
	}
}

