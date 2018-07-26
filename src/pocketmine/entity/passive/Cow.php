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

namespace pocketmine\entity\passive;

use pocketmine\entity\behavior\FloatBehavior;
use pocketmine\entity\behavior\LookAtPlayerBehavior;
use pocketmine\entity\behavior\MateBehavior;
use pocketmine\entity\behavior\PanicBehavior;
use pocketmine\entity\behavior\RandomLookAroundBehavior;
use pocketmine\entity\behavior\TemptedBehavior;
use pocketmine\entity\behavior\WanderBehavior;
use pocketmine\entity\Tamable;
use pocketmine\item\Bucket;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Player;

class Cow extends Tamable{
    public const NETWORK_ID = self::COW;

    public $width = 0.9;
    public $height = 1.4;

    protected function addBehaviors() : void{
        $this->behaviorPool->setBehavior(0, new FloatBehavior($this));
        $this->behaviorPool->setBehavior(1, new PanicBehavior($this, 2.0));
        $this->behaviorPool->setBehavior(0, new MateBehavior($this, 1.0));
        $this->behaviorPool->setBehavior(0, new TemptedBehavior($this, [Item::WHEAT], 1.25));
        $this->behaviorPool->setBehavior(5, new WanderBehavior($this, 1.0));
        $this->behaviorPool->setBehavior(6, new LookAtPlayerBehavior($this, 6.0));
        $this->behaviorPool->setBehavior(7, new RandomLookAroundBehavior($this));
    }

    protected function initEntity() : void{
        $this->setMaxHealth(10);
        $this->setMovementSpeed(0.2);
        $this->setFollowRange(10);

        parent::initEntity();
    }

    public function getName() : string{
        return "Cow";
    }

    public function onInteract(Player $player, Item $item, Vector3 $clickPos, int $slot) : void{
        if($item instanceof Bucket and $item->getDamage() === 0){
            $milk = clone $item;
            $milk->setDamage(1);
            $player->getInventory()->setItemInHand($milk);
        }
    }
}