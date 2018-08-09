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
use pocketmine\entity\Entity;
use pocketmine\entity\object\ArmorStand as EntityArmorStand;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\Player;

class ArmorStand extends Item{

    public function __construct(int $meta = 0){
        parent::__construct(self::ARMOR_STAND, $meta, "Armor Stand");
    }

    public function onActivate(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector): bool{
        $entity = Entity::createEntity("ArmorStand", $player->level, Entity::createBaseNBT($blockReplace->asVector3()->add(0.5, 0, 0.5), null, $this->getDirection($player->getYaw())));

        if($entity instanceof EntityArmorStand){
            if($player->isSurvival()){
                $this->count--;
                $player->getInventory()->setItemInHand($this);
            }

            $entity->spawnToAll();
            $player->getLevel()->broadcastLevelEvent($player, LevelEventPacket::EVENT_SOUND_ARMOR_STAND_PLACE);
            return true;
        }

        return false;
    }

    public function getDirection(float $yaw){
        return (round($yaw / 22.5 / 2) * 45) - 180;
    }

}