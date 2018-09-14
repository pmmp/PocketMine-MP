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
use pocketmine\block\Fence;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\entity\object\LeashKnot;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;

class Lead extends Item{

	public function __construct(int $meta = 0){
		parent::__construct(self::LEAD, $meta, "Lead");
	}

	public function onActivate(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector) : bool{
		if($blockClicked instanceof Fence){
			$knot = LeashKnot::getKnotFromPosition($player->level, $blockClicked);
			$f = 7.0;
			$flag = false;

			foreach($player->level->getCollidingEntities(new AxisAlignedBB($blockClicked->x - $f, $blockClicked->y - $f, $blockClicked->z - $f, $blockClicked->x + $f, $blockClicked->y + $f, $blockClicked->z + $f)) as $entity){
				if($entity instanceof Living){
					if($entity->isLeashed() and $entity->getLeashedToEntity() === $player){
						if($knot === null){
							$knot = new LeashKnot($player->level, Entity::createBaseNBT($blockClicked));
							$knot->spawnToAll();
						}

						$entity->setLeashedToEntity($knot, true);
						$flag = true;
					}
				}
			}

			if($flag){
				$player->level->broadcastLevelSoundEvent($blockClicked, LevelSoundEventPacket::SOUND_LEASHKNOT_PLACE);
			}

			return true;
		}
		return false;
	}
}
