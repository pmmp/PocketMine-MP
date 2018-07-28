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

namespace pocketmine\entity\hostile;

use pocketmine\entity\Ageable;
use pocketmine\entity\behavior\FindAttackableTargetBehavior;
use pocketmine\entity\behavior\FloatBehavior;
use pocketmine\entity\behavior\LookAtPlayerBehavior;
use pocketmine\entity\behavior\MeleeAttackBehavior;
use pocketmine\entity\behavior\RandomLookAroundBehavior;
use pocketmine\entity\behavior\WanderBehavior;
use pocketmine\entity\Monster;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\entity\passive\Villager;
use pocketmine\Player;

class Zombie extends Monster implements Ageable{
    public const NETWORK_ID = self::ZOMBIE;

    public $width = 0.6;
    public $height = 1.95;

    protected function initEntity() : void{
        $this->setMovementSpeed($this->isBaby() ? 0.345 : 0.23);
        $this->setFollowRange(35);
        $this->setAttackDamage(3);

        parent::initEntity();
    }

    public function getName(): string{
        return "Zombie";
    }

    public function getDrops(): array{
        $drops = [
            ItemFactory::get(Item::ROTTEN_FLESH, 0, mt_rand(0, 2))
        ];

        if(mt_rand(0, 199) < 5){
            switch(mt_rand(0, 2)){
                case 0:
                    $drops[] = ItemFactory::get(Item::IRON_INGOT, 0, 1);
                    break;
                case 1:
                    $drops[] = ItemFactory::get(Item::CARROT, 0, 1);
                    break;
                case 2:
                    $drops[] = ItemFactory::get(Item::POTATO, 0, 1);
                    break;
            }
        }

        return $drops;
    }

    public function getXpDropAmount() : int{
        //TODO: check for equipment
        return $this->isBaby() ? 12 : 5;
    }

    protected function addBehaviors() : void{
        $this->behaviorPool->setBehavior(0, new FloatBehavior($this));
        $this->behaviorPool->setBehavior(2, new MeleeAttackBehavior($this, 1.0));
        $this->behaviorPool->setBehavior(7, new WanderBehavior($this, 1.0));
        $this->behaviorPool->setBehavior(8, new LookAtPlayerBehavior($this, 0.8));
        $this->behaviorPool->setBehavior(8, new RandomLookAroundBehavior($this));

        $this->targetBehaviorPool->setBehavior(2, new FindAttackableTargetBehavior($this, Player::class));
        $this->targetBehaviorPool->setBehavior(2, new FindAttackableTargetBehavior($this, Villager::class));
    }
}